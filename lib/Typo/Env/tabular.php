<?php
$Classes=array();
if($Env[2]!='')//Les options du tableau.
	$Classes=explode('|',$Env[2]);

$Caption='';
$Head=array();
$Foot=array();
$Body=array();

$StructureActuelle=&$Body;

$Lignes=explode("\n",$envContent);
$LigneActuelle='';
foreach($Lignes as $ligne)
{
	if(preg_match('#^\s*\\\\caption (.+)$#',$ligne,$match))
	{//Titre du tableau
		$Caption=$match[1];
	}
	elseif(preg_match('#^\s*\\\\(head|body|foot)$#',$ligne,$match))
	{
		//Si il y a des restes en mémoire les sauver
		if($LigneActuelle!='')
		{
			$StructureActuelle[] = explode('&amp;',$LigneActuelle);
			$LigneActuelle='';
		}

		if($match[1]=='head')
			$StructureActuelle=&$Head;
		elseif($match[1]=='body')
			$StructureActuelle=&$Body;
		elseif($match[1]=='foot')
			$StructureActuelle=&$Foot;
	}
	elseif(preg_match('#(.+)\s?\\\\\\\\$#',$ligne,$match))
	{
		$LigneActuelle .=$match[1];
		$StructureActuelle[] = explode('&amp;',$LigneActuelle);
		$LigneActuelle='';
	}
	elseif($ligne!='')
		//Ligne de tableau étalée sur plusieurs lignes de code
		$LigneActuelle .=$ligne . "\n";
}
//La dernière ligne
if($LigneActuelle !='')
	$StructureActuelle[] = explode('&amp;',$LigneActuelle);

unset($Lignes);

// print_r($Head);
// echo '<br />';
//  print_r($Body);
// echo '<br />';
// print_r($Foot);

$Table=array('head'=>&$Head,'foot'=>&$Foot,'body'=>&$Body);
$CellType=array('head'=>'th','foot'=>'th','body'=>'td');

$envContent='<table>' . "\n";
if($Caption!='')
	$envContent .='	<caption>' . $Caption . '</caption>' . "\n";

foreach($Table as $Categorie=>$Data)
{
	if(count($Data)!=0)
	{
		$envContent .= '	<t' . $Categorie . '>' . "\n";
		foreach($Data as $Row)
		{
			$envContent .= '		<tr>' . "\n";
			$ID=0;
			foreach($Row as $Column)
			{
				if(isset($Classes[$ID]))
					$Class=' class="' . $Classes[$ID] . '"';
				else
					$Class='';
				$ID++;

				$Column = trim($Column);
				if(strpos(substr($Column,0,-1),"\n")!==false)//pas besoin d'avoir des éléments blocks.
					$Column=Typo_parseLines($Column);

				$envContent .= '			<' . $CellType[$Categorie] . $Class . '>' . $Column . '</' . $CellType[$Categorie] . '>' . "\n";
			}
			$envContent .= '		</tr>' . "\n";
		}
		$envContent .= '	</t' . $Categorie . '>' . "\n";
	}
}
$envContent.='</table>' . "\n";