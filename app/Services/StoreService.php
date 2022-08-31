<?php
namespace  App\Services;
use App\Models\MongDB\Store;
use Illuminate\Support\Facades\DB;

class StoreService extends BaseService
{
    private $collection;
    private $resourceModel;

    public function __construct()
    {
        $connection          = DB::connection('mongodb');
        $this->collection    = $connection->collection('store_collection');
        $this->resourceModel = new Store();
    }

    public function add($array)
    {

        $this->collection->insert($array);

      //  return $this->collection->where('rms_uuid', $array['rms_uuid'])->get();
    }

    public function modify($uuid, $fields)
    {
        $res = $this->getByUuid($uuid);
        if(!$res){
            throw new \Exception('资源不存在', 20027);
        }
        foreach ($fields as $key=>$value) {
            $res->$key = $value;
        }
        $res->modifiedtime = time();
        $res->save();
        return $res;
    }

    public function remove($uuid)
    {
        $res = $this->getByUuid($uuid);
        if(!$res){
            throw new \Exception('资源不存在', 20027);
        }
        $res->delete();
    }

    public function getByUuid($uuid)
    {
        return ResourceModel::where('rms_uuid', $uuid)->first();
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
