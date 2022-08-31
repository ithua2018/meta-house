<?php
namespace App\Models\Collections;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Jenssegers\Mongodb\Eloquent\Model;

class House extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'houses_collection';


}
