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
use App\Http\Controllers\IdentityDocumentionController;

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
        Route::post('/password/reset', [authController::class, 'passwordReset'])->name('user.password.reset');
        Route::post('/logout', [authController::class, 'logout']);

        Route::get('/category/parents', [CategoryController::class, 'getParent']);
        Route::get('/category/children', [CategoryController::class, 'getChildren']);
        Route::get('/category/child/{id}', [CategoryController::class, 'getChild']);

        Route::get('/projects', [ProjectController::class, 'index']);
        Route::post('/projects', [ProjectController::class, 'create']);
        Route::get('/projects/{id}', [ProjectController::class, 'show']);
        Route::post('/projects/{id}', [ProjectController::class, 'update']);
        Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);
    
        Route::post('/account confirmation/mail', [authController::class, 'sendConfirmationMail'])->name('user.accountConfirmation.mail');
        Route::post('/account confirmation/verification', [authController::class, 'verification'])->name('user.accountConfirmation.verification');

        Route::group(
            ['middleware' => 'PostExists'] ,function () {
                Route::group(
                    ['middleware' => 'MyOwnPost'] ,function () {
                        Route::post('/post/edit/{id}', [PostController::class, 'editPost'])->name('user.post.edit');
                        Route::delete('/post/delete/{id}', [PostController::class, 'deletePost'])->name('user.post.delete');
                    });
                Route::get('/post/{id}', [PostController::class, 'getPost'])->name('user.post.get');
            });

        Route::post('/post/create', [PostController::class, 'createPost'])->name('user.post.create');

        Route::get('/user/{id}/posts', [PostController::class, 'getUserPosts'])->name('user.posts.get');
        Route::get('/posts/small/get', [PostController::class, 'getSmallServices'])->name('user.smallServices.get');
        Route::get('/posts/non small/get', [PostController::class, 'getNonSmallServices'])->name('user.nonSmallServices.get');
 
        Route::post('/offer/create/post/{id}', [OfferController::class, 'createOffer'])->middleware('PostExists')->name('user.offer.create');
        Route::group(
            ['middleware' => ['OfferExists','MyOwnOffer']] ,function () {        
                Route::post('/offer/edit/{id}', [OfferController::class, 'editOffer'])->name('user.offer.edit');
                Route::delete('/offer/delete/{id}', [OfferController::class, 'deleteOffer'])->name('user.offer.delete');
        });

        Route::get('/post/{id}/offers', [OfferController::class, 'getPostOffers'])->middleware('PostExists')->name('user.offers.get');
       
        Route::post('/offer/accept/{id}', [OfferController::class, 'acceptOffer'])->middleware(['PostExists','MyOwnPost','OfferExists'])->name('user.offer.accept');
        Route::delete('/order/cancel/{id}', [OfferController::class, 'cancelOrder'])->middleware(['OrderExists'])->name('user.order.cancel');
        Route::post('/order/accept/{id}', [OfferController::class, 'acceptAcceptOffer'])->middleware(['OrderExists'])->name('user.order.accept');

        Route::post('/ID documention/send', [IdentityDocumentionController::class, 'sendIdentityDocument'])->name('user.idDocumention.send');
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

        
        Route::post('/ID documention/respone', [IdentityDocumentionController::class, 'ResponeIdentityDocumentation'])->name('cms.idDocumention.respone');
        Route::get('/ID documention/get', [IdentityDocumentionController::class, 'GetIdentityDocumentation'])->name('cms.idDocumention.get');
    }
);
