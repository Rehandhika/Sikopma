<?php

namespace App\Http\Controllers;

use App\Services\Storage\FileSecurityServiceInterface;
use App\Services\Storage\StorageOrganizerInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller untuk menangani download file private dengan signed URLs.
 */
class FileDownloadController extends Controller
{
    public function __construct(
        protected FileSecurityServiceInterface $securityService,
        protected StorageOrganizerInterface $storageOrganizer
    ) {}

    /**
     * Download file dengan signed URL.
     *
     * @return StreamedResponse|\Illuminate\Http\Response
     */
    public function download(Request $request)
    {
        // Decode path from base64
        $encodedPath = $request->route('path');
        $path = base64_decode($encodedPath);
        $disk = $request->route('disk', 'local');

        // Sanitize path
        $path = $this->securityService->sanitizePath($path);

        // Log access
        $this->securityService->logFileAccess($path, 'download', [
            'disk' => $disk,
        ]);

        // Check if file exists
        if (! Storage::disk($disk)->exists($path)) {
            abort(404, __('filestorage.storage.file_not_found', ['path' => $path]));
        }

        // Get file info
        $filename = basename($path);
        $mimeType = Storage::disk($disk)->mimeType($path);

        // Stream the file
        return Storage::disk($disk)->download($path, $filename, [
            'Content-Type' => $mimeType,
        ]);
    }

    /**
     * View file inline (for images/PDFs).
     *
     * @return StreamedResponse|\Illuminate\Http\Response
     */
    public function view(Request $request)
    {
        // Decode path from base64
        $encodedPath = $request->route('path');
        $path = base64_decode($encodedPath);
        $disk = $request->route('disk', 'local');

        // Sanitize path
        $path = $this->securityService->sanitizePath($path);

        // Log access
        $this->securityService->logFileAccess($path, 'view', [
            'disk' => $disk,
        ]);

        // Check if file exists
        if (! Storage::disk($disk)->exists($path)) {
            abort(404, __('filestorage.storage.file_not_found', ['path' => $path]));
        }

        // Get file info
        $mimeType = Storage::disk($disk)->mimeType($path);

        // Return file for inline viewing
        return response()->stream(
            function () use ($disk, $path) {
                $stream = Storage::disk($disk)->readStream($path);
                fpassthru($stream);
                fclose($stream);
            },
            200,
            [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline',
            ]
        );
    }
}
