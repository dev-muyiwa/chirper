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

    public function getBookmarks(): JsonResponse
    {
        try {
            $bookmarks = Auth::user()->bookmarks()->paginate();

            return $this->onSuccess($bookmarks, "User bookmarks fetched");
        } catch (Exception $e) {
            return $this->onFailure($e, $e->getMessage());
        }
    }

    public function getFollowers(Request $request, $handle): JsonResponse
    {
        try {
            $user = User::where("handle", $handle)
                ->orWhere("id", $handle)->first();
            if (!$user) {
                throw new CustomException("User not found.");
            }

            $followers = $user->followers()->get();

            return $this->onSuccess($followers, "Followers fetched.");
        } catch (Exception $e) {
            return $this->onFailure($e, $e->getMessage());
        }
    }

    public function getFollowings(Request $request, $handle): JsonResponse
    {
        try {
            $user = User::where("handle", $handle)
                ->orWhere("id", $handle)->first();
            if (!$user) {
                throw new CustomException("User not found.");
            }

            $followings = $user->followings()->get();

            return $this->onSuccess($followings, "Followings fetched.");
        } catch (Exception $e) {
            return $this->onFailure($e, $e->getMessage());
        }
    }

    public function followUser(Request $request): JsonResponse
    {
        try {
            $handle = $request["user"];
            $user = User::where("handle", $handle)
                ->orWhere("id", $handle)->first();

            if (!$user) {
                throw new CustomException("User not found.");
            }

            $auth_user = Auth::user();

            if ($auth_user->id === $user->id){
                throw new CustomException("You cannot follow yourself.", CustomException::FORBIDDEN);
            }

            if (!$auth_user->followings->contains("id", $user->id)) {
                $auth_user->increment("followings_count");
                $user->increment("followers_count");
                $auth_user->followings()->attach($user);
            }



            return $this->onSuccess(null, "You followed {$handle}.");
        } catch (Exception $e) {
            return $this->onFailure($e, $e->getMessage());
        }
    }

    public function unfollowUser(Request $request): JsonResponse
    {
        try {
            $handle = $request["user"];
            $user = User::where("handle", $handle)
                ->orWhere("id", $handle)->first();

            if (!$user) {
                throw new CustomException("User not found.");
            }

            $auth_user = Auth::user();

            if ($auth_user === $user){
                throw new CustomException("You cannot follow yourself.", CustomException::FORBIDDEN);
            }

            if ($auth_user->followings->contains("id", $user->id)) {
                $auth_user->decrement("followings_count");
                $user->decrement("followers_count");
                $auth_user->followings()->detach($user);
            }

            return $this->onSuccess(null, "You unfollowed {$handle}.");
        } catch (Exception $e) {
            return $this->onFailure($e, $e->getMessage());
        }
    }
}
