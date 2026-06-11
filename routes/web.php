<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| The React SPA is served by the frontend container (frontend/).
| This backend exposes the REST API under /api/v1 via module routes.
|
*/

Route::get('/', function () {
    return response()->json([
        'service' => config('app.name'),
        'status' => 'ok',
        'frontend' => 'Use the frontend container or `npm run dev` in frontend/',
    ]);
});

