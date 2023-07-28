<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use Exception;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\Concerns\Has;

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
            $user = User::where("handle", $id)
                ->orWhere("id", $id)->first();
            if (!$user) {
                throw new CustomException("User not found.");
            }

            $user->update($request->all());

            return $this->onSuccess($user, "User profile updated.");
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

            $user = User::where("handle", $id)
                ->orWhere("id", $id)->first();

            if (!$user) {
                throw new CustomException("User not found.");
            }

            if (!Hash::check($old_password, $user->password)) {
                throw new CustomException("Password doesn't match.");
            }

            $user->update(["password" => Hash::make($new_password)]);

            return $this->onSuccess(null, "User password updated.");
        } catch (Exception $e) {
            return $this->onFailure($e, $e->getMessage());
        }
    }
}
