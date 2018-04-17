<?php

/*class HalonConfiguration{
    private $values = array();

    function __construct($params) {
        $result = MGMySQL::query("
            SELECT 
                name
                ,value
            FROM
                configuration
        ");
                
        while($row = $result->fetch())
        {
            $this->values[$row['name']] = $row['value'];
        }
    }


    
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
    
    public function __set($name, $value) {
        $this->values[$name] = $value;
        
        MGMySQL::query("
            INSERT INTO 
                    configuration 
                    (name, value)
            VALUES 
                    (:name, :value)
            ON DUPLICATE KEY UPDATE 
                    value = :value
        ", array(
            ':name'     => $name
            ,':value'   => $value
        ));
    }
}*/