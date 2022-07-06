<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\authController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\PostController;

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
Route::get('/guest', [ServicesController::class, 'index']);

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
        Route::post('/password/reset', [authController::class, 'passwordReset']);
        Route::post('/logout', [authController::class, 'logout']);

        Route::get('/category/parents', [CategoryController::class, 'getParent']);
        Route::get('/category/children', [CategoryController::class, 'getChildren']);
        Route::get('/category/child/{id}', [CategoryController::class, 'getChild']);

        Route::get('/projects', [ProjectController::class, 'index']);
        Route::post('/projects', [ProjectController::class, 'create']);
        Route::get('/projects/{id}', [ProjectController::class, 'show']);
        Route::post('/projects/{id}', [ProjectController::class, 'update']);
        Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);
    
        Route::post('/account confirmation/mail', [authController::class, 'sendConfirmationMail']);
        Route::post('/account confirmation/verification', [authController::class, 'verification']);

        Route::group([
            'prefix' => 'post'] ,function(){
                Route::post('category/{id}/create', [PostController::class, 'createPost']);
                Route::post('/edit/{id}', [PostController::class, 'editPost']);
                Route::delete('/delete/{id}', [PostController::class, 'deletePost']);
            }
        );
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
        Route::post($cms . '/password/reset', [authController::class, 'passwordResetCMS']);
        Route::post($cms . '/logout', [authController::class, 'logoutCMS']);

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

        Route::get($cms . '/service', [ServicesController::class, 'index_cms']);
        Route::post($cms . '/service', [ServicesController::class, 'create']);
        Route::get($cms . '/service/{id}', [ServicesController::class, 'show']);
        Route::post($cms . '/service/{id}', [ServicesController::class, 'update']);
        Route::delete($cms . '/service/{id}', [ServicesController::class, 'destroy']);
    }
);
