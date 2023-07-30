<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
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


// Add an expiry date to the access token.
// $table->timestamp('expires_at')->default(Carbon::now()->addDays(5)) doesn't work for some reasons.

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

Route::post('/email/verification-notification', [AuthController::class, 'resendNotification'])
    ->middleware("auth:sanctum")
    ->name('verification.send');


// Users.

Route::prefix("users")
    ->middleware(["auth:sanctum", "authorizeToken"])
    ->group(function () {

        Route::controller(UserController::class)->group(function () {
            Route::get("", "users")->name("users.all")->withoutMiddleware(["auth:sanctum", "authorizeToken"]);
            Route::get("{handle}", "user")->name("user")->withoutMiddleware(["auth:sanctum", "authorizeToken"]);

            Route::patch("{handle}", "updateUserProfile");
            Route::put("{handle}", "updateUserPassword");
            Route::post("{handle}/logout", "logout");
            Route::post("{handle}/logout-all", "logoutFromAllDevices");
        });


        Route::controller(PostController::class)->group(function () {
            Route::post("{handle}/post", "createPost");
            Route::get("{handle}/post/{postId}", "getPost");
            Route::delete("{handle}/post/{postId}", "deletePost")->middleware("verifyAuthor");
        });
    });


Route::group(["middleware" => ["auth:sanctum"]], function ($route) {
    $route->get("/home", function (Request $request) { // for testing
        return $request->user();
    })
        ->middleware("verified")
        ->name("home");

//    $route->post('/logout', [AuthController::class, 'logout']);
//    $route->post('/email/verification-notification', [AuthController::class, 'resendNotification'])
//        ->name('verification.send');
});
