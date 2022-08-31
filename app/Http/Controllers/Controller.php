<?php

namespace App\Http\Controllers;

use App\CodeResponse;
use App\Constants\ResponseCode;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    //
    protected function codeReturn(array $codeResponse, $data = null, $info = '', $isSuccess=true)
    {
        list($errno, $errmsg) = $codeResponse;
        $ret = ['errorCode' => $errno, 'errorMessage' => $info ?: $errmsg, 'success'=>$isSuccess];
        if (!is_null($data)) {
            if (is_array($data)) {
                $data = array_filter($data, function ($item) {
                    return $item !== null;
                });
            }
            $ret['data'] = $data;
        }
        return response()->json($ret);
    }

    /**
     * @param $page
     * @param  null  $list
     * @return JsonResponse
     */
    protected function successPaginate($page, $list = null)
    {
        return $this->success($this->paginate($page, $list));
    }

    /**
     * @param  LengthAwarePaginator|array  $page
     * @param  null|array  $list
     * @return array
     */
    protected function paginate($page, $list = null)
    {
        if ($page instanceof LengthAwarePaginator) {
            $total = $page->total();
            return [
                'total' => $page->total(),
                'page' => $total == 0 ? 0 : $page->currentPage(),
                'limit' => $page->perPage(),
                'pages' => $total == 0 ? 0 : $page->lastPage(),
                'list' => $list ?? $page->items()
            ];
        }

        if ($page instanceof Collection) {
            $page = $page->toArray();
        }
        if (!is_array($page)) {
            return $page;
        }

        $total = count($page);
        return [
            'total' => $total,
            'page' => $total == 0 ? 0 : 1,
            'limit' => $total,
            'pages' => $total == 0 ? 0 : 1,
            'list' => $page
        ];
    }

    protected function success($data = null)
    {
        return $this->codeReturn(ResponseCode::SUCCESS, $data);
    }

    protected function fail(array $codeResponse = ResponseCode::FAIL, $info = '')
    {
        return $this->codeReturn($codeResponse, null, $info, false);
    }

    /**
     * 401
     * @return JsonResponse
     */
    protected function badArgument()
    {
        return $this->fail(ResponseCode::PARAM_ILLEGAL);
    }

    /**
     * 402
     * @return JsonResponse
     */
    protected function badArgumentValue()
    {
        return $this->fail(ResponseCode::PARAM_VALUE_ILLEGAL);
    }


    protected function failOrSuccess(
        $isSuccess,
        array $codeResponse = ResponseCode::FAIL,
        $data = null,
        $info = ''
    ) {
        if ($isSuccess) {
            return $this->success($data);
        }
        return $this->fail($codeResponse, $info);
    }

}
