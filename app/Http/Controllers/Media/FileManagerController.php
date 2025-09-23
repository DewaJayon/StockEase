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
    public function index(Request $request)
    {
        $files = $this->_getFiles();

        if ($request->filled('search')) {
            $files = $this->_search($request->search);
        }

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
    private function _search($search)
    {
        $files = $this->_getFiles();

        $filteredFiles = $files->filter(function ($file) use ($search) {
            return Str::contains($file['name'], $search);
        });

        $page = request()->input('page', 1);
        $perPage = 20;

        return new LengthAwarePaginator(
            $filteredFiles->forPage($page, $perPage),
            $filteredFiles->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query()
            ]
        );
    }
}
