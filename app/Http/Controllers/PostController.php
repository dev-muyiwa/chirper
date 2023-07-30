<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Models\Post;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function createPost(Request $request): JsonResponse
    {
        try {
            $request->validate([
               "title" => "required|string|max:255",
               "media" => "nullable|array|max:4"
            ]);

            // Upload the media to cloudinary and store the links to the db.

            $user = Auth::user();
            $post = $user->posts()->create($request->all());

            $user->increment("chirp_count");

            return $this->onSuccess($post, "Post created.", 201);
        } catch (Exception $e) {
            return $this->onFailure($e, $e->getMessage());
        }
    }

    public function getPost(Request $request): JsonResponse
    {
        try {
            $post = Post::find($request["postId"]);
            if (!$post){
                throw new CustomException("Post not found.");
            }

            $post->increment("view_count");

            return $this->onSuccess($post, "Post fetched.");
        } catch (Exception $e) {
            return $this->onFailure($e, $e->getMessage());
        }
    }

    public function deletePost(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $post = $user->posts()->find($request["postId"]);

            if ($post) {
                throw new CustomException("Post not found.", CustomException::NOT_FOUND);
            }

            $post->delete();
            $user->decrement("chirp_count");

            return $this->onSuccess(null, "Post deleted.");
        } catch (Exception $e) {
            return $this->onFailure($e, $e->getMessage());
        }
    }
}
