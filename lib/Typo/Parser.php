<?php

//Le parsage du texte s'effectue  en 4 étapes
function Typo_Parser($Texte)
{
	// 1 : Passer le texte au pré-parser pour effectuer les REPLACE
	$Texte=Typo_MOC($Texte);
	// 2 : Échapper ce qui doit l'être : liens, mathématiques, entités HTML...
	$Texte=Typo_Escape($Texte);


	//Si mode debug activé :
	if(isset(TYPO::$Options[DEBUG_MODE]))
		return $Texte;

	// 3 : Parser le texte.
	$Texte=Typo_Parse($Texte);
	// 4 : Déséchapper pour pouvoir renvoyer le texte.
	$Texte=Typo_unEscape($Texte);

	return $Texte;
}

function Typo_Escape(&$texte)
{
	//Protéger d'une attaque XSS.
	$texte=str_replace(array('<','>'),array('&lt;','&gt;'),$texte);

	//Protection des champs à échapper.
	foreach(Typo::$Escape_And_Prepare as $Regexp=>&$Escaping)
	{
		$Escaping['_Occurrences']=array();
		$Escaping['_KeyWord']=str_replace('%n',$Escaping['Protect'],Typo::$EscapeString);
		if(!isset($Escaping['NoBrace']))
		{
			while(Typo::preg_match_wb($Regexp,$texte,$Part))
			{
				$Escaping['_Occurrences'][]=$Part[$Escaping['RegexpCode']];
				$texte=str_replace($Part[$Escaping['RegexpCode']],str_replace('%c',count($Escaping['_Occurrences']),$Escaping['_KeyWord']),$texte);
			}
		}
		else
		{
			while(preg_match($Regexp,$texte,$Part))
			{
				$Escaping['_Occurrences'][]=$Part[$Escaping['RegexpCode']];
				$texte=str_replace($Part[$Escaping['RegexpCode']],str_replace('%c',count($Escaping['_Occurrences']),$Escaping['_KeyWord']),$texte);
			}
		}
	}

	return $texte;
}

function Typo_unEscape(&$texte)
{
	//Déséchapper ce qui doit l'être.
	foreach(Typo::$Escape_And_Prepare as $Regexp=>&$Escaping)
	{
		foreach($Escaping['_Occurrences'] as $ID=>$Occurrence)
		{
			if(isset($Escaping['Modifications']))
				$Occurrence=str_replace(array_keys($Escaping['Modifications']),array_values($Escaping['Modifications']),$Occurrence);
			if(isset($Escaping['Replace']))
				$Occurrence=str_replace('%n',$Occurrence,$Escaping['Replace']);
			$texte=str_replace(str_replace('%c',$ID+1,$Escaping['_KeyWord']),$Occurrence,$texte);
		}
	}

	return $texte;
}

function Typo_MOC(&$texte)
{//Meta Object Compiler.
//Processe le texte pour effectuer les modifications nécessaires.
//Syntaxe : {{NOM|Options|Texte}}
preg_match_all('#{{\[REPLACE\|(.+)\|(.+)\]}}#',$texte,$REPLACE,PREG_SET_ORDER);
foreach($REPLACE as $MocExpr)
{
	//Supprimer la MOC :
	$texte=preg_replace("#\n?" . preg_quote($MocExpr[0]) . "\n?#",'',$texte);
	//Puis effectuer les remplacements
	$texte=str_replace($MocExpr[1],$MocExpr[2],$texte);
}

preg_match_all('#{{\[REG-REPLACE\|(.+)\|(.+)\]}}#',$texte,$REPLACE,PREG_SET_ORDER);
foreach($REPLACE as $MocExpr)
{
	//Supprimer la MOC (en échappant le délimiteur):
	$texte=preg_replace("#\n?" . preg_quote($MocExpr[0],'#') . "\n?#",'',$texte);
	//Puis effectuer les remplacements selon l'expression régulière.
	$texte=preg_replace($MocExpr[1],$MocExpr[2],$texte);

}

return $texte;
}

function Typo_Parse($texte)
{

	Typo::$Footnotes=array();



//Ponctuez-moi tout ça !
	$texte=preg_replace(array_keys(Typo::$Ponctuation),array_values(Typo::$Ponctuation),$texte);



//Caractères spéciaux
	$SpecialChar=array_merge(Typo::$SpecialChar,Typo::$_SpecialChar);//Créer le tableau à partir des deux tableaux : le premier contient des données qui peuvent avoir été modifiées par l'utilisateur, le second contient les remplacement inhérents au texte HTML.
	$texte=str_replace(array_keys($SpecialChar),array_values($SpecialChar),$texte);





//Footnote : préparer dès maintenant.
	if(isset(Typo::$Options[ALLOW_FOOTNOTE]))
		$texte=Typo::preg_replace_wb('#\\\\footnote{(.+)}#isU','@[FN:$1' . "\n" . ']@',$texte);







//Balises de formatage inline
	foreach(Typo::$Balise as $Regexp=>$Remplacement)
		$texte=Typo::preg_replace_wb($Regexp,$Remplacement,$texte);


	//Mettre en forme les chiffres
	$texte=preg_replace_callback('#([^&0-9\#])([0-9][0-9 ]{2,}[0-9])#',"Typo::FormaterNombres",$texte);


	if(isset(Typo::$Options[ALLOW_FOOTNOTE]))
		$texte=preg_replace_callback("#@\[FN:(.+)\]@#sU",'Typo::FootNote_handler',$texte);
















//////////
//Début du parsage "important"
//////////
	$texte=Typo_parseLines($texte);








//Terminer la gestion des footnotes


	if(count(Typo::$Footnotes)!=0)
	{
		$texte .='<hr class="footnote court" />' . "\n";
		$texte .='<ol>';
		foreach(Typo::$Footnotes as $Nb=>$Note)
		{
			$Nb=$Nb+1+Typo::$RecNbFootNote;

			$NoteMEF=Typo_parseLines($Note);
			$NoteMEF=preg_replace('#^<p>(.+)<\/p>\s?$#','$1',$NoteMEF);

			$texte .='	<li><a class="footnote" id="Ref-' . $Nb . '" href="#Note-' . $Nb . '"><sup>' . str_replace('%n',$Nb,Typo::$Options[ALLOW_FOOTNOTE]) . '</sup> <small>&uarr;</small></a> ' . $NoteMEF . '</li>' . "\n";
		}
		$texte .='</ol>';

		Typo::$RecNbFootNote +=count(Typo::$Footnotes);
	}








//C'est fini, le texte est prêt à être renvoyé.
	return $texte;
	}



function Typo_parseLines($texte)
{
	//Mise en paragraphe
	$arrTexte=explode("\n",str_replace("\r",'',$texte));//Passer en mode "End Of Line \n", et couper le texte à chaque saut de ligne.
	$NbLignes=count($arrTexte);
	$texte='';
	$parOpen=false;//Détermine si un paragraphe est ouvert.
	$listeOpen=false;
	$envOpen=false;

	for($i=0;$i<$NbLignes;$i++)
	{
		$Ligne=$arrTexte[$i];
		$envOpen=false;

//Environnements
		if(preg_match('#^\\\\begin(\[(.+)\])?{([A-Z]+)}#iU',$Ligne,$Env))
		{
			if($parOpen)
			{
				FermeParagraphe($texte);
				$parOpen=false;
			}
			if($listeOpen)
			{
				$texte .='</ul>' . "\n";;//Fermer la dernière liste.
				$listeOpen=false;
			}
			$envContent='';
			$arrTexte[$i]=str_replace($Env[0],'',$arrTexte[$i]);
			while(!preg_match('#^\s*\\\\end{' .$Env[3] . '}#',$arrTexte[$i]))
			{
				$envContent .= $arrTexte[$i] . "\n";
				$i++;
				if($i>=$NbLignes)
				{
					Typo::RaiseError('Impossible de trouver la fin de l\'environnement "' . $Env[3] . '"');
					break;
				}
			}
			if(substr($envContent,0,1)=="\n")
				$envContent=substr($envContent,1);

			$File= substr(__FILE__,0,strrpos(__FILE__,'/')) . '/Env/' . $Env[3] . '.php';
			if(is_file($File))
				include($File);
			else
				Typo::RaiseError('Utilisation d\'un environnement inconnu ("' . $Env[3] . '").');
			$texte .= "\n" . $envContent . "\n";
			$envOpen=true;
		}
//Éléments de liste
		if(preg_match('#^\s*\\\\item\s(.+)$#i',$Ligne,$ListItem))
		{//Item de liste
			if(!$listeOpen)
			{
				if($parOpen)
					FermeParagraphe($texte);
				$parOpen=false;
				$listeOpen=true;
				$texte .='<ul>' . "\n";
			}
			$texte .='	<li>' . $ListItem[1] . '</li>' . "\n";
			$Ligne='';
		}
		elseif($listeOpen)
		{//Pas d'item détécté, mais que l'on est quand même en environnement liste :
			$listeOpen=false;
			$texte .='</ul>' . "\n";
		}

//Paragraphes standards
		if($listeOpen || $envOpen)
		{/*Ne rien faire si on est en mode liste : la ligne a déjà été traitée*/}
		elseif(preg_match('#^(~+)(.*)$#',$Ligne,$Coupure))
		{//~~~~~ indique un trait horizontal, une rupture dans le rythme.
			if($parOpen)
				FermeParagraphe($texte);
			if($Coupure[1]=='~')
				$texte .='<hr class="court" />';
			else
				$texte .='<hr />';
			$parOpen=false;
			if($Coupure[2]!='')//Il y a du texte après, le réinjecter.
			{
				$arrTexte[$i]=$Coupure[2];
				$i--;
			}
		}
		elseif(preg_match('#^\s*\$(.+)\$\s*$#',$Ligne,$Formule))//Ne tester qu'un seul $ car le premier $ aura été remplacé lors de l'échappement
		{
			if($parOpen)
				FermeParagraphe($texte);
			$texte .="\n" . '<p class="displaymath">' . $Formule[1] . '</p>' . "\n";
			$parOpen=false;
		}
		elseif(isset(Typo::$Options[ALLOW_SECTIONING]) && Typo::preg_match_wb("#\\\\(sub|subsub)?section{(.+)}#sU",$Ligne,$titre))
		{
			if($parOpen)
				FermeParagraphe($texte);

			if($titre[1]=='')
				$texte .='<h2>' . $titre[2] . '</h2>';
			elseif($titre[1]=='sub')
				$texte .='<h3>' . $titre[2] . '</h3>';
			elseif($titre[1]=='subsub')
				$texte .='<h4>' . $titre[2] . '</h4>';
			$texte .= "\n";
			$parOpen=false;
		}
		else
		{
			if($Ligne!='' && !$parOpen)
			{
				$texte .='<p>';
				$parOpen=true;
			}
			elseif($Ligne=='' && $parOpen && !isset(Typo::$Options[P_UNGREEDY]))
			{
				FermeParagraphe($texte);
				$parOpen=false;
			}

			if($parOpen)
				$texte .=$Ligne . '<br />' . "\n";
		}
	}
	//////////
	//Fin du parsage
	//////////
	if($parOpen)
		FermeParagraphe($texte);//Fermer le dernier paragraphe.
	if($listeOpen)
		$texte .='</ul>' . "\n";;//Fermer la dernière liste.

	if(preg_match('#\\\\(.+)(\\[|{)#',$texte,$Match))
		Typo::RaiseError('Attention, une balise inconnue (ou mal utilisée) a été trouvée : <strong>' . $Match[0] . '</strong>.');
	return $texte;
}

function FermeParagraphe(&$texte)
{
	if(substr($texte,strlen($texte)-7,6)=='<br />');
		$texte=substr($texte,0,strlen($texte)-7);

	if(substr($texte,strlen($texte)-4,3)!='<p>')
		$texte .= '</p>' . "\n\n";
}
?>