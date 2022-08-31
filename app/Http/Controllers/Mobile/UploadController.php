<?php

namespace App\Http\Controllers\Mobile;

use App\Constants\ResponseCode;
use App\Http\Requests\Mobile\UploadRequest;
use App\Services\UploadService;
use Illuminate\Http\JsonResponse;
class UploadController extends AbstractApiController
{
    private UploadService $uploadService;

    public function __construct(UploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    public function store(UploadRequest $request): JsonResponse
    {
        $result =  $this->uploadService->handleUploadedFile($request->file);
        if($result['state'] === 'no') {
            return $this->fail(ResponseCode::UPLOAD_COMMON_ERROR, $result['msg']);
        }
        unset($result['state']);
        return $this->success($result);
    }
}
