<?php


namespace App\Models\Collections;




use Matchory\Elasticsearch\Model;

class Areas extends Model
{
    protected $connection = "default";
    protected $index = "areas";

}
