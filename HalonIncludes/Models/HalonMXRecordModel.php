<?php

class HalonMXRecordModel {
    
    private $table = "Halon_mxRecords";
    
    public function getDomainsWithCustomMXRecords() {
        $result = MGMySQL::query("SELECT domain FROM `{$this->table}`");
        return $result->fetchAll();
    }
    
    public function saveDefaultRecords($data) {
        $result = MGMySQL::query("INSERT INTO `{$this->table}` VALUES (:domain, :records) ON DUPLICATE KEY UPDATE mx_records = :records",
                array("domain" => $data['domain'], "records" => $data['records']));
    }
    
    public function getDefaultRecords($domain) {
        $result = MGMySQL::select(array("mx_records"), $this->table, array("domain" => $domain))->fetch();
        return $result;
    }
    
    public function removeDefaultRecords($domain) {
        MGMySQL::delete($this->table, array("domain" => $domain));
    }
}
