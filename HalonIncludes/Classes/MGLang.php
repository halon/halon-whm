<?php

class MGLang
{
   private static $instance;
   private $dir;
   private $langs = array();
   private $currentLang;

   private $fillLangFile = false;
   private $context = array();
   private $staggedContext = array();
   
   private function __construct() {} 
   private function __clone(){}
 
   
   public static function getInstance ($dir = null, $lang = null)
   {
        if (self::$instance === null) 
        {
            self::$instance = new self();
            self::$instance->dir = $dir;
            self::$instance->loadLang();
            if($lang)
            {
                self::$instance->loadLang($lang);
            }
        }
        return self::$instance;
   }
   
   public static function getAvaiable()
   {
       $langArray = array();
       $handle = opendir(self::$instance->dir);

       while (false !== ($entry = readdir($handle))) {
            list($lang,$ext) = explode('.',$entry);
            if($lang && isset($ext) && strtolower($ext) == 'php')
            {
                $langArray[] = $lang;
            }
       }
       
       return $langArray;
   }

   public static function loadLang($lang = 'english')
   {
       if($lang)
       {
           $file = self::getInstance()->dir.DS.$lang.'.php';
           if(file_exists($file))
           {
                include $file;
                self::getInstance()->langs = array_merge(self::getInstance()->langs,$_LANG);
                self::getInstance()->currentLang = $lang;
           }
       }
   }
   
   public static function setContext(){
       self::getInstance()->context = array();
       foreach(func_get_args() as $name)
       {
           self::getInstance()->context[] = $name;
       }
   }
   
   public static function addToContext(){
       foreach(func_get_args() as $name)
       {
           self::getInstance()->context[] = $name;
       }
   }
   
   public static function stagCurrentContext($stagName){
        self::getInstance()->staggedContext[$stagName] = self::getInstance()->context;
   }
   
   public static function unstagContext($stagName){
       if(isset(self::getInstance()->staggedContext[$stagName]))
       {
           self::getInstance()->context = self::getInstance()->staggedContext[$stagName];
           unset(self::getInstance()->staggedContext[$stagName]);
       }
   }

   public static function T()
   {
       $lang = self::getInstance()->langs;

       $history = array();

       foreach(self::getInstance()->context as $name)
       {
           if(isset($lang[$name]))
           {
               $lang = $lang[$name];
           }
           $history[] = $name;
       }
       
       $returnLangArray = false;
       
       foreach(func_get_args() as $find)
       {
           $history[] = $find;
           if(isset($lang[$find]))
           {
               if(is_array($lang[$find]))
               {
                   $lang = $lang[$find];
               }
               else
               {
                   return htmlentities($lang[$find]);
               }
           }
           else
           {
               
               if(self::getInstance()->fillLangFile)
               {
                   $returnLangArray = true;
               }
               else
               {
                    return htmlentities($find);
               }
           }
       }
       
       
       if($returnLangArray)
       {
           return '$'."_LANG['".implode("']['", $history)."']";
       }
       
       return htmlentities($lang);
   }
   
   public static function absoluteT(){
       $lang = self::getInstance()->langs;
       
       $returnLangArray = false;
       
       foreach(func_get_args() as $find)
       {
           $history[] = $find;
           if(isset($lang[$find]))
           {
               if(is_array($lang[$find]))
               {
                   $lang = $lang[$find];
               }
               else
               {
                   return htmlentities($lang[$find]);
               }
           }
           else
           {
               
               if(self::getInstance()->fillLangFile)
               {
                   $returnLangArray = true;
               }
               else
               {
                    return htmlentities($find);
               }
           }
       }
       
       
       if($returnLangArray)
       {
           return '$'."_LANG['".implode("']['", $history)."']";
       }
       
       return htmlentities($lang);
   }
}