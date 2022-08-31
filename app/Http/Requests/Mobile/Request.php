<?php

namespace App\Http\Requests\Mobile;

use App\Constants\ResponseCode;
use App\Exceptions\BusinessException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;
use Pearl\RequestValidate\RequestAbstract;

class Request extends RequestAbstract
{
    /**
     * 格式化验证信息
     * @param  Validator  $validator
     *
     * @return JsonResponse
     * @throws BusinessException
     */
    protected function formatErrors(Validator $validator): JsonResponse
    {
        $errorMsgs = $validator->getMessageBag()->toArray();
        if(!empty($errorMsgs)) {
            $errorMsgs = array_values($errorMsgs);
            throw new  BusinessException(ResponseCode::PARAM_ILLEGAL, $errorMsgs[0][0]);
        }
       return true;
    }

}
