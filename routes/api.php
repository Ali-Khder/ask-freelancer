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
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\ChargeController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\ChatController;

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
        Route::post('/message/{id}', [ChatController::class, 'sendMessage']);
        Route::get('/message/{id}', [ChatController::class, 'index']);

        Route::post('/wallet', [ChargeController::class, 'createWallet']);
        Route::post('/charge', [ChargeController::class, 'charge']);
        Route::get('/wallet', [ChargeController::class, 'getAmount']);

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
            ['middleware' => 'PostExists'],
            function () {
                Route::group(
                    ['middleware' => 'MyOwnPost'],
                    function () {
                        Route::post('/post/edit/{id}', [PostController::class, 'editPost'])->name('user.post.edit');
                        Route::delete('/post/delete/{id}', [PostController::class, 'deletePost'])->name('user.post.delete');
                    }
                );
                Route::get('/post/{id}', [PostController::class, 'getPost'])->name('user.post.get');
            }
        );

        Route::post('/post/create', [PostController::class, 'createPost'])->name('user.post.create');

        Route::get('/user/{id}/posts', [PostController::class, 'getUserPosts'])->name('user.posts.get');
        Route::get('/posts/small/get', [PostController::class, 'getSmallServices'])->name('user.smallServices.get');
        Route::get('/posts/non small/get', [PostController::class, 'getNonSmallServices'])->name('user.nonSmallServices.get');

        Route::post('/offer/create/post/{id}', [OfferController::class, 'createOffer'])->middleware('PostExists')->name('user.offer.create');
        Route::group(
            ['middleware' => ['OfferExists', 'MyOwnOffer']],
            function () {
                Route::post('/offer/edit/{id}', [OfferController::class, 'editOffer'])->name('user.offer.edit');
                Route::delete('/offer/delete/{id}', [OfferController::class, 'deleteOffer'])->name('user.offer.delete');
            }
        );

        Route::get('/post/{id}/offers', [OfferController::class, 'getPostOffers'])->middleware('PostExists')->name('user.offers.get');

        Route::post('/offer/accept/{id}', [OfferController::class, 'acceptOffer'])->middleware(['PostExists', 'MyOwnPost', 'OfferExists'])->name('user.offer.accept');
        Route::delete('/order/cancel/{id}', [OfferController::class, 'cancelOrder'])->middleware(['OrderExists'])->name('user.order.cancel');
        Route::post('/order/accept/{id}', [OfferController::class, 'acceptAcceptOffer'])->middleware(['OrderExists'])->name('user.order.accept');

        Route::post('/ID documention/send', [IdentityDocumentionController::class, 'sendIdentityDocument'])->name('user.idDocumention.send');

        Route::post('/skill/{id}/check', [TestController::class, 'checkanswer'])->name('user.skill.check');
        Route::get('/skill/{id}/questions/get', [TestController::class, 'getquestions'])->name('user.skill.questions.get');
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

        Route::post($cms . '/ID documention/respone', [IdentityDocumentionController::class, 'ResponeIdentityDocumentation'])->name('cms.idDocumention.respone');
        Route::get($cms . '/ID documention/get', [IdentityDocumentionController::class, 'GetIdentityDocumentation'])->name('cms.idDocumention.get');

        Route::post($cms . '/password/change', [authController::class, 'changeCMSPassword'])->name('cms.auth.password.change');
        Route::post($cms . '/account confirmation/mail', [authController::class, 'sendConfirmationMail'])->name('cms.accountConfirmation.mail');
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

        Route::post($cms . '/category/{id}/test/create', [TestController::class, 'createTest'])->name('cms.test.create');
        Route::delete($cms . '/category/{id}/test/delete', [TestController::class, 'deleteTest'])->name('cms.test.delete');
        Route::post($cms . '/test/question/{id}', [TestController::class, 'editQuestion'])->middleware(['QuestionExists'])->name('cms.question.update');
        Route::post($cms . '/test/answer/{id}', [TestController::class, 'editAnswer'])->middleware(['AnswerExists'])->name('cms.answer.update');
    }
);
