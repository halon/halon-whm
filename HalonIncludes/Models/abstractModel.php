<?php

/**
 * Description of abstractModel
 *
 * @author Michal Czech <michael@modulesgarden.com>
 */
abstract class abstractModel {
    static $tableDeclaration = null;
    static $fieldDeclaration = array();
    
    /**
     * Fill Current Model Properties 
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     * @param array $data
     */
    function fillProperties($data, $required = 'public',array $skipChecking = array()){
        
        if(empty($data))
        {
            return;
        }
        
        $used = array('id'=>true);
        
        if(is_array($data))
        {
            foreach($data as $property => $value)
            {
                if(property_exists($this, $property))
                {
                    $this->$property = $value;
                    $used[$property] = true;
                }
            }
        }
        
        if($required == 'public' || $required == 'all')
        {            
            foreach(get_object_vars($this) as $property => $value)
            {
                if(!isset($used[$property]) && !in_array($property, $skipChecking))
                {
                    throw new SystemException('Missing object property '.$property);
                }
            }
        }
    }
    
    /**
     * Normalized Time Stamp
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     * @param string $strTime
     * @return string
     */
    static function timeStamp($strTime = 'now')
    {
        return date('Y-m-d H:i:s',  strtotime($strTime));
    }
    
    /**
     * Disable Get Function
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     * @param string $property
     * @throws Main\MGLibs\exceptions\system
     */
    function __get($property) {
        throw new SystemException('Property: '.$property.' does not exits');
    }
    
    /**
     * Disable Set Function
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     * @param string $property
     * @param string $value
     * @throws Main\MGLibs\exceptions\system
     */
    function __set($property, $value) {
        throw new SystemException('Property: '.$property.' does not exits');
    }
    
    /**
     * Cast To array
     * 
     * @param string $container
     * @return array
     */
    function toArray($container = true){
        $className = get_called_class();
        
        $fields = isset(static::$fieldDeclaration)?static::$fieldDeclaration:get_class_vars($className);

        foreach(explode('\\', $className) as $className);
        
        $data = array();
        
        foreach($fields as $name)
        {
            if(isset($this->{$name}))
            {
                $data[$name] = $this->{$name};
            }
        }

        if($container === true)
        {
            return array(
                $className => $data
            );
        }
        elseif($container)
        {
            return array(
                $container => $data
            );
        }
        else
        {
            return $data;
        }
    }
}
