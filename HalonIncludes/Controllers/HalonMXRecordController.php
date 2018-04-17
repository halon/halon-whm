<?php

class HalonMXRecordController {
    
    private $username;
    private $domain;
    private $currentRecords;
    
    public function __construct($username = "", $domain = "") {
        $this->username = $username;
        $this->domain = $domain;
    }
    
    public function getDomainsWithCustomMXRecords() {
        $model = new HalonMXRecordModel();
        $result = $model->getDomainsWithCustomMXRecords();
        foreach($result as $key => $value) {
            $result[$key] = $value["domain"];
        }
        return $result;
    }
    
    private function saveDefaultRecords($data) {
        $data['records'] = serialize($data['records']);
        $model = new HalonMXRecordModel();
        $model->saveDefaultRecords($data);
    }
    
    private function removeBackupFromDatabase() {
        $model = new HalonMXRecordModel();
        $model->removeDefaultRecords($this->domain);
    }
    
    private function restoreDefaultRecords() {
        $model = new HalonMXRecordModel();
        $records = $model->getDefaultRecords($this->domain);
        $records['mx_records'] = unserialize($records['mx_records']);
        return $records;
    }
  
    public function enableProtection() {
        try{
            $configurationController = new HalonConfigurationController();
            $moduleConfiguration = $configurationController->getModuleConfiguration();
            $customMxRecords = $moduleConfiguration['customMxRecords'];
            $spfHostname = $moduleConfiguration['spfHostname'];

            if(empty($customMxRecords)) {
                throw new Exception("No custom MX records in module configuration.");
            }
            
            if(empty($spfHostname)) {
                throw new Exception("No SPF Hostname in module configuration.");
            }

            $this->currentRecords = HalonDriver::localAPI()->getZoneRecords($this->username, $this->domain);
            if(!$this->checkIfDefault()) {
                throw new Exception("This domain has got custom MX records.");
            }

            $this->backupMxRecords(); 
            $this->editRecords($customMxRecords);
            $this->changeSpfRecord("addDirective", $spfHostname);
            HalonDriver::localAPI()->setMxCheck($this->username, array("domain" => $this->domain, "mxcheck" => "local"));
        }
        catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    public function disableProtection() { 
        try {
            $defaultRecords = $this->restoreDefaultRecords();
            $this->currentRecords = HalonDriver::localAPI()->getZoneRecords($this->username, $this->domain); 
            $this->editRecords($defaultRecords['mx_records']['mxRecords']);
            $this->changeSpfRecord("removeDirective", null, $defaultRecords['mx_records']['spfRecords']);
            HalonDriver::localAPI()->setMxCheck($this->username, array("domain" => $this->domain, "mxcheck" => "auto"));
            $this->removeBackupFromDatabase();
        }
        catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    public function checkIfDefault() {
        if((count($this->currentRecords['mxRecords']) > 1)||(count($this->currentRecords['mxRecords']) == 1&&
                $this->currentRecords['mxRecords'][0]['exchange'] != $this->domain)) {
            return false;
        }
        return true;
    } 
    
    public function checkSPFRecord($action) {
        foreach($this->currentRecords['spfRecords'] as $key => $record) {
            if((strpos($record['record'], "v=spf") !== false)&&(((strpos($record['record'], "include") === false)&&$action == "addDirective")||
                    $action == "removeDirective")) {
                if($action == "removeDirective") {
                    $currentLine = $this->getCurrentSpfRecordLine();
                    if($currentLine === false) {
                        throw new Exception("No SPF record to fetch.");
                    }
                    $this->currentRecords['spfRecords'][$key]['line'] = $currentLine;
                }
                $this->currentRecords['spfRecords'][$key]['key'] = $key;
                return $this->currentRecords['spfRecords'][$key];
            }
        }
        return false;
    }
    
    public function getCurrentSpfRecordLine() {
        $currentSpf = HalonDriver::localAPI()->getSpfRecords($this->username, $this->domain); 
        foreach($currentSpf as $record) {
            if(strpos($record['record'], "v=spf") !== false) {
                return $record['line'];
            }
        }
        return false;
    }
 
    private function backupMxRecords() {
        $data = array("domain" => $this->domain, "records" => $this->currentRecords);
        $this->saveDefaultRecords($data);
    }
    
    private function changeSpfRecord($action, $spfHostname = null, $defaultRecords = array()) {
        try {
            $response = $this->checkSPFRecord($action);
            if(is_array($response)) {
                $newValue = $action == "addDirective"?$this->addDirective($response['txtdata'], $spfHostname):$defaultRecords[$response['key']]['txtdata'];
                $params = array("line" => $response['line'], "domain" => $this->domain, "name" => $response['name'], "type" => "TXT",
                    "txtdata" => $newValue);
                HalonDriver::localAPI()->changeSpfRecord($this->username, $params);      
            }
        }
        catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    private function addDirective($spfRecord, $spfHostname) {
        $pos = strpos($spfRecord, "v=spf");
        $i = 0;
        while($spfRecord[$pos + strlen("v=spf") + $i] != " ") {
            $i++;  
        }
        $substr = substr($spfRecord, 0, $pos + strlen("v=spf") + $i);
        $spfRecord = str_replace($substr, "$substr include:".$spfHostname, $spfRecord);
        return $spfRecord;       
    }
    
    private function addRecords($records) {
        try {
            foreach($records as $record) {
                $params = array("domain" => $this->domain, "exchange" => isset($record['exchange'])?$record['exchange']:$record[1], 
                    "oldexchange" => "", "oldpreference" => "", "preference" => isset($record['preference'])?$record['preference']:$record[0]);
                HalonDriver::localAPI()->addMxRecord($this->username, $params);
            }
        }
        catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    private function editRecords($recordsToSet) {
        try {
            //If there are more default records than custom records
            if(count($this->currentRecords['mxRecords']) > count($recordsToSet)) {
                for($i = 0; $i <= count($recordsToSet) - 1; $i++) {
                    $params = array("domain" => $this->domain, "exchange" => isset($recordsToSet[$i]['exchange'])?$recordsToSet[$i]['exchange']:$recordsToSet[$i][1], 
                        "oldexchange" => $this->currentRecords['mxRecords'][$i]['exchange'],
                        "oldpreference" => $this->currentRecords['mxRecords'][$i]['preference'], 
                        "preference" => isset($recordsToSet[$i]['preference'])?$recordsToSet[$i]['preference']:$recordsToSet[$i][0]);
                    HalonDriver::localAPI()->editMxRecord($this->username, $params);
                } 
                //Removing rest of current records
                $recordsToRemove = array_slice($this->currentRecords['mxRecords'], count($recordsToSet));
                $this->removeRecords($recordsToRemove);
            }
            else {
                $editedRecords = array();
                for($i = 0; $i <= count($this->currentRecords['mxRecords']) - 1; $i++) {
                    $params = array("domain" => $this->domain, "exchange" => isset($recordsToSet[$i]['exchange'])?$recordsToSet[$i]['exchange']:$recordsToSet[$i][1], 
                        "oldexchange" => $this->currentRecords['mxRecords'][$i]['exchange'],
                        "oldpreference" => $this->currentRecords['mxRecords'][$i]['preference'], 
                        "preference" => isset($recordsToSet[$i]['preference'])?$recordsToSet[$i]['preference']:$recordsToSet[$i][0]);
                    HalonDriver::localAPI()->editMxRecord($this->username, $params);
                    $editedRecords[] = $recordsToSet[$i][1];
                } 

                //Adding rest of custom records
                $toAdd = $this->getRecordsToAdd($editedRecords, $recordsToSet);
                if(!empty($toAdd)) {
                    $this->addRecords($toAdd);
                }       
            }
            return true;
        }
        catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    private function getRecordsToAdd($editedRecords, $allRecords) {
        $toAdd = array();
        foreach($allRecords as $record) {
            if(!in_array($record[1], $editedRecords)) {
                $toAdd[] = $record;
            }
        }
        return $toAdd;
    }
    
    private function removeRecords($recordsToRemove) {
        try {
            for($i = 0; $i <= count($recordsToRemove) - 1; $i++) {
                $params = array("domain" => $this->domain, "exchange" => $recordsToRemove[$i]['exchange'], "preference" => $recordsToRemove[$i]['preference']);
                HalonDriver::localAPI()->removeMxRecord($this->username, $params);
            }
        }
        catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
