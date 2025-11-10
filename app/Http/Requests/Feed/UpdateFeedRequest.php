<?php

namespace App\Http\Requests\Feed;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFeedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'url' => ['sometimes', 'required', 'string', 'url', 'max:2048'],
            'type' => ['sometimes', 'required', 'in:xml,json'],
        ];
    }

    public function messages(): array
    {
        return [
            'url.url' => 'The feed URL must be a valid URL.',
            'type.in' => 'Feed type must be XML or JSON.',
        ];
    }
}
