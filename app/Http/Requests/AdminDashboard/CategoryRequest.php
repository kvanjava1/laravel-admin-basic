<?php

namespace App\Http\Requests\AdminDashboard;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('category') ? $this->route('category')->id : null;

        return [
            'category_group_id' => 'required|exists:category_groups,id',
            'parent_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('categories', 'slug')
                    ->where('category_group_id', $this->category_group_id)
                    ->ignore($id),
            ],
            'is_active' => 'boolean',
        ];
    }
}
