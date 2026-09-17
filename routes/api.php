<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/tokens/create', function (Request $request) {
        $expiration = config('sanctum.expiration');

        $token = $request->user()->createToken(
            $request->token_name,
            ['*'],
            $expiration ? now()->addMinutes($expiration) : null,
        );

        return [
            'token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at,
        ];
    });
});
