<?php

use Illuminate\Support\Facades\Route;

Route::prefix('api/whatsapp')->group(function () {
    Route::get('/webhook', function (\Illuminate\Http\Request $request) {
        $webhook = app('whatsapp')->webhook();
        $result = $webhook->verifyToken(
            $request->input('hub_mode'),
            $request->input('hub_verify_token'),
            $request->input('hub_challenge')
        );

        if ($result === false) {
            return response('Forbidden', 403);
        }

        return response($result, 200)->header('Content-Type', 'text/plain');
    });

    Route::post('/webhook', function (\Illuminate\Http\Request $request) {
        $signature = $request->header('X-Hub-Signature-256');
        $webhook = app('whatsapp')->webhook();

        if (!$webhook->verifySignature($request->getContent(), $signature)) {
            return response('Forbidden', 403);
        }

        $webhook->handle($request->all());

        return response('OK', 200);
    })->middleware('throttle:60,1');
});
