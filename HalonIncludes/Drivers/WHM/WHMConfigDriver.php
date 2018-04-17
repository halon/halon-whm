<?php

class WHMConfigDriver extends AbstractConfigDriver{
    private $configFile = '/usr/local/cpanel/etc/Halon.ini';
    
    function __construct($params) {
        
        if(file_exists($this->configFile))
        {
            $this->values = parse_ini_file($this->configFile);
        }
        else
        {
            throw new SystemException('Config file not exists',404,'configNotExists');
        }
    }
}