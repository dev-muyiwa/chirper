<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use Exception;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function user(string $id): JsonResponse
    {
        try {
            $user = User::where("handle", $id)
                ->orWhere("id", $id)->first();
            if (!$user) {
                throw new CustomException("User not found.");
            }

            return $this->onSuccess($user, "User fetched.");
        } catch (Exception $e) {
            return $this->onFailure($e, $e->getMessage());
        }
    }

    public function users(): JsonResponse
    {
        try {
            $users = User::paginate();

            return $this->onSuccess($users, "All users fetched.");
        } catch (Exception $e) {
            return $this->onFailure($e, $e->getMessage());
        }
    }

    public function updateUserProfile(Request $request, string $id): JsonResponse
    {
        try {
            Auth::user()->update($request->all());

            return $this->onSuccess(Auth::user(), "User profile updated.");
        } catch (Exception $e) {
            return $this->onFailure($e, $e->getMessage());
        }
    }

    public function updateUserPassword(Request $request, string $id): JsonResponse
    {
        [$old_password, $new_password] = $request->only("old_password", "new_password");
        try {
            $request->validate([
                'old_password' => 'required|string',
                'new_password' => 'required|string',
            ]);

            $user = Auth::user();

            if (!Hash::check($old_password, $user->password)) {
                throw new CustomException("Password doesn't match.");
            }

            $user->update(["password" => Hash::make($new_password)]);

            // Logout other devices and/or destroy other tokens.

            return $this->onSuccess(null, "User password updated.");
        } catch (Exception $e) {
            return $this->onFailure($e, $e->getMessage());
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            // Delete only the current token

            return self::onSuccess(null, message: "User logged out successfully.");
        } catch (Exception $e) {
            return $this->onFailure($e, $e->getMessage());
        }
    }

    public function logoutFromAllDevices(): JsonResponse
    {
        try {
            Auth::user()->tokens()->delete();

            return self::onSuccess(null, message: "User logged out from all devices successfully.");
        } catch (Exception $e) {
            return $this->onFailure($e, $e->getMessage());
        }
    }
}
