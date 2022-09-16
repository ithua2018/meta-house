<?php


namespace App\Http\Requests\Mobile;


class InvitationRecordRequest extends  Request
{
    public function authorize(): bool
    {
        switch ($this->method())
        {
            case 'POST':
            case 'PUT':
                return true;
            case 'GET':
            default:
            {
                return false;
            }
        }
    }

    /** @return array<mixed> */
    public function rules(): array
    {
        switch ($this->method()) {
            case 'POST':    //create
                return [
                    'invited_uid' => 'required|int',
                    'house_id' => 'required|int',
                    'viewing_date' => 'required|date_format:Y-m-d',
                    'viewing_time' => 'required|date_format:H:i',
                    'remark' => 'nullable|string'
                ];
            case 'put':
                if (Request::has('id')) {   //更新
                    return [
                        'status' => 'required|int|in:1,2'
                    ];
                }
            default:
            {
                return [];
            }
        }
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
