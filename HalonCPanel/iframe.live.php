<?php
require_once '/usr/local/cpanel/php/cpanel.php';
$cpanel = new CPANEL();
print $cpanel->header('Halon');
?>
<iframe src="index.live.php" style="width: 100%; height: calc(100vh - 280px);"></iframe>
<?php
print $cpanel->footer();
$cpanel->end();
?>
