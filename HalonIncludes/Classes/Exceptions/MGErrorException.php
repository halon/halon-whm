<?php

class MGErrorException extends Exception{
    private $token;
    private $details;
    
    function __construct($message, $code, $severity, $filename, $lineno, $previous = null) {
        $this->details = array(
            $severity, $filename, $lineno
        );
        parent::__construct($message, $code, $previous);
        $this->token = time().md5(rand(0,1000));
    }
    
    function getToken(){
        return $this->token;
    }
}