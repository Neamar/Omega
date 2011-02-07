<?php
$envContent='<blockquote> ' . str_replace("\n\n",'',Typo_parseLines($envContent)) . '</blockquote>';
if($Env[2]!='')
	$envContent .= '<div class="auteur">&mdash; ' . $Env[2] . '</div>';
?>