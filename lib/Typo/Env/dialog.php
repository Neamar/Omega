<?php
$Scenes = explode("\n",$envContent);

$envContent='<div class="dialogue">';

foreach($Scenes as $Scene)
{
	$Scene = explode(':',$Scene);
	
	
	if(count($Scene)==1)
		$envContent .= $Scene[0];//Pas de dialogue.
	elseif(count($Scene)==2)
		$envContent .='<span class="auteur">' . $Scene[0] . '</span>: ' . $Scene[1];//Cas standard
	elseif(count($Scene)>2)
	{//Cas du deux points dans le dialogue.
		$envContent .='<span class="auteur">' . $Scene[0] . '</span>:';
		unset($Scene[0]);
		$envContent .=implode(':',$Scene);
	}
	$envContent .='<br />';
}

$envContent .='</div>';
?>