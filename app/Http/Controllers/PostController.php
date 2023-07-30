<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Models\Bookmark;
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

            $post->increment("view_count");

            return $this->onSuccess($post, "Post fetched.");
        } catch (Exception $e) {
            return $this->onFailure($e, $e->getMessage());
        }
    }

    public function getPosts(Request $request): JsonResponse
    {
        try {
            $posts = Auth::user()->posts()->paginate();


            return $this->onSuccess($posts, "Post fetched.");
        } catch (Exception $e) {
            return $this->onFailure($e, $e->getMessage());
        }
    }

    public function deletePost(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $post = $user->posts()->find($request["postId"]);

            $post->delete();
            $user->decrement("chirp_count");

            return $this->onSuccess(null, "Post deleted.");
        } catch (Exception $e) {
            return $this->onFailure($e, $e->getMessage());
        }
    }

    public function addToBookmarks(Request $request): JsonResponse
    {
        try {
            $bookmark = Auth::user()->bookmarks()
                ->where('post_id', $request["postId"])
                ->first();

            if (!$bookmark) {
                Auth::user()->bookmarks()->create(["post_id" => $request["postId"]]);
            }

            return $this->onSuccess(null, "Added to bookmarks.");
        } catch (Exception $e) {
            return $this->onFailure($e, $e->getMessage());
        }
    }

    public function removeFromBookmarks(Request $request): JsonResponse
    {
        try {
            $bookmark = Auth::user()->bookmarks()
                ->where('post_id', $request["postId"])
                ->first();

            if ($bookmark) {
                $bookmark->delete();
            }

            return $this->onSuccess(null, "Deleted from bookmarks.");
        } catch (Exception $e) {
            return $this->onFailure($e, $e->getMessage());
        }
    }

    public function getPostLikes(Request $request): JsonResponse
    {
        try {
            $likes = Post::find($request["postId"])->likes()->get();

            return $this->onSuccess($likes, "Post likes fetched.");
        } catch (Exception $e) {
            return $this->onFailure($e, $e->getMessage());
        }
    }

    public function likePost(Request $request): JsonResponse
    {
        try {
            $like = Auth::user()->likes()
                ->where('post_id', $request["postId"])
                ->first();

            if (!$like) {
                Auth::user()->likes()
                    ->create(["post_id" => $request["postId"]]);
                $like->increment("likes_count");
            }


            return $this->onSuccess(null, "Added to likes.");
        } catch (Exception $e) {
            return $this->onFailure($e, $e->getMessage());
        }
    }

    public function unlikePost(Request $request): JsonResponse
    {
        try {
            $like = Auth::user()->likes()
                ->where('post_id', $request["postId"])
                ->first();

            if ($like) {
                $like->delete();
                $like->decrement("likes_count");
            }

            return $this->onSuccess(null, "Deleted from bookmarks.");
        } catch (Exception $e) {
            return $this->onFailure($e, $e->getMessage());
        }
    }
}
