<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EvidenceFileController extends Controller
{
    public function serve(Request $request, string $path): BinaryFileResponse
    {
        $storagePath = 'questionnaire-evidence/'.$path;

        if (! Storage::disk('local')->exists($storagePath)) {
            abort(404);
        }

        $absolutePath = Storage::disk('local')->path($storagePath);
        $mimeType = mime_content_type($absolutePath) ?: 'application/octet-stream';
        $fileName = basename($absolutePath);

        $disposition = str_starts_with($mimeType, 'image/') ? 'inline' : 'attachment';

        return response()->file($absolutePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => "{$disposition}; filename=\"{$fileName}\"",
        ]);
    }
}
