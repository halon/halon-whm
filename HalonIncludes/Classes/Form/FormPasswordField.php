<?php

class FormPasswordField extends FormAbstractField{
    function __construct($parent,$options) {
        parent::__construct($parent,$options);
        $this->type = 'Password';
    }
    
    function generate($options = array()) {
        $num = strlen($this->value);
        $this->value = '';

        for($i=0;$i<=$num;$i++)
        {
            $this->value .= '*';
        }
        
        return parent::generate($options);
    }
}