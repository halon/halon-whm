<?php

class HalonDriver{
    static private $_instances = array();    
    private function __construct() {;}
    private function __clone() {;}

    /**
     * Singleton Instanace
     * 
     * @param string $endPoint
     * @param string $username
     * @param string $password
     * @return DedykToolAPI
     */
    
    public static function __callStatic($name,$arguments)
    {
        if($arguments)
        {
            self::$_instances[$name] = new $arguments[0]($arguments[1]);
        }
        
        return self::$_instances[$name];
    }
    
}