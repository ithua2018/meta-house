<?php

namespace App\Http\Requests\Mobile;
class ProfileUpdateRequest extends Request
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
            //'username' => 'required',
            'password' => 'required_if:login_type,2|min:6|max:50',
            'code' => 'required_if:login_type,1|size:4',
            'mobile' => 'required||regex:/^1[345789][0-9]{9}$/',
            'login_type'=>'required|int|between: 1,2'

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
