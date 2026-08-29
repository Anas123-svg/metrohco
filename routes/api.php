<?php

use App\Http\Controllers\Api\FcmTokenController;
use App\Http\Controllers\Api\LanguageController;
use App\Http\Controllers\Api\SupportTicketController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\PropertyController;
use Illuminate\Support\Facades\Route;

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

Route::get('/home', [HomeController::class, 'index'])->name('api.home');
Route::get('/get-lang', [LanguageController::class, 'getLang'])->name('api.get_language');
Route::get('/get-basic', [HomeController::class, 'getBasic'])->name('getBasic');
Route::get('/vendor/{username}', [HomeController::class, 'vendorDetails'])->name('api.vendor.details');
Route::get('/agent/{username}', [HomeController::class, 'agentDetails'])->name('api.agent.details');


Route::post('/save-fcm-token', [FcmTokenController::class, 'store']);

//guest user routes
Route::prefix('user')->group(function () {
  Route::get('/signup', [UserController::class, 'signup'])->name('api.user.signup');
  Route::post('/signup/submit', [UserController::class, 'signupSubmit'])->name('api.user.signup_submit');

  Route::get('/login', [UserController::class, 'login'])->name('api.user.login');
  Route::post('/login/submit', [UserController::class, 'loginSubmit'])->name('api.user.login_submit');

  Route::post('/forget-password', [UserController::class, 'forgetPassword'])->name('api.user.forget_password');
  Route::post('/verify-otp', [UserController::class, 'verifyOTP'])->name('api.user.verify_otp');
  Route::post('/reset-password', [UserController::class, 'resetPassword'])->name('api.user.reset_password');
});

//authenticated user routes
Route::prefix('/user')->middleware('auth.sanctum')->group(function () {
  Route::get('/dashboard', [UserController::class, 'redirectToDashboard'])->name('api.user.dashboard');
  Route::get('/wishlist', [UserController::class, 'wishlist'])->name('api.user.wishlist');

  Route::get('/edit-profile', [UserController::class, 'editProfile'])->name('api.user.edit_profile');
  Route::post('/update-profile', [UserController::class, 'updateProfile'])->name('api.user.update_profile');
  Route::get('/change-password', [UserController::class, 'changePassword'])->name('api.user.change_password');
  Route::post('/update-password', [UserController::class, 'updatePassword'])->name('user.update_password');

  Route::post('addto/wishlist', [UserController::class, 'addWishlist'])->name('api.user.wishlist')->middleware('auth.sanctum');
  Route::post('remove/wishlist', [UserController::class, 'removeWishlist'])->name('api.remove.wishlist')->middleware('auth.sanctum');

  Route::prefix('support-ticket')->group(function () {
    Route::get('/', [SupportTicketController::class, 'index'])->name('api.user.support_ticket');
    Route::get('/create', [SupportTicketController::class, 'create'])->name('api.user.support_ticket.create');
    Route::post('store', [SupportTicketController::class, 'store'])->name('api.user.support_ticket.store');
    Route::get('message/{id}',  [SupportTicketController::class, 'message'])->name('api.user.support_ticket.message');
    Route::post('reply/{id}',  [SupportTicketController::class, 'reply'])->name('api.user.support_ticket.reply');
  });

  Route::post('/logout', [UserController::class, 'logoutSubmit'])->name('user.logout');
});

// Properties routes
Route::get('/properties', [PropertyController::class, 'index'])->name('api.properties.index');
Route::get('/property-details/{id}', [PropertyController::class, 'show'])->name('api.properties.show');
Route::post('/property-contact', [PropertyController::class, 'contact'])->name('api.property_contact');

// Projects routes
Route::get('/projects', [ProjectController::class, 'index'])->name('api.projects.index');
Route::get('/project-details/{id}', [ProjectController::class, 'show'])->name('api.projects.show');
