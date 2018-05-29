<?php namespace AwkwardIdeas\HexorxCountriesBundler\Models;

abstract class BaseModel
{
    protected $fillable = [];

    protected $attributes = [];

    protected $toArrayExclude = [];

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    public function fill(array $attributes)
    {
        if( $attributes ){
            foreach( $attributes as $property => $value ){
                $this->__set( $property, $value );
            }
        }

        return $this;
    }

    public function toSimpleArray(){
        $dataSet = [];

        foreach($this->attributes as $key=>$value){
            if(!in_array($key, $this->toArrayExclude)){
                if(is_array($value)){
                    if(is_object($value[array_keys($value)[0]])){
                        $valueArray = [];
                        foreach($value as $object){
                            $valueArray[] = $object->toSimpleArray();
                        }
                        $value = $valueArray;
                    }
                }
                if(is_object($value)){
                    $value = $value->toSimpleArray();
                }
                $dataSet[$key] = $value;
            }
        }

        return $dataSet;
    }

    public function ObjectToArray(){
        return get_object_vars( $this );
    }

    public function GetAttribute( $property ) {

        $methodName = 'get'. ucwords( $property )  . "Attribute";

        if( method_exists( $this, $methodName ) ){
            return $this->$methodName();
        }else if( property_exists( $this, $property ) ) {
            return $this->$property;
        }else if($this->fillable === [] || in_array($property, $this->fillable)){
            return array_key_exists($property, $this->attributes) ? $this->attributes[$property]: null;
        }
        return null;
    }

    public function SetAttribute( $property, $value ) {

        $methodName = 'set'. ucwords( $property ) . "Attribute";

        if( method_exists( $this, $methodName ) ) {
            return $this->$methodName($value);
        }else if( property_exists( $this, $property ) ) {
            $this->$property = $value;
            return true;
        }else if($this->fillable === [] || in_array($property, $this->fillable)){
            $this->attributes[$property] = $value;
        }else{
            return false;
        }
    }

    /**
     * Create a new Eloquent Collection instance.
     *
     * @param  array  $models
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function newCollection(array $models = [])
    {
        return new Collection($models);
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge($this->attributesToArray(), $this->relationsToArray());
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Determine if an attribute or relation exists on the model.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        return ! is_null($this->getAttribute($key));
    }

    /**
     * Unset an attribute on the model.
     *
     * @param  string  $key
     * @return void
     */
    public function __unset($key)
    {
        unset($this->attributes[$key], $this->relations[$key]);
    }


    /**
     * Handle dynamic static method calls into the method.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return (new static)->$method(...$parameters);
    }
}
