<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\SubwayStation
 *
 * @method static \Illuminate\Database\Eloquent\Builder|SubwayStation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SubwayStation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SubwayStation query()
 * @mixin \Eloquent
 * @property int $id ID
 * @property int $station_id 站点ID
 * @property string $pname 省份
 * @property string $cityname 城市
 * @property string $adname 区
 * @property int $city_id 市id
 * @property string $name 站名全称
 * @property float $lon 经度
 * @property float $lat 维度
 * @property string $line 几号线
 * @property int $is_finished 是否完成 0在建 1完成
 * @method static \Illuminate\Database\Eloquent\Builder|SubwayStation whereAdname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubwayStation whereCityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubwayStation whereCityname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubwayStation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubwayStation whereIsFinished($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubwayStation whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubwayStation whereLine($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubwayStation whereLon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubwayStation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubwayStation wherePname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubwayStation whereStationId($value)
 */
class SubwayStation extends Model
{
    const UPDATED_AT = null;
    const CREATED_AT = null;
    protected $keyType = 'string';
    public $incrementing = false;
}
