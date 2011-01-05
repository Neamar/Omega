<?php
$envContent=str_replace("	",'&nbsp;&nbsp;&nbsp;&nbsp;',Typo_parseLines($envContent));
$envContent=str_replace('<p>','<p style="text-indent:0;">',$envContent);
?>