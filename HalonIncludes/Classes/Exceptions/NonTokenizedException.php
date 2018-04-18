<?php

class NonTokenizedException extends Exception{
    private $userMessage;
    private $details;

    function __construct($message, $code = 0, $userMessage = '', $details = '',$previous = null) {
        parent::__construct($message, $code, $previous);
        $this->userMessage = $userMessage;
        $this->details = $details;
        }
    
    function getUserMessage(){
        return $this->userMessage;
    }
    
    function getDetails(){
        return $this->details;
    }
}
