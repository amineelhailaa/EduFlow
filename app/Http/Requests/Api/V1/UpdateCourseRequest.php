<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'teacher_id' => 'sometimes|nullable|integer|exists:users,id',
            'interest_id' => 'sometimes|nullable|integer|exists:interests,id',
            'price' => 'sometimes|integer|min:0',
        ];
    }
}
