<?php

class WHMLocalAPIDriver extends AbstractLocalAPIDriver{
    protected $apiKey;
    private $sortCol        = 'domain';
    private $sortVect       = 'ASC';
    public $currentUsername = false;
    public $currentDomain   = false;
    public $lastParams      = array();
    public $typeList        = array('base','addon','parked');
        
    function __construct($params) {
        parent::__construct($params);
    }

    public function _connect($params){

        $accesKeyFile = '/root/.halon_tokenapi';
        
        if(!file_exists($accesKeyFile))
        {
            throw new SystemException("Cant find Acces Hash File",14041);
        }
        
        $handle = fopen($accesKeyFile,'r');
        $content = explode("\n", fread($handle, 1024));
        $this->apiKey = $this->getToken($content);

        if(empty($this->apiKey))
        {
            throw new SystemException("Cant find Acces Hash File",14041);
        }
    }
    
    protected function getToken($fileContent) {
        foreach($fileContent as $line) {
            if(strpos($line, "token") !== false) {
                $token = substr($line, strpos($line, "token") + 7);
                return $token;
            }
        }
        return "";
    }

    private function _request($function,array $params = array(), $version = "0"){
        $ch = curl_init();
        
        $this->lastParams = $params;
        if($version == "1") {
            $url = "http://127.0.0.1:2086/json-api/".$function.'?api.version=1';
            if(!empty($params)) {
                $url .= "&".http_build_query($params);
            }
            curl_setopt($ch, CURLOPT_URL, $url);
        }
        else {
            curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1:2086/json-api/".$function.'?'.http_build_query($params));
        }

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $header[0] = "Authorization: WHM root:" . $this->apiKey;
        # Remove newlines from the hash
        curl_setopt($ch,CURLOPT_HTTPHEADER,$header);

        curl_setopt($ch, CURLOPT_TIMEOUT, 400);
        $data = curl_exec($ch);

        curl_close($ch);
        
        $response = json_decode($data,TRUE);
                        
        return $response;
    }
    
    protected function _userRequest($user,$module,$function,$params = array())
    {
        $params['cpanel_jsonapi_user']     = $user;
        $params['cpanel_jsonapi_module']   = $module;
        $params['cpanel_jsonapi_func']     = $function;
  
        $response = $this->_request('cpanel', $params);

        if(!empty($response['error'])){
            throw new SystemException($response['error'],0,'cpanelError',array('raw'=>$response,'last'=> $this->lastParams));
        }
        
        if(!empty($response['cpanelresult']['error']))
        {
            throw new SystemException($response['cpanelresult']['error'],0,'cpanelError',array('raw'=>$response,'last'=> $this->lastParams));
        }
        
        if(isset($response['cpanelresult']['postevent']))
        {
            if($response['cpanelresult']['postevent']['result'] != 1)
            {
                throw new SystemException('API ERROR',$response['cpanelresult']['postevent']['result'],'cpanelError',array('raw'=>$response,'last'=> $this->lastParams));
            }
        }
        else
        {
            if(isset($response['cpanelresult']['event']['result']) != 1)
            {
                throw new SystemException('API ERROR',$response['cpanelresult']['event']['result'],'cpanelError',array('raw'=>$response,'last'=> $this->lastParams));
            }
        }
        return $response;
    }

    public function checkZoneEditFeature() {
        $result = $this->_request("get_featurelist_data", array("featurelist" => "disabled"), "1");
        foreach($result['data']['features'] as $feature) {
            if($feature['id'] == "zoneedit"&&$feature['value'] == "1") {
                return true;
            }
        }
        return false;
    }

    function getMainDomain($account){
        $main = $this->_userRequest($account, 'DomainLookup', 'getmaindomain');
        return $main['cpanelresult']['data'][0]['main_domain'];
    }

    public function getDatabaseConfigurationFromWrapper() {
        return shell_exec('/usr/local/cpanel/3rdparty/perl/524/bin/perl /usr/local/cpanel/share/Halon/Wrapper/wrapper.pl getConfiguration');
    }
    
    public function getAccessHashFromWrapper() {
        return shell_exec('/usr/local/cpanel/3rdparty/perl/524/bin/perl /usr/local/cpanel/share/Halon/Wrapper/wrapper.pl getAccessHash');
    }
       
    public function installCrt($account,$params) {
        $result = $this->_userRequest($account,'SSL','installssl',$params);
        return $result;
    }
    
    function getDomainsList(){
       $myfile = fopen("/etc/userdatadomains.json", "r");
       $data   = fread($myfile,filesize("/etc/userdatadomains.json"));
       fclose($myfile);
       return $data;
    }
    
    public function getAccounts(){
        $accounts = $this->_request('listaccts');
        $output = array();
        foreach ($accounts['acct'] as $account)
        {
            $output[] = array(
                'name'        => $account['user'],
                'locked'     => $account['is_locked'],
                'suspended' => $account['suspended'],
                'mainDomain' => $account['domain']
            );
        }
        return $output;
    }
    
    function getUserDomains($user = null, $main = null)
    {
        $domainsList = array();

        if($main == null)
        {
            $main = $this->getMainDomain($user);
        }

        $domainsList[] = array(
                    'domain'    => $main
                    ,'user'     => $user
                    ,'type'     => 'Base'
                    ,'root'     => $main
        );
        
        //get subdomains
        
        $subdomains = $this->_userRequest($user, 'SubDomain', 'listsubdomains');
        if(!empty($subdomains['cpanelresult']['data']) && is_array($subdomains['cpanelresult']['data']))
        {
            foreach($subdomains['cpanelresult']['data'] as $subDomain)
            {
                $domainsList[] = array(
                                'domain'    => $subDomain['domain']
                                ,'user'     => $user
                                ,'type'     => 'Subdomain'
                                ,'root'     => $subDomain['rootdomain'],
                                'status' => $subDomain['status']
                );
            }
        }
        
        //get addon domain
        $addons = $this->_userRequest($user, 'AddonDomain', 'listaddondomains');
        if(!empty($addons['cpanelresult']['data']) && is_array($addons['cpanelresult']['data']))
        {
            foreach($addons['cpanelresult']['data'] as $addonDomain)
            {
                $domainsList[] = array(
                                'domain'    => $addonDomain['domain']
                                ,'user'     => $user
                                ,'type'     => 'Addon'
                                ,'root'     => $addonDomain['rootdomain'],
                                'status'    => $addonDomain['status']
                );
            }
        }
        
        
        //GET Parked
        $parked = $this->_userRequest($user, 'Park', 'listparkeddomains');
        if(!empty($parked['cpanelresult']['data']) && is_array($parked['cpanelresult']['data']))
        {
            foreach($parked['cpanelresult']['data'] as $addonDomain)
            {
                $domainsList[] = array(
                                'domain'    => $addonDomain['domain']
                                ,'user'     => $user
                                ,'type'     => 'Parked',
                                'status'    => $addonDomain['status']
                );
            }
        }
        
        return $domainsList;
    }
    
    public function checkSpfStatusForUser($username) {
        try {
            $response = $this->_userRequest($username, "SPFUI", "installed", array("user" => $username));
            $status = $response['cpanelresult']['data'][0]['installed'];
            if($status) {
                return $status;
            }
            throw new Exception($username.": SPF is not installed");
        }
        catch(Exception $e) {
            throw new Exception($e->getMessage());
        } 
    } 
    
    public function getZoneRecords($username, $domain) {
        try {
            $response = $this->_userRequest($username, "ZoneEdit", "fetchzone_records", array("domain" => $domain, "type" => 'MX'));
            $mxRecords = array();
            foreach($response['cpanelresult']['data'] as $record) {
                if(substr($record['name'], 0, -1) != $domain) {
                    continue;
                }
                $mxRecords[] = array("exchange" => $record['exchange'], "name" => $record['name'], "record" => $record['record'],
                    "preference" => $record['preference'], "line" => $record['line']);
            }

            $response = $this->getSpfRecords($username, $domain);
            $spfRecords = array();
            foreach($response as $record) {
                if(substr($record['name'], 0, -1) != $domain) {
                    continue;
                }
                $spfRecords[] = array("name" => $record['name'], 'record' => $record['record'], "txtdata" => $record['txtdata'], "line" => $record['line']);
            }
            return array("mxRecords" => $mxRecords, "spfRecords" => $spfRecords);
        }
        catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    public function getSpfRecords($username, $domain) {
        $response = $this->_userRequest($username, "ZoneEdit", "fetchzone_records", array("domain" => $domain, "type" => 'TXT'));
        return $response['cpanelresult']['data'];
    }
    
    public function changeSpfRecord($username, $params) {
         try {
            $response = $this->_userRequest($username, "ZoneEdit", "edit_zone_record", $params);
            $status = $response['cpanelresult']['data'][0]['result']['status'];
            if($status) {
                return $status;
            }
            throw new Exception($response['cpanelresult']['data'][0]['result']['statusmsg']);
        } 
        catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    public function editMxRecord($username, $params) {
        try {
            $response = $this->_userRequest($username, "Email", "changemx", $params);
            $status = $response['cpanelresult']['data'][0]['status'];
            if($status) {
                return $status;
            }
            throw new Exception($response['cpanelresult']['data'][0]['statusmsg']);
        } 
        catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    public function removeMxRecord($username, $params) {
        try {
            $response = $this->_userRequest($username, "Email", "delmx", $params);
            $status = $response['cpanelresult']['data'][0]['status'];
            if($status) {
                return $status;
            }
            throw new Exception($response['cpanelresult']['data'][0]['statusmsg']);
        }
        catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    public function addMxRecord($username, $params) {
        try {
            $response = $this->_userRequest($username, "Email", "addmx", $params);
            $status = $response['cpanelresult']['data'][0]['status'];
            if($status) {
                return $status;
            }
            throw new Exception($response['cpanelresult']['data'][0]['statusmsg']);
        }
        catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    public function setMxCheck($username, $params) {
        try {
            $response = $this->_userRequest($username, "Email", "setmxcheck", $params);
            $status = $response['cpanelresult']['data'][0]['status'];
            if($status) {
                return $status;
            }
            throw new Exception($response['cpanelresult']['data'][0]['statusmsg']);
        }
        catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
