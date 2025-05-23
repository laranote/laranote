<?php

namespace App\Actions;

class GetHocuspocusServerUrl
{
    /**
     * This class generates the correct WebSocket URL for connecting to the Hocuspocus server.
     * It checks if the app is using HTTPS to decide between wss or ws,
     * then adds either a port or a reverse proxy route based on your config.
     *
     * wss://yourdomain.com:1234 or ws://yourdomain.com:1234,
     * wss://yourdomain.com/editor or ws://yourdomain.com/editor.
     *
     * @return string
     */
    static function execute(): string
    {
        $scheme = parse_url(config('app.url'), PHP_URL_SCHEME) === 'https' ? 'wss' : 'ws';
        $pathOrPort = empty(config('hocuspocus-laravel.reverse_proxy_route')) ? ":" . config("hocuspocus-laravel.port") : config("hocuspocus-laravel.reverse_proxy_route");
        return $scheme . "://" . parse_url(config("app.url"), PHP_URL_HOST) . $pathOrPort;
    }
}
