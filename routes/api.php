<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\authController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/register', [authController::class, 'register']);
Route::post('/login', [authController::class, 'login']);

Route::group(
    [
        'middleware' => [
            'auth:user-api',
            'pass:user'
        ]
    ],
    function () {

        Route::post('/test', [authController::class, 'user_test']);
    }
);

Route::group([], function () {

    Route::post('/CMS/login', [authController::class, 'cms_login']);

    Route::group(
        [
            'middleware' => [
                'auth:admin-api',
                'pass:admin'
            ]
        ],
        function () {

            Route::post('/CMS/test', [authController::class, 'CMS_test']);
        }
    );
});
