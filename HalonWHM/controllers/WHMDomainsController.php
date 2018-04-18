<?php

class WHMDomainsController extends AbstractControler{
    function __construct($params,$options) { 
        parent::__construct($params,$options);
    }

    public function MainHTMLAction($input, $data = array()) {

        $data['domains'] = $this->getDomains();
        
        return array(
            'template'   => 'Domains'
            ,'vars'      => $data
        );
    } 
    
    private function getDomains() {
        //Get domains with enabled protection
        $mxRecordController = new HalonMXRecordController();
        $domainsWithCustomMXRecords = $mxRecordController->getDomainsWithCustomMXRecords();

        //Get all domains from cPanel
        $fileContent = HalonDriver::localAPI()->getDomainsList();
        $fileContent = json_decode($fileContent); 
        $domains = array();

        foreach($fileContent as $domain => $domainInfo) {
            if($domainInfo[2] == "sub") {
                continue;
            }
            $checkbox = "<input type='checkbox' name='checkedItems' data-domain='$domain' data-username='{$domainInfo[0]}' />";
            $status = in_array($domain, $domainsWithCustomMXRecords)?MGLang::T("enabledStatus"):MGLang::T("disabledStatus");
            if(in_array($domain, $domainsWithCustomMXRecords)) {
                $class = "btn btn-danger";
                $buttonContent = MGLang::T("disableProtectionButtonValue");
                $type = "disable";
            }
            else {
                $class = "btn btn-success";
                $buttonContent = MGLang::T("enableProtectionButtonValue");
                $type = "enable";
            }
            $button = "<button name='toggleProtection' data-domain='$domain' data-username='{$domainInfo[0]}' class='$class' data-type='$type' 
                data-enablecontent='" . MGLang::T("enableProtectionButtonValue") . "' data-disablecontent='" . 
                    MGLang::T("disableProtectionButtonValue") . "' >$buttonContent</button>";
            $domains[] = array($checkbox, $domain, $status, $button);
        }
        return $domains;
    }
    
    public function getDomainsJSONAction($input) {
        $domains = $this->getDomains();
        $total = count($domains);
        if(isset($input['search']['value']) && $input['search']['value'] != "" ) {
            $domains = $this->filterDomains($domains, $input['search']['value']);
        }

        if(isset($input['order'][0]['column'])) {
            $column = $input['order'][0]['column'];
            $order = $input['order'][0]['dir'];
            $domains = $this->orderDomains($domains, $column, $order);
        }
         
        $resultArrayAfterApplyingFilterAndOrder = empty($domains)?array():$domains;
        return array(
            'draw' => $input['draw'],
            'recordsTotal'=> $total,
            'recordsFiltered' => count($resultArrayAfterApplyingFilterAndOrder),
            'start' => $input['start'],
            'length' => $input['length'],
            'data' => $resultArrayAfterApplyingFilterAndOrder,
        );
    }
 
    private function filterDomains($domains, $searchValue) {
        $searchResult = array();
        foreach($domains as $domain) {
            if((strpos($domain[1], $searchValue) !== false)) {
                $searchResult[] = $domain;
            }
        }
        return $searchResult;
    } 
            
    private function orderDomains($domains, $column, $order) {
        switch($column) {
            case 1: usort($domains, function($a, $b) {
                        return strcmp($a[1], $b[1]);
                    });
                    return ($order == "desc")?array_reverse($domains):$domains;
            case 2: usort($domains, function($a, $b) { 
                        return strcmp($a[2], $b[2]);
                    });
                    return ($order == "desc")?array_reverse($domains):$domains;
        }
    }
    
    public function toggleProtectionJSONAction($input) {
        $username = $input['data']['username'];
        $type = $input['data']['type'];
        $domain = $input['data']['domain'];
        $result = array();
        if($type == "enable") {
            $result[] = $this->enableProtection($domain, $username);
        }
        else {
            $result[] = $this->disableProtection($domain, $username);
        }
        return array("result" => $result);
    }
    
    public function toggleBulkProtectionJSONAction($input) {
        
        $mxRecordController = new HalonMXRecordController();
        $domainsWithEnabledProtection = $mxRecordController->getDomainsWithCustomMXRecords();
        
        $domains = $input['data']['items'];
        $type = $input['data']['type'];
        $method = ($type == "enable")?"enableProtection":"disableProtection";
        $result = array();
        foreach($domains as $domain) {
            if(in_array($domain['domain'], $domainsWithEnabledProtection)&&$type == "enable") {
                $result[] = array("domain" => $domain['domain'], "result" => "Protection for that domain is already enabled"); 
                continue;
            }
            else if(!in_array($domain['domain'], $domainsWithEnabledProtection)&&$type == "disable") {
                $result[] = array("domain" => $domain['domain'], "result" => "Protection for that domain is already disabled"); 
                continue;
            }
            $result[] = $this->{$method}($domain['domain'], $domain['username']);
        }
        return array("result" => $result);
    }
    
    public function enableProtection($domain = "", $username = "") {
        try {
            HalonDriver::localAPI()->checkSpfStatusForUser($username);
            $mxRecordsController = new HalonMXRecordController($username, $domain);
            $mxRecordsController->enableProtection();
            
            return array("domain" => $domain, "result" => true);
        }
        catch(Exception $e) {
            return array("domain" => $domain, "result" => $e->getMessage());
        }
    }
    
    public function disableProtection($domain = "", $username = "") {
        try {
            HalonDriver::localAPI()->checkSpfStatusForUser($username);
            
            //Get default MX records form database
            $mxRecordsController = new HalonMXRecordController($username, $domain);
            $mxRecordsController->disableProtection();
     
            return array("domain" => $domain, "result" => true);
        }
        catch(Exception $e) {
            return array("domain" => $domain, "result" => $e->getMessage());
        }
    } 
}
