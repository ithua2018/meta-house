<?php

namespace App\Http\Controllers\Mobile\Search;
use App\Constants\ResponseCode;
use App\Http\Controllers\Mobile\AbstractApiController;
use App\Services\Collections\AreaCollectionService;
use Illuminate\Http\Request;

class AreaSearchController extends AbstractApiController
{
   private AreaCollectionService $areaCollectionService;
    public function __construct(
        AreaCollectionService $areaCollectionService
    )
    {
        $this->areaCollectionService = $areaCollectionService;

    }

    /**
     * 中文 拼音 首字母 搜索
     * @param  Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
   public function index(Request $request)
   {
       if (!$request->get('q')) {
           return $this->fail(ResponseCode::PARAM_ILLEGAL,'请输入搜索关键字');
       }
       $result =  $this->areaCollectionService->topSearch($request->get('q'));
       return $this->success($result);
   }


}
