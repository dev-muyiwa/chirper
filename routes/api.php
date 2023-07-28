<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get("/401", function () {
    return response()->json([
        'status' => "failure",
        'error' => null,
        'message' => "Bearer token not found.",
    ], 401);
})->name("unauthorized");

Route::get("/403", function () {
    return response()->json([
        'status' => "failure",
        'error' => null,
        'message' => "You are not authorized to access this resource.",
    ], 403);
})->name("forbidden");


// Authentication.

Route::prefix("auth")
    ->controller(AuthController::class)
    ->group(function () {
        Route::post('signup', 'signup')->name("signup");
        Route::post('login', 'manualLogin')->name("login");

        Route::get("oauth-google", "continueWithGoogle")->name("oauth-google-redirect");
        Route::get("oauth-google/callback", "googleCallback")->name("oauth-google-callback");

        Route::post("forgot-password", "forgotPassword");
        Route::post("reset-password", "resetPassword")->name('password.reset');
    });

// Users.

Route::prefix("users")
//    ->middleware("auth:sanctum")
    ->controller(UserController::class)
    ->group(function () {

        Route::get("", "users")->name("users.all");
        Route::get("{id}", "user")->name("user");

        Route::patch("{id}", "updateUserProfile")->middleware(["auth:sanctum", "authorizeToken"]);
        Route::patch("{id}/password", "updateUserPassword")->middleware(["auth:sanctum", "authorizeToken"]);

        // Add the verify email endpoint.
    });




Route::group(["middleware" => ["auth:sanctum"]], function ($route) {
    $route->get("/home", function (Request $request) { // for testing
        return $request->user();
    })
        ->middleware("verified")
        ->name("home");

//    $route->post('/logout', [AuthController::class, 'logout']);
    $route->post('/email/verification-notification', [AuthController::class, 'resendNotification'])
        ->name('verification.send');
});
