<?php

namespace App\Http\Requests\AdminDashboard\Article;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:articles,slug',
            'content' => 'required|string',
            'excerpt' => 'required|string',
            'featured_image_id' => 'required|exists:media,id',
            'featured_image_alt' => 'nullable|string|max:255',
            'featured_image_caption' => 'nullable|string|max:255',
            'seo_title' => 'required|string|max:255',
            'seo_description' => 'required|string|max:160',
            'seo_focus_keyword' => 'required|string|max:100',
            'status' => 'required|in:draft,published,scheduled',
            'published_at' => 'nullable|date',
            'tags' => 'required|array|min:1',
            'tags.*' => 'string|max:50',
        ];
    }
}
