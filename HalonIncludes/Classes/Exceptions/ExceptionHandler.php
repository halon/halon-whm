<?php

class ExceptionHandler{
    const EXCEPTION_HANDLER = "handleException";
    const ERROR_HANDLER = "handleError";
    const SHUTDOWN_HANDLER = "handleShutdown";
    
    public function register()
    {
        set_error_handler(array($this, self::ERROR_HANDLER));
        set_exception_handler(array($this, self::EXCEPTION_HANDLER));
        register_shutdown_function(array($this, self::SHUTDOWN_HANDLER));
    }
    
    public function handleException(Exception $exception)
    {
        echo "<pre>";
            print_r("handleException");
            print_r($exception);
        echo "</pre>";
        /*
        MGExceptionLogger::addLog($exception);
        
        if(method_exists($exception, 'getToken'))
        {
            die("Error: ".$exception->getToken());
        }*/
    }
    
    public function handleError($level, $message, $file = null, $line = null)
    {
        throw new MGErrorException($message, $level, 0, $file, $line);
    }
    
    public function handleShutdown()
    {
        $error = error_get_last();

        if($error)
        {
            echo "<pre>";
            print_r("handleShutdown");
            print_r($error);
            echo "</pre>";
            
            /*
            $ex = new MGErrorException($error['message'], $error['type'], 0, $error['line'], $error['file']);
            
            MGExceptionLogger::addLog($ex);
            
            $message = null;

            if(method_exists($ex, 'getUserMessage'))
            {
                $message = $ex->getUserMessage();
            }
            
            if(empty($message))
            {
                $message = 'Something Went Wrong, Please Check The Logs';
            }
            
            if(method_exists($ex, 'getToken'))
            {
                $message .= MGLang::absoluteT('errorMessages','errorID').$ex->getToken();
            }
            
            die($message);*/
        }
    }
}