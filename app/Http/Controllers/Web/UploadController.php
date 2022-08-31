<?php

namespace App\Http\Controllers\Web;

use App\Constants\ResponseCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\UploadRequest;
use App\Models\Collections\FakeImages;
use App\Services\UploadService;
use Illuminate\Http\JsonResponse;
class UploadController extends Controller
{
    private UploadService $uploadService;

    public function __construct(UploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    public function store(UploadRequest $request): JsonResponse
    {
        $result =  $this->uploadService->handleUploadedFile($request->file, true);

        if($result['state'] === 'no') {
            return $this->fail(ResponseCode::UPLOAD_COMMON_ERROR, $result['msg']);
        }
        //插入到mongodb
        $imageModel = new FakeImages();
        $count = $imageModel->count();
        $imageModel->id = 1;
        if($count>0) {
            $imageModel->id = $count+1;
        }
        $imageModel->image_url = $result['post_img_url'];
        $imageModel->save();
        unset($result['state']);
        return $this->success($result);
    }
}
