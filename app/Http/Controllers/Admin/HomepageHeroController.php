<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateHomepageHeroRequest;
use App\Models\HomepageHero;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class HomepageHeroController extends Controller
{
    public function edit()
    {
        return view('admin.homepage-hero', ['hero' => HomepageHero::findOrFail(HomepageHero::SETTINGS_ID)]);
    }

    public function update(UpdateHomepageHeroRequest $request)
    {
        $newFiles = [];
        $disk = Storage::disk('public');
        try {
            foreach (['media', 'poster'] as $field) {
                if ($request->hasFile($field)) {
                    $path = $request->file($field)->store('homepage-hero', 'public');
                    if (!$path) {
                        throw ValidationException::withMessages([$field => 'The upload could not be saved. Please try again.']);
                    }
                    $newFiles[$field] = $path;
                }
            }

            $obsolete = DB::transaction(function () use ($request, $newFiles, $disk) {
                $hero = HomepageHero::lockForUpdate()->findOrFail(HomepageHero::SETTINGS_ID);
                $old = [$hero->media_path, $hero->poster_path];
                $type = $request->validated('media_type');
                $media = $newFiles['media'] ?? ($hero->media_type === $type ? $hero->media_path : null);
                if (!$media && ($request->boolean('enabled') || $hero->media_path && $type !== $hero->media_type)) {
                    throw ValidationException::withMessages(['media' => "Choose a {$type} file for this banner."]);
                }
                if ($request->boolean('enabled') && !$disk->exists($media)) {
                    throw ValidationException::withMessages(['media' => 'The saved file is missing. Upload a replacement.']);
                }

                $hero->fill($request->safe()->only(['enabled', 'media_type', 'headline', 'subtext', 'button_label', 'button_link']));
                $hero->media_path = $media;
                $hero->poster_path = $type === 'video'
                    ? ($newFiles['poster'] ?? ($request->boolean('remove_poster') ? null : $hero->poster_path))
                    : null;
                $hero->save();

                return array_diff(array_filter($old), [$hero->media_path, $hero->poster_path]);
            });
        } catch (\Throwable $error) {
            $disk->delete(array_values($newFiles));
            throw $error;
        }

        DB::afterCommit(fn () => $disk->delete(array_values($obsolete)));
        return redirect()->route('admin.homepage-hero')->with('success', 'Homepage hero saved.');
    }
}
