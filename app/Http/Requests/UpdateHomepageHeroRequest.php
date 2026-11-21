<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHomepageHeroRequest extends FormRequest
{
    public function rules(): array
    {
        $image = ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];
        $video = ['file', 'mimetypes:video/mp4,video/webm', 'max:20480'];

        return [
            'enabled' => ['required', 'boolean'],
            'media_type' => ['required', Rule::in(['image', 'video'])],
            'media' => ['nullable', ...($this->input('media_type') === 'video' ? $video : $image)],
            'poster' => ['prohibited_unless:media_type,video', 'nullable', ...$image],
            'remove_poster' => ['sometimes', 'boolean'],
            'headline' => ['nullable', 'string', 'max:160'],
            'subtext' => ['nullable', 'string', 'max:600'],
            'button_label' => ['nullable', 'string', 'max:60'],
            'button_link' => ['bail', 'nullable', 'string', 'max:2048', function ($attribute, $value, $fail) {
                $relative = str_starts_with($value, '/') && !str_starts_with($value, '//');
                $absolute = in_array(strtolower((string) parse_url($value, PHP_URL_SCHEME)), ['https', 'http'], true)
                    && filter_var($value, FILTER_VALIDATE_URL);
                if (str_contains($value, '\\') || preg_match('/[[:cntrl:][:space:]]/', $value) || (!$relative && !$absolute)) {
                    $fail('Enter a store path starting with / or a full HTTP or HTTPS link.');
                }
            }],
        ];
    }
}
