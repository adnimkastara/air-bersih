<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BrandingAssetController extends Controller
{
    public function show(string $path): Response
    {
        $normalizedPath = ltrim(str_replace('\\', '/', $path), '/');

        abort_if($normalizedPath === '' || Str::contains($normalizedPath, '..'), 404);
        abort_unless(Storage::disk('public')->exists($normalizedPath), 404);

        $absolutePath = Storage::disk('public')->path($normalizedPath);
        $mimeType = mime_content_type($absolutePath) ?: 'application/octet-stream';

        return response()->file($absolutePath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }
}
