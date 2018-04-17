<?php
if(!defined("DS")) {
    define('DS',DIRECTORY_SEPARATOR);
}

function HalonLoader($class){
    $searchDirs = array(
        'Classes',
        'Classes'.DS.'Form',
        'Classes'.DS.'Exceptions', 
        "Controllers",
        'Models',
        'Drivers'
    ); 

    if(preg_match("/^([A-Z]{1,3}[a-z]{0,10})([A-Z][a-zA-Z]+)Driver$/D", $class,$results))
    {
        if($results[1].$results[2] == 'Halon')
        {
            $driverFile = __DIR__.DS.'Drivers'.DS.$class.'.php';

            if(!file_exists($driverFile))
            {
                throw new Exception("Unable to find File for Driver: ".$class);
            }

            require_once $driverFile;
        }
        else
        {
            $driverFile = __DIR__.DS.'Drivers'.DS.$results[1].DS.$class.'.php';

            if(!file_exists($driverFile))
            {
                throw new Exception("Unable to find File for Driver: ".$class);
            }

            require_once $driverFile;
        }
    }
    else
    {
        $found = false;
        foreach($searchDirs as $dir)
        {
            $classFile = __DIR__.DS.$dir.DS.$class.'.php';
            if(file_exists($classFile))
            {
                require_once $classFile;
                $found = true;
                break;
            }
        }
        
        if(!$found)
        {
            throw new SystemException("Unable to find File for class: ".$class);
        }
    }
}

spl_autoload_register('HalonLoader');

require_once __DIR__.DS.'HalonMainController.php';