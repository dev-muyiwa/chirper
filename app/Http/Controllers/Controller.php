<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use Exception;
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
            'data' => $result,
            'message' => $message,
        ];

        return response()->json($response, $code);
    }

    /**
     * return error response.
     *
     * @param $error
     * @param string $message
     * @return JsonResponse
     */
    public function onFailure($error, string $message, $code = null): JsonResponse
    {
        $response = [
            'status' => "failure",
            'error' => $error,
            'message' => $message,
        ];

        $code = ($code) ?: 500;

        if ($error instanceof CustomException) {
            return response()->json($response, $error->getStatusCode());
        } else {
            return response()->json($response, $code);
        }
    }
}



