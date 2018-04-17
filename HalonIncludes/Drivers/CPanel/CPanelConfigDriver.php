<?php

class CPanelConfigDriver extends AbstractConfigDriver{        
    function __construct($params) {

        if(!empty($params->dbConfiguration))
        {
            $this->values = parse_ini_string($params->dbConfiguration);
        }
        else
        {
            throw new SystemException('Config not exists',404,'configNotExists');
        }
    }
}