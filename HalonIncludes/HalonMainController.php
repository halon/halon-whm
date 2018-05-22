<?php

class HalonMainController extends AbstractControler{
    private $allowRawExeptionMessages = false;
    public $dbConfiguration = false;
    public $clientUsername = false;
    
    function __construct($type,$dir,$language  = 'english') { 
        
        MGLang::getInstance($dir.DS.'lang'.DS,$language);
        try{
            $mainConfig = array(
                    'dir'               => $dir
                    ,'type'             => $type
            );
            
            $additionalConfig = array();
            
            if(file_exists($dir.DS.'additionalConfig.php'))
            {
                include $dir.DS.'additionalConfig.php';
            }
            
            if(isset($additionalConfig['baseUrlConfig']))
            {
                $mainConfig['baseUrlConfig'] = $additionalConfig['baseUrlConfig'];
            }

            parent::__construct($mainConfig);
            
            $localAPIClass = $this->type.'LocalAPIDriver';

            if(!class_exists($localAPIClass))
            {
                throw new SystemException('Unable to find local API class:'.$localAPIClass);
            }

            HalonDriver::localAPI($localAPIClass,$this);
        }
        catch(Exception $ex)
        {
            MGExceptionLogger::addLog($ex);
            
            $message = null;
            
            if(method_exists($ex, 'getUserMessage'))
            {
                $message = $ex->getUserMessage();
            }
            
            if(empty($message))
            {
                $message = 'general';
            }
            
            MGLang::setContext('Main','errorMessages');
            $message = MGLang::T($message);
            
            if(method_exists($ex, 'getToken'))
            {
                $message .= MGLang::absoluteT('errorMessages','errorID').$ex->getToken();
            }
            
            die($message);
        }
    }
        
    public function setClientArea()
    {
        $this->dbConfiguration = HalonDriver::localAPI()->getDatabaseConfiguration();
        $this->clientUsername = HalonDriver::localAPI()->getCurrentUser();
        MGLang::loadLang(HalonDriver::localAPI()->getCurrentLang());
    }

    public function setAdminArea(){
        $this->allowRawExeptionMessages = true;
    }
    
    public function runHook($action, $data) {
        try {
            if($this->type == "WHM") {
                $content = file_get_contents("/usr/local/cpanel/etc/Halon.ini");
                $this->dbConfiguration = $content;
            }
            else {  
                $this->dbConfiguration = HalonDriver::localAPI()->getDatabaseConfiguration();
            }
            $this->clientUsername = $data['username'];
            $this->prepare();
            
            return $this->{$action}($data);
        }
        catch(Exception $e) { 
            throw new Exception($e->getMessage());
        }
    }
    
    private function getUserMainDomain($data) {
        return HalonDriver::localAPI()->getMainDomain($data['username']);
    }

    private function getUserDomains($data) {
        return HalonDriver::localAPI()->getUserDomainsFromWrapper($data['username']);
    }

    private function enableProtectionForNewDomain($data) {
        try {
            $configurationController = new HalonConfigurationController();
            $configuration = $configurationController->getModuleConfiguration(array("name" => "enableProtectionForNewDomains"));
            $mxRecordsController = new HalonMXRecordController($data['username'], $data['domain']);
            if($configuration['enableProtectionForNewDomains'] == "on") {
                HalonDriver::localAPI()->checkSpfStatusForUser($data['username']);
                $mxRecordsController->enableProtection();
                return array("domain" => $data['domain'], "result" => true);
            }
            else {
                $currentSpf = $mxRecordsController->getCurrentSpfRecordLine();
                $newValue = $mxRecordsController->removeDirectiveFromDefaultSpfRecordValue($currentSpf['txtdata']);
                $params = array("line" => $currentSpf['line'], "domain" => $data['domain'], "name" => $currentSpf['name'], "type" => "TXT",
                    "txtdata" => $newValue);
                HalonDriver::localAPI()->changeSpfRecord($data['username'], $params);
            }
        }
        catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    private function removeDomainFromDatabase($data) {
        try {
            $mxRecordController = new HalonMXRecordController("", $data['domain']);
            $mxRecordController->removeBackupFromDatabase();
            return array("domain" => $data['domain'], "result" => true);
        }
        catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getEnduserSettings() {
        try {
            $this->dbConfiguration = HalonDriver::localAPI()->getDatabaseConfiguration();
            $this->prepare();
            $configurationController = new HalonConfigurationController();
            $configuration = $configurationController->getModuleConfiguration();
            $enduserConfiguration = [];
            if (isset($configuration['enduserUrl']))
                $enduserConfiguration['enduserUrl'] = $configuration['enduserUrl'];
            if (isset($configuration['enduserApiKey']))
                $enduserConfiguration['enduserApiKey'] = $configuration['enduserApiKey'];
            return $enduserConfiguration;
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }

    private function prepare() {
       $configClass     = $this->type.'ConfigDriver';
        
       if(!class_exists($configClass))
       {
           throw new SystemException('Unable to find file for config driver:'.$configClass);
       }
      
       $dbConfig = new $configClass($this); 
       
       if(!$dbConfig->isNotEmpty())
       {
           throw new SystemException('Empty DB Configuration',404,'unableToLoadDBConfiguration');
       }
       
       MGMySQL::getInstance($dbConfig->db_host, $dbConfig->db_user, $dbConfig->db_pass, $dbConfig->db_name);
       
       //HalonDriver::config('HalonConfiguration',$this);
       
       ##
       # Remote Class API. Add here your custom API connection class!
       ##
       
//       $remoteAPIClass = 'HalonAPI';
//       
//       if(!class_exists($remoteAPIClass))
//       {
//           throw new SystemException('Unable to find remote API class:'.$remoteAPIClass);
//       }
//       
//       HalonDriver::remoteAPI($remoteAPIClass,$this);
    }
    
    function searchControlers()
    {
        $controllers = array();
        
        $configFile = $this->dir.DS.'controllers'.DS.$this->type.'EnabledControllers.ini';
        
        if(!file_exists($configFile))
        {
            throw new SystemException('Unable to find controllers configuration file:'.$configFile);
        }
        
        $config = parse_ini_file($configFile);
 
        foreach($config['controller'] as $controller)
        {
            $controllerFile = $this->dir.DS.'controllers'.DS.$this->type.$controller.'Controller.php';
            if(file_exists($controllerFile))
            {
                $controllers[] = $controller;
            }
        }

        return array(
            'controllers'   => $controllers
            ,'default'      => $config['defaultController']
        );
    }
    
    private function runAction($page = 'Main',$action = 'Main',$type='HTML',$input = array()){        
        $controllersDir     = $this->dir.DS.'controllers'.DS;
        $controllerClass    = $this->type.$page.'Controller';
        $controllerFile     = $controllersDir.$controllerClass.'.php';
                
        if(!file_exists($controllerFile))
        {
            throw new SystemException("Unable To Find File: ".$controllerFile);
        }

        require_once $controllerFile;

        if(!class_exists($controllerClass))
        {
            throw new SystemException("Unable To Find Class: ".$controllerClass,404,'controllerNotExits');
        }
        
        $controllerMethod = $action.$type.'Action';
        
        if(!method_exists($controllerClass, $controllerMethod))
        {
            throw new SystemException("Unable To Find Method: ".$controllerMethod,404,'actionNotExits');
        }
        
        MGLang::stagCurrentContext('runAction');
        MGLang::setContext($page);

        $controllerObject = new $controllerClass($this,array('page'=>$page));

        $data = $controllerObject->$controllerMethod($input);

        MGLang::unstagContext('runAction');
        
        return $data;
    }
    
    function getHTMLPage($page = 'Main', $action = 'Main',$input = array()){

        $controlerConfig = $this->searchControlers();
        
        $page = ($page === null)?$controlerConfig['default']:$page;
        $action = ($action === null)?'Main':$action;
        
        $content = array(
            'pages'         => $controlerConfig['controllers']
            ,'currentPage'  => $page
            ,'currenAjax'   => $this->getAJAX($page)
            ,'content'      => null
            ,'error'        => null
        );
        
        try{
            $this->prepare();

            $data = $this->runAction($page, $action, 'HTML',$input);

            if(empty($data['template']))
            {
                throw new SystemException('Template not provided');
            }

            if($data)
            {
                MGLang::stagCurrentContext('runAction');
                MGLang::setContext($page);
                $content['content'] = $this->getHTML($data['template'], $data['vars']);
                MGLang::unstagContext('runAction');
            }
        } 
        catch (Exception $ex) 
        {
            MGExceptionLogger::addLog($ex);
                        
            $message = null;
            
            if(method_exists($ex, 'getUserMessage'))
            {
                $message = $ex->getUserMessage();
            }
                       
            if(empty($message))
            {
                $message = 'general';
            }

            $messageLang = MGLang::absoluteT($page,'errorMessages',$message);
            
            if($message == $messageLang)
            {
                $messageLang = MGLang::absoluteT('errorMessages',$message);
            }
            
            if($message == $messageLang)
            {
                $messageLang = MGLang::absoluteT('errorMessages','general');
            }
            
            if($this->allowRawExeptionMessages && $message == $messageLang)
            {
                $messageLang = $ex->getMessage();
            }
            
            $content['error'] = $messageLang;
            
            if(method_exists($ex, 'getToken'))
            {
                $content['errorID'] = $ex->getToken();
            }
        }
        
        try{
            MGLang::stagCurrentContext('createContainer');
            MGLang::setContext('Container');
            $html = $this->getHTML('Container',$content);
            MGLang::unstagContext('createContainer');
            return $html;
        } catch (Exception $ex) {
            MGExceptionLogger::addLog($ex);
            
            $message = null;
            
            if(method_exists($ex, 'getUserMessage'))
            {
                $message = $ex->getUserMessage();
            }
            
            if(empty($message))
            {
                $message = 'general';
            }
            
            MGLang::setContext('Main','errorMessages');
            $message = MGLang::T($message);
            
            if(method_exists($ex, 'getToken'))
            {
                $message .= MGLang::absoluteT('errorMessages','errorID').$ex->getToken();
            }
            
            die($message);
        }
    }
    
    function getAPIResponse($page = 'Main', $action = 'Main', $input = array()){
                
        $controlerConfig = $this->searchControlers();
        
        $page = ($page === null)?$controlerConfig['default']:$page;
        $action = ($action === null)?'Main':$action;
        
        $content = array(
            'action'        => $page
            ,'result'       => null
        );
        
        try{
            $this->prepare();
            $content['result']  = 'success';
            $content['success'] = $this->runAction($page, $action, 'JSON', $input);
        } 
        catch (Exception $ex) 
        {
            MGExceptionLogger::addLog($ex);
            $message = null;
            
            if(method_exists($ex, 'getUserMessage'))
            {
                $message = $ex->getUserMessage();
            }
           
            if(empty($message))
            {
                $message = 'general';
            }
            
            $messageLang = MGLang::absoluteT($page,'errorMessages',$message);
            
            if($message == $messageLang)
            {
                $messageLang = MGLang::absoluteT('errorMessages',$message);
            }
            
            if($message == $messageLang)
            {
                $messageLang = MGLang::absoluteT('errorMessages','general');
            }
            
            if($this->allowRawExeptionMessages && $message == $messageLang)
            {
                $messageLang = $ex->getMessage();
            }
            
            if(method_exists($ex, 'getToken'))
            {
                $content['errorID'] = $ex->getToken();
            }

            $content['result']  = 'error';
            $content['error']   = $messageLang;
        }
        
        return json_encode($content);
    }
    
    function getJSONResponse($page = 'Main', $action = 'Main', $input = array()){
                
        $controlerConfig = $this->searchControlers();
        
        $page = ($page === null)?$controlerConfig['default']:$page;
        $action = ($action === null)?'Main':$action;
        
        $content = array(
            'action'        => $page
            ,'result'       => null
        );
        
        try{
            $this->prepare();
            $content['result']  = 'success';
            $content['success'] = $this->runAction($page, $action, 'JSON', $input);
        } 
        catch (Exception $ex) 
        {            
            MGExceptionLogger::addLog($ex);
                        
            $message = null;
            
            if(method_exists($ex, 'getUserMessage'))
            {
                $message = $ex->getUserMessage();
            }
                       
            if(empty($message))
            {
                $message = 'general';
            }

            $messageLang = MGLang::absoluteT($page,'errorMessages',$message);

            if($message == $messageLang)
            {
                $messageLang = MGLang::absoluteT('errorMessages',$message);
            }
            
            if($message == $messageLang)
            {
                $messageLang = MGLang::absoluteT('errorMessages','general');
            }
            
            if($this->allowRawExeptionMessages && $message == $messageLang)
            {
                $messageLang = $ex->getMessage();
            }
            
            $content['result']  = 'error';
            $content['error'] = $messageLang;
            
            if(method_exists($ex, 'getToken'))
            {
                $content['errorID'] = $ex->getToken();
            }
        }
        if($input["action"] == "getDomains") {
            return $this->getPureJSON($content['success']);
        }
        return $this->getJSON($content);
    }
    
    function runTaskCron($controler, $maxItems = 10){
        try{ 
            $this->prepare();
            
            $taskManager = new MGCronTaskManager();

            $start = time();

            $diff  = 0;

            $maxSeconds = $taskManager->maxMinutes*60;

            $itemNumber = 1;

            while($itemNumber <= $maxItems && $diff < $maxSeconds && $taskManager->takeTaskFromStack())
            {
                $task = $taskManager->getCurrentTask();
                
                try{
                    $result = $this->runAction($controler, $task->action, 'Cron', $task);

                    $taskManager->setTaskResult($result);
                } catch (Exception $ex) {
                    MGExceptionLogger::addLog($ex);
                    
                    $message = null;

                    if(method_exists($ex, 'getUserMessage'))
                    {
                        $message = $ex->getUserMessage();
                    }

                    if(empty($message))
                    {
                        $message = 'general';
                    }
                    
                    MGLang::setContext('cron','errorMessages');

                    $message = MGLang::T($message);
                    
                    if(method_exists($ex, 'getToken'))
                    {
                        $message .= MGLang::absoluteT('errorMessages','errorID').$ex->getToken();
                    }
                    
                    $taskManager->setTaskResult($message);
                }
                $diff = time()-$start;
                $itemNumber++;
            }
            
            return null;

        } catch (Exception $ex) {
            MGExceptionLogger::addLog($ex);   
            
            $message =  $ex->getMessage();
            
            if(method_exists($ex, 'getToken'))
            {
                $message .= MGLang::absoluteT('errorMessages','errorID').$ex->getToken();
            }
            
            return $message;
        }
    }        
    
    function runCron($controler, $action){
        try{
            $this->prepare();
            $this->runAction($controler, $action, 'Cron');
        } catch (Exception $ex) {
            MGExceptionLogger::addLog($ex);   
            
            $message =  $ex->getMessage();
            
            if(method_exists($ex, 'getToken'))
            {
                $message .= MGLang::absoluteT('errorMessages','errorID').$ex->getToken();
            }
            
            return $message;
        }
    }
}
