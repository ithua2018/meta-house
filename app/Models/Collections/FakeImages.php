<?php
namespace App\Models\Collections;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Jenssegers\Mongodb\Eloquent\Model;

class FakeImages extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'fake_images_collection';


}
