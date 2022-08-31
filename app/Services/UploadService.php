<?php

namespace App\Services;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use function Functional\memoize;

class UploadService  extends  BaseService
{
    /**
     * 上传图片到临时目录
     * @param UploadedFile $file
     * @param $request
     * @return array|string[]
     */

    public function handleUploadedFile(UploadedFile $file,  $is_fake=false):array
    {
        //多一层验证，防止直接调用此方法，对文件直接放行
        $extension = $file->extension();
       if (!in_array($extension, array('gif', 'jpg', 'jpeg', 'bmp', 'png'))) {
                return array(
                    "state" => 'no',
                    'msg' => '图片格式有误'
                );
            }

        // 判断图片有效性
        if (!$file->isValid()) {
            return array(
                "state" => 'no',
                'msg' => '上传文件无效'
            );
        }
            //生成文件名
            $extension = $file->getClientOriginalExtension();
            if ($extension == "") {//前端批量上传组件在拖动改变图片排序后, 扩展名会为空, 这里修补一下
                $extension = $file->extension();
                if ($extension == 'jpeg') $extension = 'jpg';
            }
            $randFileName = $this->getUniqueHash();
            $fileName = $randFileName . '.' . $extension;
            $pathName = $is_fake ? 'image/fake/' . $fileName : 'temporary/' . $fileName;

            // 获取图片在临时文件中的地址
            $files = file_get_contents($file->getRealPath());
            $disk = Storage::disk('public');
            $disk->put($pathName, $files);
            // 根据前端传递值动态生成多规格图片
//            if ($request->has('specification')) {
//                $specificationArr = explode(',', $request->specification);
//                if (count($specificationArr) < 1) {
//                    return array(
//                        "state" => 'no',
//                        'msg' => 'specification格式有误'
//                    );
//                }
//                $realBasePath = public_path() . '/storage/';
//                $imgSmall = \Image::make($realBasePath . $pathName);
//                $imageSpecification = config('image.specification');
//                rsort($specificationArr);   //将前端输入的规格按大到小排序，不然将导致先生成小图片后再生成大图模糊的问题
//                foreach ($specificationArr as $specification) {
//                    if (in_array($specification, $imageSpecification)) {
//                        $imgSmall->widen($specification);
//                        $imgSmall->save($realBasePath . 'temporary/' . $randFileName . "_$specification." . $extension);
//                    }
//                }
//            }
            $url =  config('rent.image_url') . '/storage/' . $pathName;
            return array(
                "state" => "SUCCESS",        //上传状态，上传成功时必须返回"SUCCESS"
                "show_img_url" => $url,            //返回的地址
                "post_img_url" => 'storage/' . $pathName,       //新文件名
                "original" => $file->getClientOriginalName(),       //原始文件名
                "type" => $file->getClientMimeType(),            //文件类型
                "size" => $file->getSize()           //文件大小
            );
    }



    private function getUniqueHash(): string
    {
        return substr(sha1(uniqid()), 0, 12);
    }
}
