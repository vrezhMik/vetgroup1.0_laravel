<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'OK';
});

Route::get('/__debug/url', function (Request $request) {
    return response()->json([
        'scheme_host' => $request->getSchemeAndHttpHost(),
        'full_url' => $request->fullUrl(),
        'app_url' => config('app.url'),
        'headers' => [
            'host' => $request->header('host'),
            'x_forwarded_proto' => $request->header('x-forwarded-proto'),
            'x_forwarded_host' => $request->header('x-forwarded-host'),
            'x_forwarded_port' => $request->header('x-forwarded-port'),
        ],
    ]);
});
