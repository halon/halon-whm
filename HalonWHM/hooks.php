#!/usr/local/cpanel/3rdparty/php/56/bin/php -q

<?php

require_once '/usr/local/cpanel/share/Halon/HalonLoader.php';

class HookManager {
    
    private $data;
    private $action;
    
    public function loadEventData($argv) {
        $this->data = json_decode($this->readEventData(), true);
        $this->action = substr($argv[1], 2);
    }
    
    public function readEventData() {
        $data = '';
        $stdin = fopen('php://stdin', 'r');
        while ($line = fgets($stdin)) {
            $data .= $line;
        }
        fclose($stdin);

        return $data;
    }
    
    public function getEventParams() {
        switch($this->action) {
            case "createaccount": return array("domain" => $this->data['data']['domain'], "user" => $this->data['data']['user']);
            case "addaddondomain": return array("domain" => $this->data['data']['args']['newdomain'], "user" => $this->data['data']['user']);
            case "parkdomain": return array("domain" => $this->data['data']['new_domain'], "user" => $this->data['data']['user']);
          //  case "parkdomain1": return array("domain" => $this->data['data']['args'][0], "user" => $this->data['data']['user']);
          //  case "parkdomain2": return array("domain" => $this->data['data']['args']['domain'], "user" => $this->data['data']['user']);
        }
    }
    
    public function runAction($argv) {
        $this->loadEventData($argv);
        $params = $this->getEventParams();
        $Main = $this->getController(); 
        $mainDomain = $Main->runHook("getUserMainDomain", array("user" => $params['user']));
       // if(strpos($this->data['data']['target_domain'], $mainDomain === false)||!isset($this->data['data']['target_domain'])) {
        $response = $Main->runHook("enableProtectionForNewDomain", $params);
     //   }
    }
    
    public function getController() {
        if(posix_getuid() == 0) {
            $Main = new HalonMainController('WHM',__DIR__);
        }
        else {
            $Main = new HalonMainController('CPanel',__DIR__);
        }
        return $Main;
    }
}

$hookManager = new HookManager();
$hookManager->runAction($argv);
