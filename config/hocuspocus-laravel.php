<?php

return [
    'secret' => env('HOCUSPOCUS_SECRET'),
    'port' => env('HOCUSPOCUS_PORT', 1234),
    'reverse_proxy_route' => env('HOCUSPOCUS_REVERSE_PROXY_ROUTE'),
];
