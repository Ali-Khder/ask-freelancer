<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\authController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\ChargeController;

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
        Route::post('/wallet', [ChargeController::class, 'createWallet']);
        Route::post('/charge', [ChargeController::class, 'charge']);
        Route::get('/wallet', [ChargeController::class, 'getAmount']);

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
                Route::group(
                    ['middleware' => 'PostExists'] ,function () {
                        Route::group(
                            ['middleware' => 'MyOwnPost'] ,function () {
                                Route::post('/edit/{id}', [PostController::class, 'editPost']);
                                Route::delete('/delete/{id}', [PostController::class, 'deletePost']);
                            });
                        Route::get('/{id}', [PostController::class, 'getPost']);
                    });

                Route::post('/create', [PostController::class, 'createPost']);
            }
        );

        Route::get('/user/{id}/posts', [PostController::class, 'getUserPosts']);

        Route::group([
            'prefix' => 'offer'] ,function(){
                Route::post('/create/post/{id}', [OfferController::class, 'createOffer'])->middleware('PostExists');
                Route::group(
                    ['middleware' => ['OfferExists','MyOwnOffer']] ,function () {
                                Route::post('/edit/{id}', [OfferController::class, 'editOffer']);
                                Route::delete('/delete/{id}', [OfferController::class, 'deleteOffer']);
                });
            }
        );

        Route::get('/post/{id}/offers', [OfferController::class, 'getPostOffers'])->middleware('PostExists');
    }
);

Route::post('/CMS/login', [authController::class, 'cms_login']);

Route::group(
    [
        'middleware' => [
            'auth:admin-api',
            'pass:admin',
            'permissions'
        ]
    ],
    function () {
        $cms = '/CMS';

        Route::post($cms . '/password/change', [authController::class, 'changeCMSPassword'])->name('cms.auth.password.change');
        Route::post($cms . '/password/reset', [authController::class, 'passwordResetCMS'])->name('cms.auth.password.reset');
        Route::post($cms . '/logout', [authController::class, 'logoutCMS'])->name('cms.auth.logout');

        Route::get($cms . '/category', [CategoryController::class, 'index'])->name('cms.categories.index');
        Route::post($cms . '/category', [CategoryController::class, 'create'])->name('cms.categories.create');
        Route::get($cms . '/category/{id}', [CategoryController::class, 'show'])->name('cms.categories.show');
        Route::post($cms . '/category/{id}', [CategoryController::class, 'update'])->name('cms.categories.update');
        Route::delete($cms . '/category/{id}', [CategoryController::class, 'destroy'])->name('cms.categories.destroy');

        Route::get($cms . '/admins', [AdminController::class, 'index'])->name('cms.admins.index');
        Route::post($cms . '/admins', [AdminController::class, 'create'])->name('cms.admins.create');
        Route::get($cms . '/admins/{id}', [AdminController::class, 'show'])->name('cms.admins.show');
        Route::post($cms . '/admins/{id}', [AdminController::class, 'update'])->name('cms.admins.update');
        Route::delete($cms . '/admins/{id}', [AdminController::class, 'destroy'])->name('cms.admins.destroy');

        Route::get($cms . '/roles', [RolePermissionController::class, 'index'])->name('cms.roles.index');
        Route::post($cms . '/roles', [RolePermissionController::class, 'create'])->name('cms.roles.create');
        Route::get($cms . '/roles/{id}', [RolePermissionController::class, 'show'])->name('cms.roles.show');
        Route::post($cms . '/roles/{id}', [RolePermissionController::class, 'update'])->name('cms.roles.update');
        Route::delete($cms . '/roles/{id}', [RolePermissionController::class, 'destroy'])->name('cms.roles.destroy');
        Route::get($cms . '/permissions', [RolePermissionController::class, 'permissions'])->name('cms.permissions.index');
        Route::post($cms . '/permissions/except', [RolePermissionController::class, 'getExceptPermission'])->name('cms.permissions.except');

        Route::get($cms . '/service', [ServicesController::class, 'index_cms'])->name('cms.services.index');
        Route::post($cms . '/service', [ServicesController::class, 'create'])->name('cms.services.create');
        Route::get($cms . '/service/{id}', [ServicesController::class, 'show'])->name('cms.services.show');
        Route::post($cms . '/service/{id}', [ServicesController::class, 'update'])->name('cms.services.update');
        Route::delete($cms . '/service/{id}', [ServicesController::class, 'destroy'])->name('cms.services.destroy');
    }
);
