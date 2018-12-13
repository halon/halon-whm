#!/usr/local/cpanel/3rdparty/php/72/bin/php -q

<?php

require_once '/usr/local/cpanel/share/Halon/HalonLoader.php';

class HookManager {
    
    private $data;
    private $action; 
    private $hooks = array("addingDomains" => array("createaccount", "addaddondomain", "park1"), "removingDomains" => array("domainUnpark", "terminate"));
    
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
            case "createaccount": return array("domain" => $this->data['data']['domain'], "username" => $this->data['data']['user']);
            case "addaddondomain": return array("domain" => $this->data['data']['args']['newdomain'], "username" => $this->data['data']['user']);
            case "park1": return array("domain" => $this->data['data']['args'][0], "username" => $this->data['data']['user']);
            case "domainUnpark": return array("domain" => $this->data['data']['domain']);
            case "terminate": return array("username" => $this->data['data']['user']);
        }
    }
    
    public function runAction($argv) {
        try {
            $this->loadEventData($argv);
            $params = $this->getEventParams();
            $Main = $this->getController(); 
            if(in_array($this->action, $this->hooks["addingDomains"])) {
                $response = $Main->runHook("enableProtectionForNewDomain", $params);
            }
            else if($this->action == "terminate") {
                if(posix_getuid() == 0) { 
                    $userDomains = $this->getUserDomains($params['username']);
                }
                else {
                    $userDomains = $Main->runHook("getUserDomains", $params);
                }
                foreach($userDomains as $domain) {
                    $response = $Main->runHook("removeDomainFromDatabase", array("domain" => $domain));
                }
            }
            else {
                $response = $Main->runHook("removeDomainFromDatabase", $params);
            }
            echo 1;
        }
        catch(\Exception $e) {
            echo $e->getMessage();
        }
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

    public function getUserDomains($username) {
        $content = file_get_contents("/etc/userdomains");
        if(!$content) {
            return false;
        }
        $domainsArray = explode("\n", $content);
        $userDomains = array();
        foreach($domainsArray as $domain) {
            $domainName = substr($domain, 0, strpos($domain, ":"));
            $user = trim(substr($domain, strpos($domain, ":") + 1));
            if($username == $user) {
                $userDomains[] = $domainName;
            }
        }
        return $userDomains;    
    }
}

$hookManager = new HookManager();
$hookManager->runAction($argv);
