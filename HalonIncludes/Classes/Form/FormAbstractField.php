<?php

abstract class FormAbstractField extends AbstractControler{
    public $name;
    public $value;
    public $options;
    public $type;
    public $rows;
    public $cols;
    public $enableDescription = false;
    public $enableLabel = true;
    public $enablePlaceholder = false;
    public $formName = false;
    public $default;
    public $frendlyName = false;
    public $sumWithNext = false;
    public $skipLabel = null;
    public $disableStartContainer = null;
    public $disableFinishContainer = null;
    public $readonly;
    public $error;
    
    function __construct($params,$options = array()) {
        parent::__construct($params);
        $this->formName = $this->name;        
        parent::__construct($options);
        
        if(empty($this->value) && !empty($this->default))
        {
            $this->value = $this->default;
        }
        
        if(empty($this->frendlyName))
        {
            $this->frendlyName = $this->name;
        }
    }
    
    function generate($options = array()){
        parent::__construct($options);
        
        MGLang::stagCurrentContext('generateField');
        MGLang::addToContext($this->frendlyName);

        $html = $this->getHTML($this->type, $this);
        
        MGLang::unstagContext('generateField');
        
        return $html;
    }
    
}