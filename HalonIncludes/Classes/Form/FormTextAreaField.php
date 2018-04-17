<?php

class FormTextAreaField extends FormAbstractField{
    function __construct($parent,$options) {
        parent::__construct($parent,$options);
        $this->type = 'TextArea';
    }
}