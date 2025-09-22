<?php

namespace App\Http\Controllers\Media;

use App\Helpers\FormatBytes;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class FileManagerController extends Controller
{
    public function index()
    {
        $files = $this->_getFiles();

        return Inertia::render('FileManager/Index', [
            'files' => $files
        ]);
    }

    /**
     * Get all files in the given path.
     *
     * @param string $path
     * @return LengthAwarePaginator
     */
    protected function _getFiles($path = '')
    {
        $disk = Storage::disk('local');

        $allFiles = collect($disk->allFiles($path))
            ->filter(fn($file) => !preg_match('/^\./', basename($file)))
            ->values();

        if ($allFiles->isEmpty()) {
            return [];
        }

        $files = $allFiles->map(function ($file) use ($disk) {
            return [
                'path'           => $file,
                'name'           => basename($file),
                'size'           => FormatBytes::formatBytes($disk->size($file)),
                'last_modified'  => Carbon::createFromTimestamp($disk->lastModified($file))->format('d M Y'),
                'file_extension' => pathinfo($file, PATHINFO_EXTENSION),
            ];
        })
            ->sortByDesc('last_modified')
            ->values();

        $page = request()->input('page', 1);
        $perPage = 20;

        return new LengthAwarePaginator(
            $files->forPage($page, $perPage),
            $files->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query()
            ]
        );
    }

    /**
     * Download a file from the given path.
     *
     * @param string $file
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function download($file)
    {

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */

        $disk = Storage::disk('local');
        $exists = $disk->exists($file);

        if (! $exists) {
            abort(404, 'File not found');
        }

        return $disk->download($file);
    }

    /**
     * Delete a file from the given path.
     *
     * @param string $filePath
     * @return \Illuminate\Http\JsonResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function destroy($filePath)
    {
        try {
            $disk = Storage::disk('local');
            $exists = $disk->exists($filePath);

            if (! $exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found'
                ], 404);
            }

            $disk->delete($filePath);

            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'data' => $th->getMessage(),
            ]);
        }
    }
}
