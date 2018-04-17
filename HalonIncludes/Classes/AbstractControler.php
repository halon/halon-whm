<?php

abstract class AbstractControler{
    protected $templatesDir;
    protected $type; 
    protected $dir;
    protected $page;
    public $baseUrlConfig = array(
        'html'  => 'index.php'
        ,'ajax' => 'ajax.php'
    );

    public function __construct($parent, $options = array()){ 
        foreach($parent as $key => $value)
        {
            if(property_exists($this,$key))
            {
                $this->{$key} = $value;
            }
        }
        
        foreach($options as $key => $value)
        { 
            if(property_exists($this,$key))
            {
                $this->{$key} = $value;
            }
        }
        
        if(!empty($this->dir) && empty($this->templatesDir))
        {
            $this->templatesDir = $this->dir.DS.'templates';
        }
    }
    
    protected function getHTML($file,$data = array()){ 
        $templateFile = $this->templatesDir.DS.$file.'.php';
     
        if(!file_exists($templateFile))
        {
            throw new SystemException("Unable to find html File:".$templateFile);
        }
        
        try{
            ob_start();
            extract((array)$data);
            include($templateFile);
            $html = ob_get_clean(); 
        } catch (Exception $ex) {
            ob_clean();
            throw $ex;
        }
        
        return $html;
    }
    
    protected function getJSON($data = array()){
        return '<!--JSONRESPONSE#'.json_encode($data).'#ENDJSONRESPONSE -->';
    }
    
    protected function getPureJSON($data = array()) {
        return json_encode($data);
    }
    
    protected function getUrl($page,$action = null)
    {
        return $this->baseUrlConfig['html'].'?page='.$page.(($action)?'&action='.$action:'');
    }
    
    protected function getCurrentAction($action = null){
        return $this->getUrl($this->page,$action);
    }
    
    protected function getAJAX($page,$action = null)
    {
        return $this->baseUrlConfig['ajax'].'?page='.$page.(($action)?'&action='.$action:'');
    }
            
    protected function getCurrentAJAX($action = null){
        return $this->getAJAX($this->page,$action);
    }
    
}