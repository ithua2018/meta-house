<?php
namespace  App\Repositories;
use Illuminate\Support\Collection;

class AreaRepository extends  AbstractRepository
{

    public function getAreaLevel1():Collection
    {
        return $this->model->where(['deep'=>1])->get();
    }

    public function getAreaLevel2ByPid($id):Collection
    {
        return $this->model->where(['pid' => $id, 'deep'=>2])->get();
    }

    public function getAreaLevel3()
    {
        return $this->model->where(['deep'=>3])->get()->groupBy('pid');
    }






}
