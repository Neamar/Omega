<?php
/*Une classe qui permet de convertir un texte "brut" en texte « typographiquement correct » : paragraphes, ponctuations et autres petites choses.*/
include('Parser.php');

define('P_UNGREEDY',1);
define('ALLOW_SECTIONING',2);
define('ALLOW_FOOTNOTE',3)/*=FOOTNOTE_DEFAULT*/;
	define('FOOTNOTE_DEFAULT','%n');
	define('FOOTNOTE_SCIENCE','(%n)');
define('PARSE_MATH',4);//Utiliser le parseur mathématique
define('RAISE_NO_ERROR',5);//Option pour ne pas afficher les erreurs. Déconseillée dans la majorité des cas !
define('FIX_MISMATCHED_DELIMITERS',6);//Option pour tenter une correction des accolades mal fermées. Déconseillée dans la majorité des cas !
define('DEBUG_MODE',7);
define('GESHI_PATH',substr(__FILE__,0,strrpos(__FILE__,'/')) . '/../../lib/geshi.php');

class Typo
{
	public static $Footnotes;
	//Le nombre de footnotes, permet d'éviter la répétion des mêmes numéros quand plusisuers textes sont parsés sur la même page.
	public static $RecNbFootNote=0;
	// Déclarations des données membres
	 private static $Texte='';
	 public static $Options=array(
		ALLOW_SECTIONING=>true,
		ALLOW_FOOTNOTE=>FOOTNOTE_DEFAULT,
	);

//Définition des balises supportées. Il est extrêmement simple d'ajouter ses propres balises, ou de modifier celles existants, puisque le tableau est en public.
	public static $Balise;


//Caractères spéciaux supportés et reconnus.
	//Ce premier tableau contient les remplacements qui doivent être effectués en interne, il est préférable de ne pas y toucher.
	public static $_SpecialChar;
	//Ce second tableau est modifiable (portée public). Vous aurez peut être besoin d'y ajouter vos propres caractères.
	public static $SpecialChar;

	public static $Ponctuation;


	public static $EscapeString='[@=INTERNAL-%n-%c=@]';
	public static $Escape_And_Prepare=array
	(
	'#\\\\begin\[[a-z]+\]{code}\s([^ù]+)\s\\\\end{code}#isU'=>array//Indique un code source
		(
		'NoBrace'=>true,
		'RegexpCode'=>1,
		'Protect'=>'CODEù',
		),
	'#\\\\verbatim{([^@]+)}#isU'=>array//Verbatim indique un texte qui ne doit pas être modifié. Pas environnement, pour inline. Doit être en premier pour éviter d'autres échappements à l'intérieur.
		(
		'Protect'=>'VERBATIM-INLINE',
		'RegexpCode'=>1,
		),
	'#(^|[^\\\\])(\$([^ù\n\$]+)\$)#iU'=>array//Idem pour les environnements mathématiques. À échapper avant la suite !
		(
		'NoBrace'=>true,
		'RegexpCode'=>2,
		'Protect'=>'MATHù',
		'Replace'=>'<math>%n</math>',
		'Modifications'=>array('$'=>'','&amp;'=>'&'),
		),
	'#\\\\(~|\{|\}|\[|\]|\$|\&|\"|oe|ae|\.|,|;|:|!|\?|\%)#iU'=>array//Certains caractères doivent être échappés : l'accolade par exemple.
		(
		'NoBrace'=>true,
		'RegexpCode'=>0,
		'Protect'=>'ESCAPE-SYMBOL',
		'Modifications'=>array('\\'=>''),
		),

	'#&(\#?[0-9A-Z]+);#iU'=>array//Échapper les entités HTML
		(
		'NoBrace'=>true,
		'RegexpCode'=>0,
		'Protect'=>'HTML-ENTITY',
		),
	'#\\\\(lien|link|l)\[([^\[]+)\]{(.+)}#iU'=>array//Les liens ne doivent pas être parsés, sinon http://neamar.fr/i.php?test devient http : //neamar. fr /i. php&nbsp;? test
		(
		'RegexpCode'=>2,
		'Protect'=>'LINK',
		'Modifications'=>array('&'=>'&amp;'),
		),
	'#\\\\(label)?image\[(.+)\]{([^\[]+)}#iU'=>array//Idem pour les images
		(
		'RegexpCode'=>3,
		'Protect'=>'IMAGE',
		'Modifications'=>array('&'=>'&amp;'),
		),
	'#\\\\begin{verbatim}\s(.[^@].+)\s\\\\end{verbatim}#isU'=>array//Verbatim indique un texte qui ne doit pas être modifié. Environnement.
		(
		'NoBrace'=>true,
		'RegexpCode'=>1,
		'Protect'=>'VERBATIM',
		),
	);

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////// GESTION DES OPTIONS AVANT LE PARSAGE DU TEXTE
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//Enregistrer un texte. LE texte pourra ensuite être rendu avec Parse() ou afficher dans un IDE pour modification.
	 public static function setTexte($TexteToParse)
	 {
			if(is_string($TexteToParse))
			{
				if(isset(self::$Options[FIX_MISMATCHED_DELIMITERS]))
				{//Tenter une reformation des balises.
					while(substr_count($TexteToParse,'{')>substr_count($TexteToParse,'}'))
						$TexteToParse .= '}';
				}
				self::$Texte=$TexteToParse;
			}
			else
				self::RaiseError('Le texte doit être un string.');
	 }

	//Charger un texte depuis un fichier.
	 public static function setTexteFromFile($file)
	 {
		if(file_exists($file))
			self::setTexte(file_get_contents($file));
		else
			self::RaiseError('Le fichier <em>' . $file . '</em> n\'existe pas. ');
	 }

	//Changer les options de parsage.
	 public static function setOptions()
	 {
		 self::$Options=array();
		 $Opt = func_get_args();
		 for ($i = 0; $i < func_num_args(); $i++)
			self::$Options[$Opt[$i]]=true;
	 }

	//Ajouter une unique option
	public static function addOption($Setting,$Value=true)
	{
		self::$Options[$Setting]=$Value;
	}

	//Supprimer une unique option
	public static function removeOption($Setting)
	{
		unset(self::$Options[$Setting]);
	}

	//Ajouter une balise
	public static function addBalise($Regle,$Rempl)
	{
		self::$Balise[$Regle]=$Rempl;//On peut aussi modifier directement l'array, qui est en public.
	}

	//Changer la langue de rendu.
	public static function switchLanguage($LNG)
	{
		$File=substr(__FILE__,0,strrpos(__FILE__,'/')) . '/Lng/' . $LNG . '.php';
		if(is_file($File))
			include($File);
		else
			Typo::RaiseError('Impossible de charger la langue <strong>' . $LNG . '</strong>. Le fichier associé n\'existe pas.');
	}

	//La fonction la plus utile, le parsage du texte et son renvoi.
	public static function Parse($ShowWarning=false)
	{
		if(self::$Texte!='')
			return Typo_Parser(self::$Texte);
		else
			self::RaiseError('Aucun texte chargé.');
	}

	//Parsage linéaire, c-à-d que toutes les balises HTML sons supprimées. Seul reste le texte et les entités HTML.
	public static function parseLinear($ShowWarning=false)
	{//Retourne le texte sans les balises entourantes.
		return str_replace("\n",'',preg_replace('#<([A-Z][A-Z0-9]*)\b[^>]*>(.*)</\1>#Ui','$2',self::Parse()));
	}

	//Créer un IDE pour la modification du texte.
	public static function renderIDE($Param=array())
	{
		include_once('IDE.php');
		renderIDE(Typo::$Texte,$Param);
	}




///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////// FONCTIONS STATIQUES UTILISÉES LORS DE LA GÉNÉRATION DU TEXTE
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//Gestionnaire de note de bas de page
	public static function FootNote_handler($Partie)
	{
		//Si deux footnotes ont le même texte, le numéro reste le même.
		if(!in_array($Partie[1],Typo::$Footnotes))
		{
			Typo::$Footnotes[]=$Partie[1];
			$NbFootNote=count(Typo::$Footnotes)+self::$RecNbFootNote;
			$Identifier='';
		}
		else
		{
			$NbFootNote=array_search($Partie[1],Typo::$Footnotes) + 1 + self::$RecNbFootNote;
			$Identifier='-' . floor(rand(0,1000));
		}

		$Caption=str_replace('%n',$NbFootNote,Typo::$Options[ALLOW_FOOTNOTE]);//Récupérer le style d'affichage du footnote.
		$Partie[1]=str_replace('"','\'',preg_replace('#\<([^\<]+)\>#','',$Partie[1]));
		$Partie[1]=preg_replace('#\[@=INTERNAL-(.+)-(.+)\]#iU','[cliquez pour voir]',$Partie[1]);
		return '<sup><a class="footnote" id="Note-' . $NbFootNote . $Identifier . '" href="#Ref-' . $NbFootNote . '" title="' . str_replace(array("\n",'\\'),'',$Partie[1]) .'">' . $Caption . '</a></sup>';
	}

	//Gestionnaire des chiffres.
	public static function FormaterNombres($Nombre)
	{//[1] : avant le chiffre, [2] : le chiffre avec les espaces éventuels.
		if(strpos($Nombre[2],' ')===false && $Nombre[2]<2500 && $Nombre[1]!=','  && $Nombre[1]!='.')//Ne pas mettre en forme les dates
			return $Nombre[0];
		else
		{
 			//On ne peut pas passer par number_format qui formate les leading zeros.
			$NombreAFormater=str_replace(' ','',$Nombre[2]); $NombreFormate=''; $j=0;

			//S'il s'agit d ela partie décimale d'un chiffre, la règle pour l'ajout d'espace est inversée.
			if($Nombre[1]==',')
				$NombreAFormater=strrev($NombreAFormater);

			//Boucle à l'envers (il faut commencer par la fin pour placer correctement les espaces)
			for($i=strlen($NombreAFormater)-1;$i>=0;$i--)
			{
				$j++;
				$NombreFormate .= $NombreAFormater[$i];
				if($j % 3 == 0 && $i!=0)
					$NombreFormate .='@';//On doit passer par un caractère unique car on ne sait pas si on appliquera un strrev à la fin.
			}

			if($Nombre[1]!=',')
				$NombreFormate=strrev($NombreFormate);

			return $Nombre[1] . str_replace('@','&nbsp;',$NombreFormate);
		}
	}


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////// FONCTIONS TECHNIQUES UTILISÉES POUR L'INTERFAÇAGE PHP
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//Déclencher une erreur : balise inconnue, impossible de charger un environnement...
	public static function RaiseError($e)
	{
		if(!isset(self::$Options[RAISE_NO_ERROR]))
			echo('<p style="border:1px solid red;"><strong>ERREUR dans la classe <em>Typo</em></strong> : ' . $e . '</p>');
	}

	public static function preg_match_wb($Regexp,$Texte,&$Resultat)
	{
		if(preg_match($Regexp,$Texte,$Resultat))//Effectuer la requête initiale.
		{
			foreach($Resultat as $n=>$Match)
			{//Pour chaque parenthèse capturante
				if($n>0 && strpos($Match,'{')!==false)
				{//On a une accolade ouvrante présente : il va falloir mettre les mains dans le cambouis
					$InitialMatch=$Match;
					$Offset=strpos($Texte,$Resultat[0]);
					$Offset=strpos($Texte,$Match,$Offset);//Cette fois, on a positionné le curseur au bon endroit : on est prêt à chercher.
					$Depart=$Offset;
					$Taille=strlen($Texte);
					$NestingLevel=0;
					while($NestingLevel>=0 && $Offset<$Taille-1)
					{//Parcourir la chaine à la recherche d'accolades, jusqu'à ce qu'on trouve l'accolade fermante correspondante.
						$Offset++;
						if($Texte[$Offset]=='{')
							$NestingLevel++;
						elseif($Texte[$Offset]=='}')
							$NestingLevel--;
					}
					$Resultat[$n]=substr($Texte,$Depart,$Offset-$Depart);
					$Resultat[0]=str_replace($InitialMatch,$Match,$Resultat[0]);
				}
			}
			return true;
		}
		else
			return false;
	}

	//Les commentaires des cette section sont en anglais car j'ai rédigé un article sur cette section : http://neamar.fr/Res/RegexpWithBraces/
	public static function preg_replace_wb($pattern,$replacement,&$subject)
	{
		preg_match_all($pattern,$subject,$MatchingString,PREG_SET_ORDER);//$MatchingString now hold all strings matching $pattern.

		$SVGReplacement=$replacement;
		foreach($MatchingString as $Result)
		{//For each result :
			$replacement=$SVGReplacement;
			foreach($Result as $n=>$Match)
			{//And for each capturing parenthesis
				if($n>0 && strpos($Match,'{')!==false)
				{//There is at least one brace in our string, we'll need to improve the regexp.
					$InitialMatch=$Match;
					$Offset=strpos($subject,$Match,strpos($subject,$Result[0])) + 1;//Switch the cursor to the beginning of our string in the whole $subject.
					$Start=$Offset - 1;//$Offset start right after the brace : for \i{something}, $Offset is on the "s" and $Start on the "{".
					$Size=strlen($subject)-1;//We need to compute it every time cause every match may change the size of our string.
					$NestingLevel=0;//How deep are we ?

					while($NestingLevel>=0)
					{//Browse the string, looking for braces...
						$Open=strpos($subject,'{',$Offset);//Find the next opening brace
						$Close=strpos($subject,'}',$Offset);//Find the next closing
						if($Close!==false && ($Open===false || $Close<$Open))//Closest brace is a closing one.
						{
							$NestingLevel--;
							$Offset=$Close+1;//Move the cursor to it's new position
						}
						elseif($Open!==false && ($Close===false || $Open<$Close))//Closest brace is an opening one
						{
							$NestingLevel++;
							$Offset=$Open+1;//Move the cursor to it's new position
						}
						elseif($Open===false && $Close==false)
							break;//Uh oh... something is wrong !
					}
					$Offset--;

					if($NestingLevel>=0)
					{
						Typo::RaiseError("Oh oh ! On dirait bien qu'il manque des accolades dans votre texte.");
					}

					$Match=substr($subject,$Start,$Offset-$Start);//Replace the regexp Match with the real Match we just computed.
					$Result[0]=str_replace($InitialMatch,$Match,$Result[0]);//Change the whole match to reflect our new choice.
				}
				$replacement=str_replace('$' . $n,$Match,$replacement);//Compute $replacement string.
			}
			$subject=str_replace($Result[0],$replacement,$subject);//Replace in subject with computed $replacement.
		}
		return $subject;
	}
}


//Charger les options par défaut
Typo::switchLanguage('fr');
?>
