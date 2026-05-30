<?php
namespace App\Traits;

trait ApiResponse
{
    protected function sendResponse($data, $message = 'Success', $statusCode = 200)
    {
        return response()->json([
            'statusCode' => $statusCode,
            'data'       => $data,
            'message'    => $message,
        ], $statusCode);
    }

    protected function sendError($message, $statusCode = 400, $data = null)
    {
        return response()->json([
            'statusCode' => $statusCode,
            'data'       => $data,
            'message'    => $message,
        ], $statusCode);
    }
}
