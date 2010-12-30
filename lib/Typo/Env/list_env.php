<?php
if(!preg_match("#^\s*\\\\li#",$envContent))
Typo::RaiseError('Un environnement itemize/enumerate doit commencer par une balise \li.');

$envContent=preg_replace("#^\s*\\\\li\s?#",'',$envContent);

$listItems=preg_split("#\n?\s*\\\\li\s*#",$envContent);

if(!isset($Attributs))
	$Attributs='';

$envContent='<' . $ListeType . ' ' . $Attributs . '>' . "\n";

foreach($listItems as $listItem)
{
	if(strpos(substr($listItem,0,-1),"\n")===false)//pas besoin d'avoir des éléments blocks.
		$Item=$listItem;
	else
		$Item=Typo_parseLines($listItem);

	$envContent .= '<li>' . $Item . '</li>' . "\n";
}
$envContent .='</' . $ListeType .'>' . "\n";
?>