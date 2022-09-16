<?php

namespace App\Http\Requests\Mobile;



class MysearchHousesRequset extends  Request
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<mixed> */
    public function rules(): array
    {
        return [
            'type' => 'required|int|in: 0,1',
            'page' => 'integer',
            'limit' => 'integer'
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
