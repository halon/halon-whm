<?php 

error_reporting(E_ALL);
ini_set('display_errors', 0);
define('DS', DIRECTORY_SEPARATOR);

require_once '/usr/local/cpanel/php/cpanel.php';
require_once '/usr/local/cpanel/share/Halon/HalonLoader.php';

try {
    $Main = new HalonMainController('WHM', __DIR__);
    $page = empty($_REQUEST['page']) ? null : $_REQUEST['page'];
    $action = empty($_REQUEST['action']) ? null : $_REQUEST['action'];
    echo $Main->getJSONResponse($page, $action, $_REQUEST);
} catch (Exception $ex) {
    echo 'Something went wrong';
}
