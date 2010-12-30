<?php
if($Env[2]=='')//Nombre de colonnes
	Typo::raiseError("Vous devez spécifier le nombre de colonnes. Consultez l'aide de l'environnement column pour plus d'informations");

$envContent ='
<div style="-webkit-column-count: ' . $Env[2] . '; -moz-column-count: ' . $Env[2] . '; -moz-column-gap: 1em; -moz-column-rule:1px solid gray; column-count: ' . $Env[2] . '; column-gap: 1em; column-rule:1px solid gray;">
' . Typo_parseLines($envContent) . '
</div>';