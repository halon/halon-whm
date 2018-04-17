<?php

class WHMConfigurationController extends AbstractControler {
    
    function __construct($params,$options) { 
        parent::__construct($params,$options);
    }

    public function MainHTMLAction($input, $data = array()) {

        $configurationController = new HalonConfigurationController();
        $currentConfiguration = $configurationController->getModuleConfiguration();
 
        $form = new FormCreator('configurationForm',$this,array(
            'url'    => $this->getCurrentAction(),
        ));

        $form->addField('TextArea', array(
            'name'  => 'customMxRecords',
            'cols'  => 50,
            'rows'  => 10,
            'enableDescription' => true,
            'description' => "One domain per line",
            'value' => (isset($input['action'])&&$input['action'] == 'save'&&isset($data['error'])&&!empty($data['error']))?$input['configurationForm_customMxRecords']:(isset($currentConfiguration['customMxRecords'])?$this->adjustContent($currentConfiguration['customMxRecords']):"")
        ));
        
        $form->addField('Text', array(
            'name'  => 'spfHostname',
            'value' => (isset($input['action'])&&$input['action'] == 'save'&&isset($data['error'])&&!empty($data['error']))?$input['configurationForm_spfHostname']:(isset($currentConfiguration['spfHostname'])?$currentConfiguration['spfHostname']:"")
        ));
 
        $form->addField('CheckBox', array(
           'name'           => 'enableProtectionForNewDomains'
           ,'value'         => (isset($currentConfiguration['enableProtectionForNewDomains']))?($currentConfiguration['enableProtectionForNewDomains'] == "on"?1:0):0
           ,'frendlyName'   => 'enableProtectionForNewDomains'
        ));
        
       $form->addField('Submit', array(
           'name'           => 'action'
           ,'value'         => 'save'
           ,'frendlyName'   => 'submitSave'
       ));

        $data['configurationForm'] = $form->getHTML();
        
        return array(
            'template'   => 'Configuration',
            'vars'      => $data
        );
    }
    
    private function adjustContent($content) {
        $value = "";
        foreach($content as $line) {
            $value .= implode(" ", $line);
            $value .= "\n";
        }
        return $value;
    }
    
    public function saveHTMLAction($input, $data = array()) {
        if(isset($input['action'])&&$input['action'] == "save") {
            $data = array("customMxRecords" => $input['configurationForm_customMxRecords'], "spfHostname" => $input['configurationForm_spfHostname'],
                "enableProtectionForNewDomains" => isset($input['configurationForm_enableProtectionForNewDomains'])?"on":"off");
            try {
                $configurationController = new HalonConfigurationController();
                $result = $configurationController->saveModuleConfiguration($data);
                $data['success'] = MGLang::T('saveSuccess');
            }
            catch(Exception $e) {
                $data['error'] = $e->getMessage();
            }
        }
        return $this->MainHTMLAction($input, $data);    
    }
}
