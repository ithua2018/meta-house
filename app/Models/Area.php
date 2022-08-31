<?php

namespace App\Models;
use Eloquent;
use Illuminate\Database\Eloquent\Model;



/**
 * App\Models\Area
 *
 * @property int $id
 * @property int|null $pid
 * @property int|null $deep
 * @property string|null $name
 * @property string|null $pinyin_prefix
 * @property string|null $pinyin
 * @property string|null $ext_id
 * @property string|null $ext_name
 * @method static \Illuminate\Database\Eloquent\Builder|Area newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Area newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Area query()
 * @method static \Illuminate\Database\Eloquent\Builder|Area whereDeep($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Area whereExtId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Area whereExtName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Area whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Area whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Area wherePid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Area wherePinyin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Area wherePinyinPrefix($value)
 * @mixin Eloquent
 */
class Area extends Model
{

}
