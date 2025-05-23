<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class SafeHtml implements CastsAttributes
{
    /**
     * Array of allowed HTML tags and their allowed attributes
     */
    protected array $allowedElements = [
        // Links
        'a' => ['href', 'target', 'rel'],

        // Images
        'img' => ['src', 'alt'],

        // Basic formatting
        'p' => ['style'],
        'br' => [],
        'strong' => [],
        'em' => [],
        'u' => [],
        'h1' => [],
        'h2' => [],
        'h3' => [],
        'h4' => [],
        'h5' => [],
        'h6' => [],
        'blockquote' => [],
        'pre' => [],
        'code' => [],

        // Lists
        'ul' => ['data-type'],
        'ol' => [],
        'li' => ['data-checked', 'data-type'],

        // Task list elements
        'label' => [],
        'input' => ['type'],
        'span' => [],
        'div' => ['data-type'],

        // Table elements
        'table' => ['style'],
        'colgroup' => [],
        'col' => ['style'],
        'tbody' => [],
        'tr' => [],
        'th' => ['colspan', 'rowspan', 'colwidth'],
        'td' => ['colspan', 'rowspan', 'colwidth'],

        // Details/Summary elements
        'details' => ['class'],
        'summary' => []
    ];

    /**
     * Cast the given value.
     *
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return mixed
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return null;
        }

        // For title field, decode HTML entities when retrieving
        if ($key === 'title') {
            return htmlspecialchars_decode($value, ENT_QUOTES | ENT_HTML5);
        }

        return $this->clean($value);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return mixed
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return null;
        }

        // For title field, strip tags and encode for safe storage
        if ($key === 'title') {
            $stripped = strip_tags($value);
            return htmlspecialchars($stripped, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }


        return $this->clean($value);
    }

    /**
     * Clean HTML content by removing dangerous elements and attributes
     */
    protected function clean(string $value): string
    {
        if (empty($value)) {
            return '';
        }

        // First decode any HTML entities in the content
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Store pre/code blocks before processing
        $codeBlocks = [];
        $value = preg_replace_callback('/<pre><code>([\s\S]*?)<\/code><\/pre>/s', function ($matches) use (&$codeBlocks) {
            $placeholder = '<!--CODE_BLOCK_' . count($codeBlocks) . '-->';
            $codeBlocks[] = $matches[1];
            return $placeholder;
        }, $value);

        // Check if content starts with <?php and preserve entire PHP file
        if (preg_match('/^\s*<\?php/s', $value)) {
            return $value; // Return PHP file content unchanged
        }

        // Store any remaining PHP code blocks before DOM processing
        $phpBlocks = [];
        $value = preg_replace_callback('/(<\?php[\s\S]*?\?>)/s', function ($matches) use (&$phpBlocks) {
            $placeholder = '<!--PHP_BLOCK_' . count($phpBlocks) . '-->';
            $phpBlocks[] = $matches[1];
            return $placeholder;
        }, $value);

        // Create a new DOM document
        $dom = new \DOMDocument('1.0', 'UTF-8');

        // Preserve spaces and tabs
        $dom->preserveWhiteSpace = true;
        $dom->formatOutput = false;

        // Convert tabs to a placeholder before processing
        $value = preg_replace('/\t/', '<!--TAB-->', $value);

        // Convert to UTF-8 and wrap with proper encoding declaration
        $value = mb_convert_encoding($value, 'HTML-ENTITIES', 'UTF-8');
        $value = '<div>' . $value . '</div>';

        // Load HTML with UTF-8 encoding
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $value, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR);
        libxml_clear_errors();

        // Recursively clean nodes
        $this->cleanNode($dom->getElementsByTagName('div')->item(0));

        // Get the cleaned HTML
        $html = $dom->saveHTML($dom->getElementsByTagName('div')->item(0));

        // Remove the wrapper div
        $html = preg_replace('/^<div>|<\/div>$/', '', $html);

        // Restore tabs
        $html = preg_replace('/<!--TAB-->/', "\t", $html);

        // Restore PHP blocks first
        $html = preg_replace_callback('/<!--PHP_BLOCK_(\d+)-->/', function ($matches) use ($phpBlocks) {
            return $phpBlocks[(int)$matches[1]];
        }, $html);

        // Then restore code blocks, wrapping them back in pre/code tags
        $html = preg_replace_callback('/<!--CODE_BLOCK_(\d+)-->/', function ($matches) use ($codeBlocks) {
            return '<pre><code>' . htmlspecialchars($codeBlocks[(int)$matches[1]], ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</code></pre>';
        }, $html);

        return $html;
    }

    /**
     * Recursively clean DOM nodes
     */
    protected function cleanNode(\DOMNode $node): void
    {
        if ($node->hasChildNodes()) {
            $children = [];
            foreach ($node->childNodes as $child) {
                $children[] = $child;
            }
            foreach ($children as $child) {
                if ($child instanceof \DOMElement) {
                    // Check if element is allowed
                    if (!isset($this->allowedElements[$child->tagName])) {
                        // Replace with text content if not allowed
                        $text = $node->ownerDocument->createTextNode($child->textContent);
                        $node->replaceChild($text, $child);
                        continue;
                    }

                    // Clean attributes
                    $attributes = [];
                    foreach ($child->attributes as $attr) {
                        $attributes[] = $attr;
                    }
                    foreach ($attributes as $attr) {
                        // Special handling for style attribute
                        if ($attr->name === 'style') {
                            // Only allow width and text-align in style attribute
                            if (!preg_match('/^(width:\s*\d+px|text-align:\s*(left|center|right)|width:\s*\d+px;\s*text-align:\s*(left|center|right)|text-align:\s*(left|center|right);\s*width:\s*\d+px)$/', $attr->value)) {
                                $child->removeAttribute($attr->name);
                            }
                        } // Remove attribute if not in allowed list
                        else if (!in_array($attr->name, $this->allowedElements[$child->tagName])) {
                            $child->removeAttribute($attr->name);
                        }
                    }

                    // Recursively clean child nodes
                    $this->cleanNode($child);
                }
            }
        }
    }
}
