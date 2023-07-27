<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\AuthorizeUserEmail;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $input = $request->all();
        try {
            $validator = Validator::make($input, [
                "first_name" => "required|string",
                "last_name" => "required|string",
                "username" => "required|string|unique:users,username",
                "email" => "required|email|unique:users,email",
                "password" => "required|string|confirmed|min:8"
            ]);

            if ($validator->fails()) {
                throw new Exception("Validation error.", 400, $validator->errors());
            }

            $user = User::create([
                "full_name" => "{$input["first_name"]} {$input["last_name"]}",
                "username" => $input["username"],
                "email" => $input["email"],
                "password" => Hash::make($input["password"])
            ]);

            $token = $user->createToken("user_token")->accessToken;

            return $this->onSuccess($token, "Registration successful.", 201);
        } catch (Exception $e) {
            return $this->onFailure($e->getTrace(), $e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function startEmailVerification(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request["email"], [
                "email" => "required|email"
            ]);

            if ($validator->fails()) {
                throw new Exception("Validation error.", 400, $validator->errors());
            }

            $user = User::where("email", $request["email"])->first() ?? null;

            if (!$user) {
                throw new Exception("User with this email not found.", 404);
            }

            $token = Str::ulid();
            $user->notify(new AuthorizeUserEmail($token));
            $user->verification_token = $token;
            $user->token_expires_at = Carbon::now()->addMinutes(15);

            $user->save();

            return $this->onSuccess(null, "OTP sent to {{$user->email}}");
        } catch (Exception $e) {
            return $this->onFailure($e->getTrace(), $e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function verifyEmail(Request $request)
    {
        try {
            $token = $request->query("token");
            $validator = Validator::make($token, [
                "token" => "required"
            ]);

            if ($validator->fails()) {
                throw new Exception("Token not found.", 400, $validator->errors());
            }

            $user = User::where("verification_token", $token)->first() ?? null;

            if (!$user){
                throw new Exception("User not found.", 404);
            }

            if ($user->hasVerifiedEmail()){
                throw new Exception("User is already verified", 403);
            }

            if ($token !== $user->verification_token || Carbon::now()->timestamp > $user->token_expires_at) {
                throw new Exception("Invalid OTP.", 400);
            }

            $user->markEmailAsVerified();


        } catch (Exception $e) {
            return $this->onFailure($e->getTrace(), $e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function manualLogin(Request $request): JsonResponse
    {
        $input = $request->all();
        try {
            $validator = Validator::make($input, [
                "username" => "required|string",
                "password" => "required|string|min:8"
            ]);

            if ($validator->fails()) {
                throw new Exception("Validation error.", 400, $validator->errors());
            }

            $user = User::where("email", $input["username"])
                ->orWhere("username", $input["username"])
                ->first() ?? null;

            if (!$user || !Hash::check($input["password"], $user->password)){
                throw new Exception("Invalid credentials.", 400);
            }

            $token = $user->createToken("user_token")->accessToken;

            $response = ["access_token" => $token];

            return $this->onSuccess($response, "Login successful");
        } catch (Exception $e) {
            return $this->onFailure($e->getTrace(), $e->getMessage(), $e->getCode() ?: 500);
        }
    }

}
