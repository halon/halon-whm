<?php

abstract class AbstractConfigDriver{    
    protected $values;

    abstract public function __construct($params);
      
    public function __isset($name) {
        if(isset($this->values[$name]))
        {
            return true;
        }
        return false;
    }

    public function __get($name) {
        if(isset($this->values[$name]))
        {
            return $this->values[$name];
        }
        return null;
    }
    
    public function isNotEmpty()
    {
        return !(empty($this->values));
    }
}