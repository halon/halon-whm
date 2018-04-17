<?php

function checkacl($acl) {
	$user = $_ENV['REMOTE_USER'];

	if ($user == "root") {
		return 1;
	}

	$reseller = file_get_contents("/var/cpanel/resellers");
	foreach ( split( "\n", $reseller ) as $line ) {
		if ( preg_match( "/^$user:/", $line) ) {
			$line = preg_replace( "/^$user:/", "", $line);
			foreach ( split(",", $line )  as $perm ) {
				if ( $perm == "all" || $perm == $acl ) {
					return 1;
				}
			}
		}
	}
	return 0;
}

error_reporting(E_ALL);
ini_set("display_errors", 1);

if(checkacl('root'))
{
    define("DS",DIRECTORY_SEPARATOR);

    define("Halon_INCLUDES",'/usr/local/cpanel/share/Halon/');

    require_once Halon_INCLUDES.'HalonLoader.php';

    $exSupport = new ExceptionHandler();
    $exSupport->register();
    
    $Main = new HalonMainController('WHM',__DIR__);

    $Main->setAdminArea();
    
    $page   = empty($_REQUEST['page'])?null:$_REQUEST['page'];
    $action = empty($_REQUEST['action'])?null:$_REQUEST['action'];

    echo $Main->getHTMLPage($page,$action,$_REQUEST);
}