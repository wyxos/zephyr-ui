<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'api/workspace'], function () {
    Route::post('/authenticate', function (Request $request) {
        $response = Http::post('https://zephyr.test/api/authenticate', [
            'email' => $request->email,
            'password' => $request->password
        ]);

        if ($response->successful()) {
            $token = $response->json()['token'];

            return response()->json(['message' => 'Authenticated successfully', 'token' => $token]);
        } else {
            // Handle the error response
            return response()->json($response->json(), $response->status());
        }
    });

    Route::get('/validate-token', function (Request $request) {
        $token = $request->token;

        if (!$token) {
            return response()->json(['message' => 'No session token found'], 401);
        }

        $response = Http::withToken($token)->post('https://zephyr.test/api/validate-token');

        if ($response->successful()) {
            return response()->json(['message' => 'Token is valid']);
        } else {
            // Consider also clearing the session token if it's invalid
            $request->session()->forget('task_manager_token');
            return response()->json(['message' => 'Token is invalid'], 401);
        }
    });

    Route::any('/proxy/{endpoint}', function (Request $request) {
        $token = $request->token;

        $method = $request->getMethod();

        $endpoint = 'https://zephyr.test/api/workspace/' .
            env('PROJECT_TOKEN') . '/' .
            $request->route('endpoint');

        $response = Http::withToken($token)
            ->{strtolower($method)}($endpoint);

        return $response->json();
    })
        ->where('endpoint', '.*');
})
    ->middleware(['api', 'auth:sanctum']);
