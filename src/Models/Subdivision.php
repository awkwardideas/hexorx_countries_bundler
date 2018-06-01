<?php namespace AwkwardIdeas\HexorxCountriesBundler\Models;

use \AwkwardIdeas\HexorxCountriesBundler\Models\Geo;

class Subdivision extends \AwkwardIdeas\HexorxCountriesBundler\Models\BaseModel
{
    protected $fillable = ["abbreviation", "unofficial_names","translations","name","geo","comments"];

    //protected $toArrayExclude = ["unofficial_names","translations","comments"];
    protected $toArrayExclude = ["unofficial_names","translations","geo","comments"];

    public function setGeoAttribute($value){
        if(is_array($value)){
            $value = new Geo($value);
        }
        $this->attributes["geo"] = $value;
        return true;
    }

}
