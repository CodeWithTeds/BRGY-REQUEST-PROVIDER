<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait JsonResponds
{
    protected function jsonMessage(string $message, int $status = 200, array $extra = []): JsonResponse
    {
        return response()->json(array_merge(['message' => $message], $extra), $status);
    }

    protected function jsonSuccess(array $data = [], int $status = 200): JsonResponse
    {
        return response()->json(array_merge(['success' => true], $data), $status);
    }

    protected function jsonError(string $message, int $status = 422, array $errors = []): JsonResponse
    {
        return response()->json(['message' => $message, 'errors' => $errors], $status);
    }

    protected function jsonDeleted($id, string $resource = 'resource'): JsonResponse
    {
        return $this->jsonMessage(ucfirst($resource) . ' deleted.', 200, ['id' => $id]);
    }

    protected function jsonUpdated($id, array $payload = [], string $message = 'Updated'): JsonResponse
    {
        return $this->jsonMessage($message, 200, array_merge(['id' => $id], $payload));
    }
}