<?php

class MGMySQLResult{
    private $result;
    private $usePDO = false;
    
    function __construct($result,$id = null) {
        
        if(is_a($result,'PDOStatement'))
        {
            $this->usePDO = true;
        }
        
        $this->result = $result;
        $this->id = $id;
    }
    
    function fetch()
    {
        if($this->usePDO)
        {
            return $this->result->fetch(PDO::FETCH_ASSOC);
        }
        else
        {
            return mysql_fetch_assoc($this->result);
        }
    }
    
    function fetchAll()
    {
        if($this->usePDO)
        {
            return $this->result->fetchAll(PDO::FETCH_ASSOC);
        }
        else
        {
            $result = array();
            while($row = $this->fetch())
            {
                $result[] = $row;
            }
            return $result;
        }
    }
        
    function fetchColumn($name = null)
    {
        if($this->usePDO)
        {
            $data = $this->result->fetch(PDO::FETCH_BOTH);
        }
        else
        {
            if($name)
            {
                $data = mysql_fetch_assoc($this->result);
            }
            else
            {
                $data = mysql_fetch_array($this->result);
            }
        }
        
        if($name)
        {
            return $data[$name];
        }
        else
        {
            return $data[0];
        }
    }
    
    function getID()
    {
        return $this->id;
    }
    
}