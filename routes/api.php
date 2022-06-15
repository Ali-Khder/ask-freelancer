<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\authController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProjectController;

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

        Route::post('/account', [authController::class, 'account']);
        Route::get('/profile', [authController::class, 'get_profile']);
        Route::post('/password/change', [authController::class, 'changePassword']);

        Route::get('/category/parents', [CategoryController::class, 'getParent']);
        Route::get('/category/children', [CategoryController::class, 'getChildren']);
        Route::get('/category/child/{id}', [CategoryController::class, 'getChild']);

        Route::get('/projects', [ProjectController::class, 'index']);
        Route::post('/projects', [ProjectController::class, 'create']);
        Route::get('/projects/{id}', [ProjectController::class, 'show']);
        Route::post('/projects/{id}', [ProjectController::class, 'update']);
        Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);
    }
);

Route::post('/CMS/login', [authController::class, 'cms_login']);

Route::group(
    [
        'middleware' => [
            'auth:admin-api',
            'pass:admin'
        ]
    ],
    function () {
        $cms = '/CMS';

        Route::post($cms . '/password/change', [authController::class, 'changeCMSPassword']);
        Route::get($cms . '/category', [CategoryController::class, 'index']);
        Route::post($cms . '/category', [CategoryController::class, 'create']);
        Route::get($cms . '/category/{id}', [CategoryController::class, 'show']);
        Route::post($cms . '/category/{id}', [CategoryController::class, 'update']);
        Route::delete($cms . '/category/{id}', [CategoryController::class, 'destroy']);

        Route::get($cms . '/admins', [AdminController::class, 'index']);
        Route::post($cms . '/admins', [AdminController::class, 'create']);
        Route::get($cms . '/admins/{id}', [AdminController::class, 'show']);
        Route::post($cms . '/admins/{id}', [AdminController::class, 'update']);
        Route::delete($cms . '/admins/{id}', [AdminController::class, 'destroy']);
    }
);
