<?php namespace AwkwardIdeas\HexorxCountriesBundler\Models;

use \AwkwardIdeas\HexorxCountriesBundler\Models\Geo;
use \AwkwardIdeas\HexorxCountriesBundler\Models\Subdivision;

class Country extends BaseModel {

    protected $fillable = ["continent", "abbreviation","alpha2","alpha3","country_code","international_prefix","ioc","gec","name","national_destination_code_lengths","national_number_lengths","national_prefix","number","region","subregion","world_region","un_locode","nationality","postal_code","unofficial_names","languages_official","languages_spoken","geo","currency_code","start_of_week","subdivisions"];

    protected $toArrayExclude = ["unofficial_names", "ioc","gec","alpha2","alpha3"];

    public function setGeoAttribute($value){
        if(is_array($value)){
            $value = new Geo($value);
        }
        $this->attributes["geo"] = $value;
        return true;
    }

    public function setSubdivisionsAttribute($value){
        if(is_array($value)){
            $subdivisionSet = [];
            if(is_array($value[array_keys($value)[0]])){
                foreach($value as $subdivision){
                    $subdivisionSet[] = new Subdivision($subdivision);
                }
            }else{
                $subdivisionSet[] = new Subdivision($value);
            }
            $value = $subdivisionSet;
        }
        $this->attributes["subdivisions"] = $value;
        return true;
    }
}