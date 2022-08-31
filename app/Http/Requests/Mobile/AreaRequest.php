<?php

namespace App\Http\Requests\Mobile;



class AreaRequest extends  Request
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<mixed> */
    public function rules(): array
    {
        return [
            'type' => 'required|int|between: 1,2',
            'id'=> 'required|int'
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
