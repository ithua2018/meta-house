<?php
namespace App\Http\Controllers\Mobile;


use App\Constants\ResponseCode;
use App\Repositories\HouseRepository;
use App\Services\EquipmentsService;
use App\Services\HouseService;

class HouseController extends AbstractApiController
{
    private HouseService $houseService;
    private EquipmentsService $equipmentsService;
    private HouseRepository $houseRepository;
    public function __construct(
        HouseService $houseService,
        EquipmentsService $equipmentsService,
        HouseRepository $houseRepository
    )
    {
        $this->houseService = $houseService;
        $this->equipmentsService = $equipmentsService;
        $this->houseRepository = $houseRepository;
    }

    /**
     * 详情
     */
    public function detail($id)
    {
        $model = $this->houseRepository->getFirstWhere(['id'=>$id]);
        if(is_null($model)) {
            return $this->fail(ResponseCode::DATA_IS_NULL);
        }
        return $this->success($model->toArray());

    }

    /**
     * 设备列表  金额范围组
     * @return \Illuminate\Http\JsonResponse
     */
    public function settings()
    {

        $equipments = $this->equipmentsService->getList();
        $price_range_groups = $this->houseService->priceRangeGroup();

        return $this->success(['equipments' => $equipments, 'price_range_groups' => $price_range_groups]);
    }



}
