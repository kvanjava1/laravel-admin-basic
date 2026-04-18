<?php

namespace App\Http\Requests\AdminDashboard\Media;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMediaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'alt_text' => 'required|string|max:1000',
            'caption' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
            'category_id' => 'nullable|integer|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'crop_16_9_x' => 'required|numeric|min:0',
            'crop_16_9_y' => 'required|numeric|min:0',
            'crop_16_9_width' => 'required|numeric|min:1',
            'crop_16_9_height' => 'required|numeric|min:1',
            'crop_4_3_x' => 'required|numeric|min:0',
            'crop_4_3_y' => 'required|numeric|min:0',
            'crop_4_3_width' => 'required|numeric|min:1',
            'crop_4_3_height' => 'required|numeric|min:1',
        ];
    }
}
