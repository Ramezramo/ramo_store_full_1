<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ImageGalleryImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
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
        $validator = Validator::make($request->all(), [
            'images' => ['required', 'array', 'min:1', 'max:30'],
            'images.*' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp,gif,avif', 'max:10240'],
        ], [
            'images.required' => 'Choose at least one image before adding it to the gallery.',
            'images.array' => 'The image selection could not be read. Please choose the images again.',
            'images.max' => 'You can upload a maximum of 30 images at once. You can repeat bulk uploads as often as needed.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($this->friendlyValidationErrors($request, $validator))
                ->withInput();
        }

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

    private function friendlyValidationErrors(Request $request, $validator): array
    {
        $messages = [];
        $files = $request->allFiles()['images'] ?? [];

        foreach ($validator->errors()->messages() as $field => $errors) {
            if (preg_match('/^images\\.(\\d+)$/', $field, $matches)) {
                $index = (int) $matches[1];
                $file = $files[$index] ?? null;
                $name = $file?->getClientOriginalName() ?: 'Selected image #' . ($index + 1);
                $failedRules = array_keys($validator->failed()[$field] ?? []);

                if (in_array('Max', $failedRules, true)) {
                    $messages[$field] = $name . ' is larger than 10 MB. Choose a smaller version of this image.';
                } elseif (in_array('Mimes', $failedRules, true) || in_array('Image', $failedRules, true)) {
                    $messages[$field] = $name . ' is not a supported image. Upload a JPG, PNG, WEBP, GIF, or AVIF file.';
                } elseif ($file?->getError() === UPLOAD_ERR_INI_SIZE || $file?->getError() === UPLOAD_ERR_FORM_SIZE) {
                    $messages[$field] = $name . ' was rejected by the server upload limit. Ask the administrator to allow files up to 10 MB.';
                } elseif ($file?->getError() === UPLOAD_ERR_PARTIAL) {
                    $messages[$field] = $name . ' was only partially uploaded. Check the connection and try again.';
                } elseif ($file?->getError() === UPLOAD_ERR_NO_FILE) {
                    $messages[$field] = $name . ' was not received. Choose the file again and retry.';
                } elseif (in_array('Uploaded', $failedRules, true) || $file?->getError()) {
                    $messages[$field] = $name . ' could not be uploaded because of a server or connection error. It is under the 10 MB limit, so try again or contact the administrator if it continues.';
                } else {
                    $messages[$field] = $name . ': ' . ($errors[0] ?? 'This image could not be uploaded.');
                }

                continue;
            }

            $messages[$field] = $errors[0] ?? 'The selected images could not be validated.';
        }

        return $messages;
    }

    public function destroy(ImageGalleryImage $image)
    {
        Storage::disk('public')->delete($image->path);
        $image->delete();

        return back()->with('success', 'Image removed from the gallery.');
    }
}
