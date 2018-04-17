<?php

class MGNotification
{ 
    private $email;
    private $subject;
    private $message;
    private $headers = null;
    
    public function userSuccessInstallCertificate($email, $toSearch = array(), $toReplace = array())
    {
        $this->email   = $email; 
        $this->subject = HalonDriver::config()->user_notifications_success_email_subject;
        $this->message = str_replace($toSearch, $toReplace, HalonDriver::config()->user_notifications_success_email_message);
        $this->sendEmail();
    }
    
    public function userFailureInstallCertificate($email, $toSearch = array(), $toReplace = array())
    {
        $this->email   = $email; 
        $this->subject = HalonDriver::config()->user_notifications_failure_email_subject;
        $this->message = str_replace($toSearch, $toReplace, HalonDriver::config()->user_notifications_failure_email_message);
        $this->sendEmail();
    }
    
    public function adminSummary($subject, $message)
    {
        $this->email   = HalonDriver::config()->admin_notifications_email;
        $this->subject = $subject;
        $this->message = $message;
        if(isset($this->email) && !empty($this->email))
        {
            $this->headers  = 'MIME-Version: 1.0' . "\r\n";
            $this->headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
            $this->sendEmail();
        }
    }
    
    private function sendEmail()
    {
        return mail($this->email, $this->subject, $this->message, $this->headers, HalonDriver::config()->notifications_sender);
    }
    
}