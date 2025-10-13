<?php

namespace App\Traits;

use App\Models\SupportingDocument;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

trait DocumentStreaming
{
    /**
     * Stream a supporting document file inline if it belongs to the given parent ID.
     */
    protected function streamSupportingDocument(int $parentId, int $docId, string $foreignKey): BinaryFileResponse
    {
        $document = SupportingDocument::where('id', $docId)
            ->where($foreignKey, $parentId)
            ->firstOrFail();

        $path = $document->file_path ?? null;
        if (!$path || !Storage::disk('public')->exists($path)) {
            abort(404, 'File not found');
        }

        $fullPath = Storage::disk('public')->path($path);
        $mime = File::mimeType($fullPath) ?? 'application/octet-stream';
        $filename = basename($fullPath);

        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}