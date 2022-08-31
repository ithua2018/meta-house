<?php
namespace  App\Repositories;
class SubwayStationRepository extends  AbstractRepository
{

    public function getOpenLines($city_id)
    {
       return $this->model->where(['is_finished' => 1, 'city_id'=>$city_id])
                          ->where('line','!=', '')->get();
    }


}
