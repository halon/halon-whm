<?php
class FormHiddenField extends FormAbstractField{
    function __construct($parent,$options) {
        parent::__construct($parent,$options);
        $this->type = 'Hidden';
    }
}
