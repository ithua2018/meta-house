<?php

namespace App\Models;

use Carbon\Carbon;
use Closure;
use DateTimeInterface;
use Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Throwable;

/**
 * Class BaseModel
 *
 * @package App\Models
 * @method static Builder|BaseModel newModelQuery()
 * @method static Builder|BaseModel newQuery()
 * @method static Builder|BaseModel query()
 * @mixin Eloquent
 */
class BaseModel extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $hidden = [
        'deleted_at',
    ];
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * @return static
     */
    public static function new()
    {
        return new static();
    }

//    public function getTable()
//    {
//        return $this->table ?? Str::snake(class_basename($this));
//    }

//    public function toArray()
//    {
//        $items = parent::toArray();
//        $items = array_filter($items, function ($item) {
//            return !is_null($item);
//        });
//        $keys = array_keys($items);
//        $keys = array_map(function ($key) {
//            return lcfirst(Str::studly($key));
//        }, $keys);
//        $values = array_values($items);
//        return array_combine($keys, $values);
//    }

    public function serializeDate(DateTimeInterface $date)
    {
        return Carbon::instance($date)->toDateTimeString();
    }


    /**
     * 乐观锁更新 compare and save
     * @return int
     * @throws Throwable
     */
    public function cas()
    {
        throw_if(!$this->exists, Exception::class, 'model not exists when cas!');

        $dirty = $this->getDirty();

        if (empty($dirty)) {
            return 0;
        }

        if ($this->usesTimestamps()) {
            $this->updateTimestamps();
            $dirty = $this->getDirty();
        }

        $diff = array_diff(array_keys($dirty), array_keys($this->original));
        throw_if(!empty($diff), Exception::class, 'key ['.implode(',', $diff).'] not exists when cas!');

        if ($this->fireModelEvent('casing') === false) {
            return 0;
        }

        $query = $this->newModelQuery()->where($this->getKeyName(), $this->getKey());
        foreach ($dirty as $key => $value) {
            $query = $query->where($key, $this->getOriginal($key));
        }

        $row = $query->update($dirty);
        if ($row > 0) {
            $this->syncChanges();
            $this->fireModelEvent('cased', false);
            $this->syncOriginal();
        }
        return $row;
    }

    /**
     * Register a casing model event with the dispatcher.
     *
     * @param  Closure|string  $callback
     * @return void
     */
    public static function casing($callback)
    {
        static::registerModelEvent('casing', $callback);
    }

    /**
     * Register a cased model event with the dispatcher.
     *
     * @param  Closure|string  $callback
     * @return void
     */
    public static function cased($callback)
    {
        static::registerModelEvent('cased', $callback);
    }
}
