<?php

abstract class AbstractLocalAPIDriver{
    
    function __construct($params) {
        foreach($params as $key => $value)
        {
            if(property_exists($this,$key))
            {
                $this->{$key} = $value;
            }
        }
        $this->_connect($params);
    }


    public $connection;
    public $cpanel;
    
    /**
     *
     * @var type abstractConfig
     */
    public $config;

    abstract public function _connect($params);
    abstract public function getAccounts();
    abstract public function getUserDomains($user = null,$main = null);
    
    /*
    abstract public function getAllDomains($limit = 10, $offset = 10, $filters = array(), $sortCol = 'domain', $sortVect = 'ASC');
    abstract public function getDomainsCount($filters = array(), $sortCol = 'domain', $sortVect = 'ASC');
    abstract public function deleteZonesFromDomain($domain,array $zonesList);
    abstract public function addZonesToDomain($domain,array $zonesList);
    */
}