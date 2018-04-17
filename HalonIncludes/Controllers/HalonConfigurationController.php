<?php

class HalonConfigurationController {
    
    public function saveModuleConfiguration($settings) {
        try {
            $settings['customMxRecords'] = serialize($this->parseMxRecords($settings['customMxRecords']));
            $settings['spfHostname'] = $this->parseSpfHostname($settings['spfHostname']);
            $model = new HalonConfigurationModel();
            return $model->saveConfiguration($settings);
        }
        catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    public function getModuleConfiguration($conditions = array()) {
        $model = new HalonConfigurationModel();
        $result = $model->getConfiguration($conditions);
        $configuration  = array();
        foreach($result as $key => $setting) {
            if($setting['name'] == "customMxRecords") {
                $setting['value'] = unserialize($setting['value']);
            }
            $configuration[$setting['name']] = $setting['value'];
        }
        return $configuration;
    }
    
    private function parseSpfHostname($hostname) {
        try {
            if(empty($hostname)){
                return;
            }
            if(preg_match('/[\S]+[\s]+[\S]+/', $hostname)) {
                throw new Exception("SPF Hostname value is incorrect.");
            }
            if(!preg_match('/[\S]+/', $hostname)) {
                throw new Exception("SPF Hostname contains incorrect signs.");
            }
            $hostname = trim($hostname);
            return $hostname;
        }
        catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
     
    private function parseMxRecords($content) { 
        try {
            $mxRecordsArray = explode("\n", $content);
            $parseResult = array();
            foreach($mxRecordsArray as $number => $line) {
                $line = trim($line);
                if(ctype_space($line) === true||$line == "") {
                    continue;
                }
                $splited = preg_split('/[\s]/', $line);
                $parsedLine = $this->adjustValues($splited);
                $parseResult[] = $parsedLine;
            }
            $parseResult = $this->uniqueRecordsNames($parseResult);
            return $parseResult;
        }
        catch(\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }  
    
    private function adjustValues($record) {
        try {
            $record = array_filter($record, function($value) {
                return ($value != ""&&$value != " ");
            });
            $record = array_values($record);
            if(count($record) < 2) {
                throw new Exception("The configuration is incomplete. Each row has to contain priority number and MX record name.");
            }
            if(!preg_match('/^[0-9]+$/', $record[0])) {
                throw new Exception("A priority has to be an integer value.");
            }
            if(count($record) > 2) {
                $record = array_slice($record, 0, 2);
            }
            return $record;
        }
        catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    private function uniqueRecordsNames($records) {
        $uniqueNames = array();
        foreach($records as $record) {
            if(!$this->inArray($record[1], $uniqueNames)) {
                $uniqueNames[] = $record;
            }
        }
        return $uniqueNames;
    }
    
    private function inArray($name, $set) {
        foreach($set as $value) {
            if($value[1] == $name) {
                return true;
            }
        }
        return false;
    }
}
