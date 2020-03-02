<?php

class CPanelLocalAPIDriver extends WHMLocalAPIDriver{
    public $clientUsername;
    public $cpanel;
    
    function __construct($params) {
        $this->cpanel = $GLOBALS['CPANEL'];
        parent::__construct($params);
    }
     
    public function _connect($params){
        
        $content = explode("\n", $this->getAccessHash());
        $this->apiKey = $this->getToken($content);
        
        if(empty($this->apiKey))
        {
            throw new SystemException("Can't find Api Key File",404);
        }
        
        $this->clientUsername = $this->getCurrentUser();
    }
    
    private function _callUAPI($module,$action,$params = array()){
        $ret = $this->cpanel->uapi($module, $action, $params);

        if(!empty($ret['cpanelresult']['result']['errors']))
        {
            throw new SystemException(implode('/',$ret['cpanelresult']['result']['errors']),0);
        }
        
        return $ret['cpanelresult']['result']['data'];
    }
            
    function getDatabaseConfiguration(){
        return parent::getDatabaseConfigurationFromWrapper();
    }    
    
    function getAccessHash(){
        return parent::getAccessHashFromWrapper();
    }
    
    function getCurrentUser(){
        if($this->cpanel) {
            return $this->cpanel->cpanelprint('$user');
        }
        return "";
    }
    
    function getCurrentLang(){
        return $this->cpanel->cpanelprint('$lang');
    }

    function getDomainRelPatch($domain)
    {
        $result = $this->_userRequest($this->clientUsername,'DomainLookup', 'getdocroot', array('domain' => $domain));
        return $result['cpanelresult']['data'][0]['reldocroot'];
    }
    
    function putValidationFile($domain, $filename, $content)
    { 
        $domainPath = $this->getDomainRelPatch($domain);
        $this->_userRequest($this->clientUsername,'Fileman','savefile',array('dir' => $domainPath, 'filename' => $filename, 'content' => $content));
        $result = $this->_userRequest($this->clientUsername,'Fileman','viewfile',array('dir' => $domainPath, 'file' => $filename));
        if(!(isset($result['cpanelresult']['data'][0]['contents']) && $result['cpanelresult']['data'][0]['contents'] == $content))
        {
            throw new SystemException('API ERROR',0,'cpanelError',array('Unable to upload csr hash file for the domain: '.$domain.'.'));
        }
    }

    function generateCsr($params = null)
    {
        $listCsrsResult = $this->_callUAPI('SSL', 'list_csrs');
        if(!empty($listCsrsResult))
        {
            foreach($listCsrsResult as $csr)
            { 
                $allOk = count($csr['domains']) == count($params['domains']);
                if($allOk === false)
                {
                    continue;
                }
                foreach($csr['domains'] as $domain)
                {
                    if(!in_array($domain, $params['domains']))
                    {
                        $allOk = false;
                        break;
                    }
                }
                
                if($allOk === true)
                {
                    $showCsrResult = $this->_callUAPI('SSL', 'show_csr', array('id' => $csr['id']));
//                    echo "<PRE>";
//                    die(var_dump($params));
                    if($showCsrResult['details']['countryName'] !== $params['country'])
                    {
                        continue;
                    }
                    if($showCsrResult['details']['stateOrProvinceName'] !== $params['state'])
                    {
                        continue;
                    }
                    if($showCsrResult['details']['localityName'] !== $params['city'])
                    {
                        continue;
                    }
                    if($showCsrResult['details']['organizationName'] !== $params['company'])
                    {
                        continue;
                    }
                    if($showCsrResult['details']['emailAddress'] !== $params['email'])
                    {
                        continue;
                    }
                    if($showCsrResult['details']['organizationalUnitName'] !== $params['division'])
                    {
                        continue;
                    }

                    $listKeysResult = $this->_callUAPI('SSL', 'list_keys');
                    foreach($listKeysResult as $key)
                    {
                        if($key['modulus'] == $showCsrResult['details']['modulus'])
                        {
                            $showKeyResult = $this->_callUAPI('SSL', 'show_key', array('id' => $key['id']));
                            if(!empty($showCsrResult['csr']) || !empty($showKeyResult['key']))
                            {
                                return array(
                                    'csr' => $showCsrResult['csr'],
                                    'key' => $showKeyResult['key']
                                );
                            }
                        }
                    }    
                }
            }
        }  

        $generateKeyResult = $this->_callUAPI('SSL', 'generate_key', array('keysize' => '2048'));
//        echo "GENERATING WITH <pre>";        
//        die(var_dump(array(
//                'key_id'                  => $generateKeyResult['id'],
//                'domains'                 => implode(',', $params['domains']),
//                'countryName'             => $params['country'],
//                'stateOrProvinceName'     => mb_convert_encoding($params['state'], "UTF-8"),
//                'localityName'            => mb_convert_encoding($params['city'], "UTF-8"),
//                'organizationName'        => mb_convert_encoding($params['company'], "UTF-8"),
//                'emailAddress'            => $params['email'],
//                'organizationalUnitName'  => $params['division'],
//                )));
        $generateCsrResult = $this->_callUAPI('SSL', 'generate_csr', array(
                'key_id'                  => $generateKeyResult['id'],
                'domains'                 => implode(',', $params['domains']),
                'countryName'             => $params['country'],
                'stateOrProvinceName'     => mb_convert_encoding($params['state'], "UTF-8"),
                'localityName'            => mb_convert_encoding($params['city'], "UTF-8"),
                'organizationName'        => mb_convert_encoding($params['company'], "UTF-8"),
                'emailAddress'            => $params['email'],
                'organizationalUnitName'  => $params['division'],
                )
        );
    
        return array(
            'csr' => $generateCsrResult['text'],
            'key' => $generateKeyResult['text']
        );
    }
    
    function getUserDomains($user = null, $main = null) {
        return parent::getUserDomains($this->clientUsername, $main);
    }
    
    function getMainDomain($user = null) {
        if($user) {
            return parent::getMainDomain($user);
        }
        return parent::getMainDomain($this->clientUsername);
    }
    
    function getUserCerts($user = null){
        $output = $this->_callUAPI('SSL', 'list_certs');
        return $output;
    }
    
    function getUserCert($user = null, $certID = null){
        $output = $this->_callUAPI('SSL', 'show_cert',array(
            'id'    => $certID
        ));
        return $output;
    }
    
    function getAccounts() {
        return array();
    }
    
}

 
