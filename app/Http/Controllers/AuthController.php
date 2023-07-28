<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Models\User;
use App\Notifications\AuthorizeUserEmail;
use Exception;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function signup(Request $request): JsonResponse
    {
        $input = $request->all();
        try {
            $validator = Validator::make($input, [
                "first_name" => "required|string",
                "last_name" => "required|string",
                "handle" => "required|string|unique:users,handle",
                "email" => "required|email|unique:users,email",
                "password" => "required|string|confirmed|min:8"
            ]);

            if ($validator->fails()) {
                throw new CustomException($validator->messages(), CustomException::BAD_REQUEST);
            }

            $user = User::create([
                "full_name" => "{$input["first_name"]} {$input["last_name"]}",
                "handle" => $input["handle"],
                "email" => $input["email"],
                "password" => Hash::make($input["password"])
            ]);

            event(new Registered($user));

            $token = $user->createToken("user_token")->plainTextToken;

            return $this->onSuccess(["access_token" => $token], "Registration successful.", 201);
        } catch (Exception|CustomException $e) {
            return $this->onFailure($e, $e->getMessage());
        }
    }

    // Not in use. Can be used for mobile verification.
    public function startEmailVerification(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $otp = rand(1000_000, 999_999);
            $user->notify(new AuthorizeUserEmail($otp));
            $user->otp()->updateOrCreate(["otp" => $otp]);

            return $this->onSuccess(null, "OTP sent to {{$user->email}}");
        } catch (Exception $e) {
            return $this->onFailure($e, $e->getMessage());
        }
    }

    public function resendNotification(Request $request): JsonResponse
    {
        $request->user()->sendEmailVerificationNotification();

        return $this->onSuccess(null, "Verification email sent.");
    }

    public function verifyEmail($id, $hash): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                throw new CustomException("User not found.", CustomException::NOT_FOUND);
            }

            if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {
                throw new CustomException("Invalid hash.", CustomException::FORBIDDEN);
            }

            if (!$user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();

                event(new Verified($user));
            }

            return $this->onSuccess(null, "Email verification successful.");
        } catch (Exception|CustomException $e) {
            return $this->onFailure($e, $e->getMessage());
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
                throw new CustomException($validator->messages(), CustomException::BAD_REQUEST);
            }

            $user = User::where("email", $input["username"])
                ->orWhere("handle", $input["username"])
                ->first() ?? null;

            if (!$user) {
                throw new CustomException("User not found.", CustomException::NOT_FOUND);
            }

            if ($user->google_id && !$user->password) {
                throw new CustomException("You are only able to sign in via Google.", CustomException::FORBIDDEN);
            }

            if (!Hash::check($input["password"], $user->password)) {
                throw new CustomException("Invalid credentials.", CustomException::BAD_REQUEST);
            }

            $token = $user->createToken("user_token")->plainTextToken;

            return $this->onSuccess(["access_token" => $token], "Login successful");
        } catch (Exception|CustomException $e) {
            return $this->onFailure($e, $e->getMessage());
        }
    }

    public function continueWithGoogle(): \Symfony\Component\HttpFoundation\RedirectResponse|RedirectResponse
    {
        return Socialite::driver("google")->stateless()->redirect();
    }

    public function googleCallback(Request $request): JsonResponse
    {
        try {
            $google_user = Socialite::driver("google")->stateless()->user();
            $user = User::where('google_id', $google_user->id)
                ->orWhere("email", $google_user->email)
                ->first() ?? null;
            if (!$user) {
                $user = User::create([
                    "full_name" => $google_user->name,
                    "handle" => $google_user->email . $google_user->id,
                    "email" => $google_user->email,
                    "google_id" => $google_user->id,
                    "avatar" => $google_user->avatar
                ]);

            } else {
                $user->update([
                    "google_id" => $google_user->id
                ]);
            }
            if ($google_user->user["email_verified"]) {
                $user->markEmailAsVerified();
                event(new Verified($user));
            }

            $token = $user->createToken("user_token")->plainTextToken;

            return $this->onSuccess(["access_token" => $token], "Login via Google successful.");
        } catch (Exception $e) {
            return $this->onFailure($e, $e->getMessage());
        }
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $email = $request->email;
        try {
            $request->validate(["email" => "required|email"]);

            $status = Password::sendResetLink($request->only("email"));

            if ($status !== Password::RESET_LINK_SENT) {
                throw new CustomException(__($status), CustomException::SERVER_ERROR);
            }

            return $this->onSuccess(null, __($status));
        } catch (Exception $e) {
            return $this->onFailure($e, $e->getMessage());
        }
    }

    public function resetPassword(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'token' => 'required',
                'email' => 'required|email',
                'password' => 'required|min:8|confirmed',
            ]);

            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user, string $password) {
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ])->setRememberToken(Str::random(60));

                    $user->save();

                    event(new PasswordReset($user));
                }
            );

            if ($status !== Password::PASSWORD_RESET) {
                throw new CustomException("Unable to reset password.", CustomException::SERVER_ERROR);
            }

            return $this->onSuccess(__($status), "Password reset successfully.");
        } catch (Exception $e) {
            return $this->onFailure($e, $e->getMessage());
        }
    }


}
