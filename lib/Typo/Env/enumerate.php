<?php
$ListeType='ol';
if(is_numeric($Env[2]))
	$Attributs='start="' . $Env[2] . '"';
else
	unset($Attributs);
include(substr(__FILE__,0,strrpos(__FILE__,'/')) . '/list_env.php');
?>