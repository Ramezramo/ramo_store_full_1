<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ImageGalleryImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ImageGalleryController extends Controller
{
    private const UPLOAD_DIRECTORY = 'image-gallery';

    public function index(Request $request)
    {
        $query = ImageGalleryImage::query()->latest();
        $search = trim((string) $request->input('search', ''));

        if ($search !== '') {
            $query->where('original_name', 'ilike', '%' . $search . '%');
        }

        $images = $query->paginate(30)->withQueryString();

        return view('admin.image-gallery', compact('images', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'images' => ['required', 'array', 'min:1', 'max:30'],
            'images.*' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp,gif,avif', 'max:10240'],
        ], [
            'images.max' => 'You can upload a maximum of 30 images at once. You can repeat bulk uploads as often as needed.',
            'images.*.max' => 'Each image must be 10 MB or smaller.',
        ]);

        $storedPaths = [];
        $created = [];

        try {
            DB::transaction(function () use ($request, &$storedPaths, &$created) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store(self::UPLOAD_DIRECTORY, 'public');
                    $storedPaths[] = $path;

                    $dimensions = @getimagesize($image->getRealPath()) ?: null;
                    $created[] = ImageGalleryImage::create([
                        'path' => $path,
                        'original_name' => $image->getClientOriginalName(),
                        'mime_type' => $image->getMimeType() ?: 'application/octet-stream',
                        'file_size' => $image->getSize(),
                        'width' => $dimensions[0] ?? null,
                        'height' => $dimensions[1] ?? null,
                        'uploaded_by' => $request->user()?->id,
                    ]);
                }
            });
        } catch (Throwable $exception) {
            foreach ($storedPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            report($exception);

            return back()->withInput()->with('error', 'The images could not be saved. Please try again.');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => count($created) . ' image(s) added to the gallery.',
                'images' => collect($created)->map(fn (ImageGalleryImage $image) => [
                    'id' => $image->id,
                    'url' => $image->url,
                    'name' => $image->original_name,
                ])->values(),
            ], 201);
        }

        return redirect()
            ->route('admin.image-gallery')
            ->with('success', count($created) . ' image(s) added to the gallery. Copy any image URL to reuse it in banners and other content.');
    }

    public function destroy(ImageGalleryImage $image)
    {
        Storage::disk('public')->delete($image->path);
        $image->delete();

        return back()->with('success', 'Image removed from the gallery.');
    }
}
