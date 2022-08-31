<?php

namespace App\Http\Requests\Mobile;
class UpdateUserRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'nick_name' => 'nullable|string|max:50',
            'sex' => 'nullable|string|in:0,1,2',
            'avatar' => 'nullable|string',
            'label' => 'nullable|max:200'
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            //
        ];
    }
}
