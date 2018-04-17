<?php
class MGMySQL{
    private $connection = array();
    private $params;
    static private $usePDO = true;
    
    static private $_instance;    
    private function __construct() {;}
    private function __clone() {;}

    /**
     * Singleton Instanace
     * 
     * @param string $endPoint
     * @param string $username
     * @param string $password
     */
    public static function getInstance($hostName = null, $username = null, $password = null, $dbname = null, $connectionName = 'default')
    {
        if(empty(self::$_instance))
        {
            self::$_instance = new self();
        }

        if ($hostName) 
        {
            if(self::$usePDO)
            {
                try{
                    self::$_instance->connection[$connectionName] = new PDO("mysql:host=$hostName;dbname=$dbname", $username, $password); 

                    self::$_instance->connection[$connectionName]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (Exception $ex) {
                    throw new SystemException('SQL Error',0,'general',null,$ex);
                }
           }
            else
            {
                if(self::$_instance->connection[$connectionName] = mysql_connect($hostName, $username, $password, true))
                {
                    mysql_select_db($dbname, self::$_instance->connection[$connectionName]);

                     if($error = mysql_error())
                     {
                         throw new SystemException("SQL Error: ".$error);
                     }
                }
                else
                {
                     throw new SystemException("Cant connect to database");
                }
            }
        }

        return self::$_instance;
    }
    
    static function query($query,$params=array(), $connectionName = 'default')
    {
        if(self::$usePDO)
        {
            try{
                $sth = self::$_instance->connection[$connectionName]->prepare($query);
                $sth->execute($params);
            } catch (Exception $ex) {
                throw new SystemException('SQL Error',0,'general',null,$ex);
            }

            if(strpos($query, 'insert') !== false || strpos($query, 'INSERT')!== false )
            {
                $id = self::$_instance->connection[$connectionName]->lastInsertId(); 
            }
            else
            {
                $id = null;
            }
            
            return new MGMySQLResult($sth,$id);
        }
        else
        {
            foreach ($params as $key => $value) 
            {
                $query = str_replace("$key", "'".mysql_real_escape_string($value)."'", $query);
            }

            if(!empty(self::$_instance->connection[$connectionName]))
            {
                $result = mysql_query($query,self::$_instance->connection[$connectionName]);
            }
            else
            {
                $result = mysql_query($query);
            }

            if($error = mysql_error())
            {
                throw new SystemException("SQL Error: ".$error);
            }

            if(strpos($query, 'insert') !== false || strpos($query, 'INSERT')!== false )
            {
                $id = mysql_insert_id();
            }
            else
            {
                $id = null;
            }

            return new MGMySQLResult($result,$id);
        }
    }
    
    static function dropInstance($connectionName)
    {
        if(self::$usePDO)
        {
            unset(self::$_instance->connection[$connectionName]);
        }
        else
        {
            mysql_close(self::$_instance->connection[$connectionName]);
        }
    }
    
    static function insert($table,array $data, $connectionName = 'default')
    {
        $cols = array();
        $valuesLabels = array();
        $values = array();
        foreach($data as $col => $value)
        {
            $cols[] = $col;
            $colName = ':'.preg_replace("/[^A-Za-z0-9]/", '', $col);
            $valuesLabels[] = $colName;
            
            $values[$colName] = $value;
        }
        
        $cols = implode("`,`", $cols);
        $valuesLabels = implode(",", $valuesLabels);
        
        $sql = "INSERT INTO $table (`$cols`) VALUES ($valuesLabels)";

        
        
        $val = self::query($sql,$values,$connectionName)->getID();
        
        return $val;
    }
    
    static function update($table,array $data,array $condition,array $conditionValues = array())
    {
        $cols = array();
        $valuesLabels = array();
        $values = array();
        foreach($data as $col => $value)
        {
            $colName = ':'.preg_replace("/[^A-Za-z0-9]/", '', $col);
            
            $cols[] = "`$col` = $colName";
                        
            $values[$colName] = $value;
        }
        
        $cols = implode(",", $cols);
        
        $conditionParsed = array();
        
        $i = 0;
        
        foreach($condition as $col => $value)
        {
            if(is_string($col))
            {
                $colName = ':cond'.$i;
                $i++;
                
                $conditionParsed[] = "`$col` = $colName";
                $values[$colName] = $value;
            }
            else
            {
                $conditionParsed[] = $value;
            }
        }
        
        foreach($conditionValues as $a => $b)
        {
            $values[$a] = $b;
        }
        
        $conditionParsed = implode(' AND ', $conditionParsed);
        
        $sql = "UPDATE $table SET $cols WHERE $conditionParsed";
        
        return self::query($sql,$values);
    }
    
    static function delete($table,array $condition,array $conditionValues = array())
    {
        $conditionParsed = array();
        
        $i = 0;
        
        foreach($condition as $col => $value)
        {
            if(is_string($col))
            {
                $colName = ':cond'.$i;
                $i++;
                
                $conditionParsed[] = "`$col` = $colName";
                $values[$colName] = $value;
            }
            else
            {
                $conditionParsed[] = $value;
            }
        }
        
        foreach($conditionValues as $a => $b)
        {
            $values[$a] = $b;
        }
        
        $conditionParsed = implode(' AND ', $conditionParsed);
        
        $sql = "DELETE FROM $table WHERE $conditionParsed";
        
        return self::query($sql,$values);
    }
    
    static function truncateDatabase($hostName, $username, $password, $dbname){
        MGFileManipulator::exec("(mysqldump -h '".$hostName."' -u'".$username."' -p'".$password."' --add-drop-table ".$dbname." | grep ^DROP & echo 'SET foreign_key_checks = 0;' )| mysql -h '".$hostName."' -u'".$username."' -p'".$password."' ".$dbname);
    }
    
    static function select(array $cols,$table,array $condition = array(),array $orderBy = array(),$limit = null,$offset = 0, $connectionName = 'default')
    {
        foreach($cols as $name => &$value)
        {
            if(!is_int($name))
            {
                $value = "`$name` as '$value'";
            }
            else
            {
                $value = "`$value`";
            }
        }
        unset($value);
        
        $cols = implode(",", $cols);
        
        $sql = "SELECT $cols FROM $table";
        
        $conditionParsed = self::parseConditions($condition,$values);
                
        if($conditionParsed)
        {
            $sql .= " WHERE ".$conditionParsed;
        }
        
        if($orderBy)
        {
            $sql .= " ORDER BY ";
            $tmp = array();
            foreach($orderBy as $col => $vect)
            {
                $tmp[] = "$col ".(($vect=='ASC'||$vect=='asc')?'ASC':'DESC');
            }
            $sql .= implode(',', $tmp);
        }
        
        if($limit)
        {
            if($offset)
            {
                $sql .= " LIMIT $offset , $limit ";
            }
            else
            {
                $sql .= " LIMIT 0 , $limit ";
            }
        }

        return self::query($sql,$values);
    }
    
    static function parseConditions($condition,&$values){
        $conditionParsed = array();
        
        $i = 0;
        
        $values = array();
        
        foreach($condition as $col => $value)
        {
            if(is_string($col))
            {
                if(is_array($value))
                {
                    $conditionTmp = array();
                    foreach($value as $v)
                    {
                        $colName = ':cond'.$i;
                        $conditionTmp[] = $colName;
                        $values['cond'.$i] = $v;
                        $i++;
                    }
                    $conditionParsed[] = "`$col` in (".implode(',',$conditionTmp).')';
                }
                else
                {
                    $colName = ':cond'.$i;                
                    $conditionParsed[] = "`$col` = $colName";
                    $values['cond'.$i] = $value;
                    $i++;
                }
            }
            elseif(is_array($value) && isset($value['customQuery']))
            {
                $conditionParsed[] = $value['customQuery'];
                foreach ($value['params'] as $n => $v)
                {
                    $values[$n] = $v;
                }
            }
            else
            {
                $conditionParsed[] = $value;
            }
            
        }

        return implode(' AND ', $conditionParsed);
    }
}
