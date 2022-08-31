<?php
namespace  App\Services\Collections;
use App\Models\Collections\House;
use App\Models\MongDB\Store;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HousesCollectionService extends BaseService
{
    private $collection;
    private $resourceModel;

    public function __construct()
    {
        $connection          = DB::connection('mongodb');
        $this->collection    = $connection->collection('houses_collection');
        $this->resourceModel = new House();
    }

    /**
     * 添加
     * @param array $array
     */
    public function add(array $array):void
    {
        $this->handleLocation($array);
        $result =  $this->collection->insert($array);
        if($result) {
            Log::info($result);
        }
    }

    public function modify($id, $fields)
    {
        $res = $this->getByUuid($id);
        if(!$res){
            return false;
        }

        $update = $this->collection->where('id', $id)->update($fields);
        if($update) {
            //todo
        }
    }

    public function remove($id)
    {
        $res = $this->getByUuid($id);
        if(!$res){
            return false;
        }
        $this->collection->where('id', $id)->delete();
    }

    public function getByUuid($id)
    {
        return $this->collection->where('id', $id)->first();
    }

    //处理坐标
    private function handleLocation(&$arr) {
                $arr['location'] = [
                    (float)$arr['lon'] ,
                    (float)$arr['lat']
        ];
    }

    public function nearby()
    {
        $list =   $this->collection->where('location', 'near', [
            '$geometry' => [
                'type' => 'Point',
                'coordinates' => [
                    -0.1367563, // longitude
                    51.5100913, // latitude
                ],
            ],
            '$maxDistance' => 50,
        ])->get();

        return $list;
    }

    public function search($conditions=[],  $skip = 0, $limit = 200)
    {
        $result = [
            'count' => 0,
            'list' => []
        ];
        $tcollection = $this->collection;
        $resourceTypeFieldService = new ResourceTypeFieldService();
        if($conditions && is_array($conditions)) {
            foreach ($conditions as &$condition) {
                if(!isset($condition['field']) || !isset($condition['operator']) || !isset($condition['value'] )){
                    throw new \Exception('无效的查询表达式', 20025);
                }
                if(is_int($condition['field'])){
                    $fieldId = $condition['field'];
                    $field = $resourceTypeFieldService->get($fieldId);
                    if (!$field) {
                        throw new \Exception('无效的查询字段', 20026);
                    }
                    $condition['field'] = $field['field'];
                }
                $field = $condition['field'];
                $operator = $condition['operator'];
                $value = $condition['value'];
                switch($condition['operator']){
                    case '>':
                    case '=':
                    case '<':
                        $tcollection = $tcollection->where($field, $operator, $value);
                        break;
                    case 'exists':
                        $tcollection = $tcollection->where($field, $operator, true);
                        break;
                    case 'all':
                        if(!is_array($value)){
                            throw new \Exception('all操作符只支持数组对象', 20027);
                        }
                        $tcollection = $tcollection->where($field, $operator, $value);
                        break;
                    case 'size':
                        if(!is_int($value)){
                            throw new \Exception('size操作符只支持整数', 20028);
                        }
                        $tcollection = $tcollection->where($field, $operator, $value);
                        break;
                    case 'regex':
                        $value = new Regex($value, '');
                        $tcollection = $tcollection->where($field, $operator, $value);
                        break;
                    case 'type':
                        if(!is_int($value)){
                            throw new \Exception('type操作符只支持整数', 20029);
                        }
                        $tcollection = $tcollection->where($field, $operator, $value);
                        break;
                    case 'mod':
                        if(!is_array($value)){
                            throw new \Exception('mod操作符只支持数组对象', 20031);
                        }
                        $tcollection = $tcollection->where($field, $operator, $value);
                        break;
                    case 'null':
                        $tcollection = $tcollection->whereNull($field);
                        break;
                    case 'in':
                        if(!is_array($value)){
                            throw new \Exception('in操作符只支持数组对象', 20032);
                        }
                        $tcollection = $tcollection->whereIn($field, $value);
                        break;
                    case 'between':
                        if(!is_array($value) || sizeof($value) != 2){
                            throw new \Exception('in操作符只支持数组对象，且数组元素必须2个', 20033);
                        }
                        $tcollection = $tcollection->whereBetween($field, $value);
                        break;
                    default:
                        throw new \Exception('无效的条件操作符：' . $condition['operator'], 20030);
                }

            }
        }
        $count = $tcollection->count();
        if ($count == 0) {
            return $result;
        }
        $list = $tcollection->skip($skip)->take($limit)->get();

        $result['count'] = $count;
        $result['list'] = $list;

        return $result;
    }

}
