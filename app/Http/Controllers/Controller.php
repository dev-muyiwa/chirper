<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Success response method.
     *
     * @param $result
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    public function onSuccess($result, string $message, int $code = 200): JsonResponse
    {
        $response = [
            'status' => "success",
            'data'    => $result,
            'message' => $message,
        ];

        return response()->json($response, $code);
    }

    /**
     * return error response.
     *
     * @param $error
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    public function onFailure($error, string $message, int $code = 400): JsonResponse
    {
        $response = [
            'status' => "failure",
            'error' => $error,
            'message' => $message,
        ];

        return response()->json($response, $code);
    }
}
