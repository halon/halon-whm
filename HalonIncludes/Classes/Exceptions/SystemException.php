<?php

class SystemException extends Exception{
    private $userMessage;
    private $details;
    private $token;

    function __construct($message, $code = 0, $userMessage = '', $details = '',$previous = null) {       
        parent::__construct($message, $code, $previous);
        $this->userMessage = $userMessage;
        $this->details = $details;
        $this->token = time().md5(rand(0,1000));
        }
    
    function getUserMessage(){
        return $this->userMessage;
    }
    
    function getDetails(){
        return $this->details;
    }
    
    function getToken(){
        return $this->token;
    }
}

