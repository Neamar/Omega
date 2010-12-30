<?php
$Classes=array();
if($Env[2]!='')//Les options du tableau.
	$Classes=explode('|',$Env[2]);

$lignes=explode("\\\\\n",$envContent);
$Colonnes=array();
foreach($lignes as $ligne)
{
	$Colonnes[]=explode('&amp;',$ligne);
}

$envContent='
<table>
	<tbody>
';
		foreach($Colonnes as $Colonne)
		{
			if(!(count($Colonne)==1 && $Colonne[0]==''))
			{
				$envContent .='		<tr>' . "\n";
				foreach($Colonne as $ID=>$Cellule)
				{
					if(strpos(substr($Cellule,0,-1),"\n")!==false)//pas besoin d'avoir des éléments blocks.
						$Cellule=Typo_parseLines($Cellule);

					if(isset($Classes[$ID]))
						$Class=' class="' . $Classes[$ID] . '"';
					else
						$Class='';

					$envContent .= '			<td' . $Class . '>' . $Cellule . '</td>' . "\n";
				}
				$envContent .= '		</tr>' . "\n";
			}
		}
$envContent .='	</tbody>
</table>';

unset($lignes,$Colonnes);