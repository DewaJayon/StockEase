<?php

namespace App\Http\Controllers\Media;

use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Support\Str;
use App\Helpers\FormatBytes;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Media\StoreFileRequest;
use Illuminate\Pagination\LengthAwarePaginator;

class FileManagerController extends Controller
{

    /**
     * Display a listing of files with optional search and filter functionality.
     *
     * This method retrieves all files from storage, applies search and filter
     * conditions (if provided in the request), and returns a paginated result
     * to be rendered with Inertia.
     *
     * Query Parameters:
     * - search (string, optional): Filter files by matching name.
     * - file_filter (string, optional): Filter files by extension/type.
     *   Use "all" to disable this filter.
     * - page (int, optional): Current page for pagination (default: 1).
     *
     * @param \Illuminate\Http\Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $files = $this->_getFiles();

        if ($request->filled('search')) {
            $files = $this->_search($files, $request->search);
        }

        if ($request->filled('file_filter') && $request->file_filter !== 'all') {
            $files = $this->_fileTypeFilter($files, $request->file_filter);
        }

        $page = $request->input('page', 1);
        $perPage = 20;

        $paginated = new LengthAwarePaginator(
            $files->forPage($page, $perPage),
            $files->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query()
            ]
        );

        return Inertia::render('FileManager/Index', [
            'files' => $paginated
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

        return $allFiles->map(function ($file) use ($disk) {
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
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Media\StoreFileRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Throwable
     */
    public function store(StoreFileRequest $request)
    {
        try {
            $data = $request->validated();
            $files = $data['file'];
            $filePath = "uploads";
            $disk = Storage::disk('local');

            foreach ($files as $file) {

                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension    = $file->getClientOriginalExtension();

                $fileName = $originalName . '.' . $extension;
                $counter  = 1;

                while ($disk->exists($filePath . '/' . $fileName)) {
                    $fileName = $originalName . " ({$counter})." . $extension;
                    $counter++;
                }

                $disk->put($filePath . '/' . $fileName, file_get_contents($file));
            }

            return redirect()->back()->with('success', 'File uploaded successfully');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'File upload failed');
        }
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

    /**
     * Search for files by name
     *
     * @param string $search
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    private function _search($files, $search)
    {
        return $files->filter(function ($file) use ($search) {
            return Str::contains(Str::lower($file['name']), Str::lower($search));
        })->values();
    }

    /**
     * Filter files by extension/type.
     *
     * @param \Illuminate\Support\Collection $files
     * @param string $file_filter The file extension/type to filter by.
     *
     * @return \Illuminate\Support\Collection
     */
    private function _fileTypeFilter($files, $file_filter)
    {
        return $files->filter(function ($file) use ($file_filter) {
            return $file['file_extension'] === $file_filter;
        })->values();
    }
}
