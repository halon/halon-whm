<?php

class MGExceptionLogger{
    static function addLog($ex)
    {   
        $token = null;
        $code  = 0;
        
        if(is_a($ex, '\Exception') || is_subclass_of($ex, '\Exception'))
        {
            $message  = $ex->getMessage();
            $code     = $ex->getCode();
            $type     = get_class($ex);
            
            if(method_exists($ex, 'getToken'))
            {
                $token = $ex->getToken();
            }
        }
        elseif(is_string($ex))
        {
            $message  = $ex;
            $type     = 'string';
        }
        else
        {
            $message  = print_r($ex,true);
            $type     = 'other';
        }
        
        try{
            if(!empty($token)){
                MGMySQL::insert('error_logs', array(
                   'token'      => $token
                   ,'date'      => date('Y-m-d h:i:s')
                   ,'message'   => $message
                   ,'debug'     => print_r($ex,true)
                   ,'code'      => $code
                   ,'type'      => $type
                ));
            }
        } catch (\Exception $exDB) {
            openlog("CPanel", LOG_PID | LOG_PERROR, LOG_LOCAL0);
            syslog(LOG_ERR, $ex->getMessage().(($token)?" Token:".$token:''));
        }
    }
    
    static function getErrorByToken($token)
    {
        $result = MGMySQL::select(array(
           'token'    
           ,'date'      
           ,'message'   
           ,'debug'     
           ,'code'     
           ,'type'      
        )
        ,'error_logs'
        ,array(
            'token' => $token
        ));
        
        if($result)
        {
            return $result->fetch();
        }
    }
    
    static function listErrorTokens($sortby = array()){
        $result = MGMySQL::select(array(
           'token'    
            ,'message'
            ,'date'
        )
        ,'error_logs', array(), $sortby);
        
        $data = array();
        
        while($row = $result->fetch())
        {
            $data[] = $row;
        }
        
        return $data;
    }
}
