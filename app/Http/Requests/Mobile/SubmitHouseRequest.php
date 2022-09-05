<?php

namespace App\Http\Requests\Mobile;
use App\Rules\ImageData;
use Illuminate\Support\Facades\Validator;

class SubmitHouseRequest extends  Request
{
    public function authorize(): bool
    {
        Validator::extend('checkHouseStructure', function ($attribute, $value, $parameters, $validator) {
            $arr = explode('-', $value);
            if(empty($arr) || count($arr) < 4) return false;
            foreach($arr as $val) {
                if(!is_numeric($val) || (int)$val<0) return false;
            }
            return true;
        });

        Validator::extend('checkRoommate', function ($attribute, $value, $parameters, $validator) {
            $arr = explode(',', $value);
            if(!empty($arr)) {
                foreach($arr as $val) {
                    if(!is_numeric($val) || !in_array($val,[0, 1])) return false;
                }
            }
            return true;
        });
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
                        'type' => 'required|numeric|in:1,2,3,4',
                        'area' => 'required|numeric',
                        'lease_type' => 'required|numeric|in:1,2,3,4',
                        'lease_aging' => 'required|numeric',

//                        'floor' => 'required|numeric',
                       'is_elevator' => 'required|numeric|between:0,1',
                        'price_range_min' => 'required|numeric',
                        'price_range_max' => 'required|numeric',
                        'vacancy_time' => 'required|date',
                        'house_structure' => 'required|checkHouseStructure',
//                        'halls' => 'required|numeric',
//                        'rooms' => 'required|numeric',

                        'facilities' => 'nullable|string',
                        'lon' => 'required|numeric',
                        'lat' => 'required|numeric',
                      //  'address'=>'required|string',
                        'images' => ['string', 'required'],
                        'title' => 'nullable|string',
                        'desc' => 'nullable|string',
                        'roommate' => 'nullable|string|checkRoommate',
                        'limit_people_number' => 'nullable|numeric',
                        'address_extra' => 'nullable|string'

                    ];

            case 'PUT':  //update
                if (Request::has('id')) {   //更新
                    return [
                        'type' => 'required|numeric|in:1,2,3,4',
                        'area' => 'required|numeric',
                        'lease_type' => 'required|numeric|in:1,2,3,4',
                        'lease_aging' => 'required|numeric',
//                        'floor' => 'required|numeric',
                        'is_elevator' => 'required|numeric|between:0,1',
                        'price_range_min' => 'required|numeric',
                        'price_range_max' => 'required|numeric',
                        'vacancy_time' => 'required|date',
                        'house_structure' => 'required|checkHouseStructure',
//                        'halls' => 'required|numeric',
//                        'rooms' => 'required|numeric',
                        'facilities' => 'nullable|string',
                        'lon' => 'required|numeric',
                        'lat' => 'required|numeric',
                     //   'address'=>'required|string',
                        'images' => ['string', 'required'],
                        'title' => 'nullable|string',
                        'desc' => 'nullable|string',
                        'roommate' => 'nullable|string|checkRoommate',
                        'limit_people_number' => 'nullable|numeric',
                        'address_extra' => 'nullable|string'
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
