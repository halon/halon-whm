<?php

class HalonConfigurationModel {
    
    private $table = "Halon_configuration";
    
    public function saveConfiguration($values) {
        try {
            foreach($values as $name => $value) {
                MGMySQL::query("INSERT INTO `{$this->table}` (name, value) VALUES (:name, :value) ON DUPLICATE KEY UPDATE value = :value", 
                    array(':name' => $name, ':value' => $value));
            }
        }
        catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    public function getConfiguration($conditions) {
        return MGMySQL::select(array("name", "value"), "{$this->table}", $conditions)->fetchAll();
    } 
}
