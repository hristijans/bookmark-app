<?php

namespace App\Http\Requests\Feed;

use Illuminate\Foundation\Http\FormRequest;

class StoreFeedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'url' => ['required', 'string', 'url', 'max:2048'],
            'type' => ['required', 'in:xml,json'],
        ];
    }

    public function messages(): array
    {
        return [
            'url.required' => 'Please provide the feed URL.',
            'url.url' => 'The feed URL must be a valid URL.',
            'type.in' => 'Feed type must be XML or JSON.',
        ];
    }
}
