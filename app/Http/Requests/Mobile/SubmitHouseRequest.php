<?php

namespace App\Http\Requests\Mobile;
use App\Rules\ImageData;

class SubmitHouseRequest extends  Request
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
                        'type' => 'required|numeric|in:1,2,3',
                        'area' => 'required|numeric',
                        'floor' => 'required|numeric',
                        'is_elevator' => 'required|numeric|between:0,1',
                        'price_range_min' => 'required|numeric',
                        'price_range_max' => 'required|numeric',
                        'vacancy_time' => 'required|date',
                        'halls' => 'required|numeric',
                        'rooms' => 'required|numeric',
                        'facilities' => 'nullable|string',
                        'lon' => 'required|numeric',
                        'lat' => 'required|numeric',
                      //  'address'=>'required|string',
                        'images' => ['string', 'required'],
                        'content' => 'nullable|string',
                        'roommate' => 'nullable|string'
                    ];

            case 'PUT':  //update
                if (Request::has('id')) {   //更新
                    return [
                        'type' => 'required|numeric|in:1,2,3',
                        'area' => 'required|numeric',
                        'floor' => 'required|numeric',
                        'is_elevator' => 'required|numeric|between:0,1',
                        'price_range_min' => 'required|numeric',
                        'price_range_max' => 'required|numeric',
                        'vacancy_time' => 'required|date',
                        'halls' => 'required|numeric',
                        'rooms' => 'required|numeric',
                        'facilities' => 'nullable|string',
                        'lon' => 'required|numeric',
                        'lat' => 'required|numeric',
                        'address'=>'required|string',
                        'images' => ['string', 'required'],
                        'content' => 'nullable|string',
                        'roommate' => 'nullable|string'
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
