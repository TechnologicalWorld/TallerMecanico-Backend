<?php

namespace App\Traits;

trait ApiResponse
{
    protected function success($data = null, string $message = '', int $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function error(string $message = '', int $code = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    protected function created($data = null, string $message = 'Recurso creado')
    {
        return $this->success($data, $message, 201);
    }

    protected function noContent()
    {
        return response()->json(null, 204);
    }
}