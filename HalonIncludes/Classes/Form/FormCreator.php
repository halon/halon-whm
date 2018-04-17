<?php

class FormCreator extends AbstractControler{
    public $fields = array();
    public $htmlFields = array();
    public $htmlHiddenFields = array();
    public $name;
    public $url = null;
    public $enctype = null;
            
    function __construct($name,$parent,$options = array()) {
        parent::__construct($parent,$options);
        $this->name = $name;
   
        if(!empty($this->templatesDir))
        {
            $this->templatesDir .= DS.'Form'.DS;
        }
    }
    
    function addField($field,array $data){
        
        if(is_object($field))
        {
            if(get_parent_class($field) !== 'FormAbstractField')
            {
                throw new SystemException('Unable to use this object as form field');
            }
            
            $this->fields[] = $field;
        }
        elseif(is_string($field) && is_array($data))
        {
            $className = 'Form'.$field.'Field';
            
            if(!class_exists($className))
            {
                throw new SystemException('Unable to crate form field type:'.$className);
            }

            $this->fields[] = new $className($this,$data);
        }
        else
        {
            throw new SystemException('Unable create form field object');
        }
    }
    
    function getHTML($container = 'Container',$data = array()){
        MGLang::stagCurrentContext('generateForm'); 
        MGLang::addToContext($this->name);
    
        $this->htmlFields = array();
        $sum = null;
        foreach($this->fields as $field)
        {
            if($field->sumWithNext)
            {
                if($sum)
                {
                    $sum .= $field->generate(array('disableFinishContainer'=>true,'skipLabel'=>true,'disableStartContainer'=>true));
                }
                else
                {
                    $sum = $field->generate(array('disableFinishContainer'=>true));
                }
            }
            else
            {
                if($sum)
                {
                    $this->htmlFields[] = $sum.$field->generate(array('skipLabel'=>true,'disableStartContainer'=>true));
                }
                else
                {
                    if($field->type == 'Hidden')
                    {
                        $this->htmlHiddenFields[] = $field->generate();
                    }
                    else
                    {
                        $this->htmlFields[] = $field->generate();
                    }
                }
            }
        }
        
        $html = parent::getHTML($container, array_merge((array)$this, $data));
        
        MGLang::unstagContext('generateForm');
        
        return $html;
    }
    
}