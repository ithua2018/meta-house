<?php

namespace App\Http\Requests\Mobile;

use Illuminate\Http\UploadedFile;

/** @property UploadedFile $file */
class UploadRequest extends  Request
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<mixed> */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file'
            ],
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
