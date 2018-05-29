<?php namespace AwkwardIdeas\HexorxCountriesBundler\Models;

class Geo extends \AwkwardIdeas\HexorxCountriesBundler\Models\BaseModel
{

    protected $fillable = ["latitude", "latitude_dec","longitude","longitude_dec","max_latitude","max_longitude","min_latitude","min_longitude","bounds"];

    public function setBoundsAttribute($value){
        if(is_array($value)){
            $value = new Bounds($value);
        }
        $this->attributes["bounds"] = $value;
        return true;
    }
}

class Bounds extends \AwkwardIdeas\HexorxCountriesBundler\Models\BaseModel{
    protected $fillable = ["northeast","southwest"];
    public function setNortheastAttribute($value){
        if(is_array($value)){
            $value = new LatLong($value);
        }
        $this->attributes["northeast"] = $value;
        return true;
    }
    public function setSouthwestAttribute($value){
        if(is_array($value)){
            $value = new LatLong($value);
        }
        $this->attributes["southwest"] = $value;
        return true;
    }
}

class LatLong extends \AwkwardIdeas\HexorxCountriesBundler\Models\BaseModel{
    protected $fillable = ["lat","lng"];
}