<?php
/**
 * Doc.php - 26 oct. 2010
 *
 * Offrir des primitives de haut niveau pour la gestion des liens de documentations
 *
 * PHP Version 5
 *
 * @category  ViewHelper
 * @package   Root
 * @author    Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @copyright 2010 Matthieu Bacconnier
 * @license   Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link      http://edevoir.com
 */

/**
 * Génère un lien vers l'élément de documentation demandé.
 * Si le titre n'est pas fourni, il est automatiquement récupéré.
 *
 * @param array $items la liste à créer
 * @param string $type ul ou ol.
 *
 * @return string le code HTML demandé.
 */
function ViewHelper_Html_list(array $Items, $Type='ul', $Id = '')
{
	if(count($Items)==0)
	{
		return '';
	}

	$R = '<' . $Type . ($Id==''?'':' id="' . $Id . '"') . ">\n";
	foreach($Items as $Item)
	{
		$R .= '	<li>' . $Item . "</li>\n";
	}
	$R .= '</' . $Type . ">\n";

	return $R;
}

/**
 * Génère une liste avec les items spécifiés transformés en URL
 *
 * @param array $Items la liste à créer. Les clés représentent l'url, les valeurs le texte du lien.
 * @param string $Type ul ou ol.
 * @param string $BaseURL l'URL de base à utiliser
 *
 * @return string le code HTML demandé.
 */
function ViewHelper_Html_listAnchor(array $Items, $Type='ul', $BaseURL = '')
{
	if(count($Items)==0)
	{
		return '';
	}

	$R = '<' . $Type . ">\n";
	foreach($Items as $URL => $Item)
	{
		$R .= '	<li><a href="' . $BaseURL . $URL . '">' . $Item . "</a></li>\n";
	}
	$R .= '</' . $Type . ">\n";

	return $R;
}

/**
 * Génère une liste d'action :
 * Lien
 * 	Description du lien...
 * 	[Lien vers de la documentation]
 *
 * @param array $Actions la liste des actions. La clé repréente la partie dynamique de l'URL, la valeur est soit un string (le texte du lien), soit un tableau pouvant contneir jusqu'à trois éléments : le texte du lien, la description du lien, et un lien vers une page d'aide.
 * @param string $BaseURL l'url de base. La partie __URL__ sera dynamiquement remplacée par la clé du tableau.
 * @param string $BaseDoc le module de base pour les liens documentation.
 *
 * @return string le code HTML demandé.
 */
function ViewHelper_Html_listAction(array $Actions, $BaseURL, $BaseDoc)
{
	static $NbActions = 1;

	//Le tableau pour la liste déroulante rapide
	$Quickjump = array('' => 'Choisissez une action...');
	foreach($Actions as $URL => &$Action)
	{
		$URL = str_replace('__URL__', $URL, $BaseURL);

		if(is_array($Action))
		{
			$Quickjump[$URL] = $Action[0];

			$Texte = '<a href="' . $URL . '">' . $Action[0] . '</a><br />
	<p class="action-detail">' . $Action[1];
			if(isset($Action[2]))
			{
				if(!function_exists('ViewHelper_Doc_anchor'))
				{
					include OO2FS::viewHelperPath('Doc');
				}
				$Texte .= '<br />' . ViewHelper_Doc_anchor($BaseDoc, $Action[2]);
			}

			$Action = $Texte . '</p>';


		}
		else
		{
			$Quickjump[$URL] = $Action;

			$Action = '<a href="' . $URL . '">' . $Action . '</a>';
		}
	}

	if(!function_exists('ViewHelper_Form_selectLabelBr'))
	{
		include OO2FS::viewHelperPath('Form');
	}

	$Quickjump = ViewHelper_Form_selectLabelBr(
		'quickjump',
		'Liens rapides',
		$Quickjump,
		null,
		array(
			'id' => 'quickjump-' . $NbActions++,
			'class' => 'quickjump'
		)
	);
	
	$R = '<div class="list-actions">
	<form method="get" action="">
	' . $Quickjump . '
	</form>
	' . ViewHelper_Html_list($Actions) . '
	</div>';

	return $R;
}

/**
 * Génère un tableau dynamique AJAX.
 *
 * @param string $URL l'URL renvoyant les ressources en JSON
 * @param string $Titre le titre du tableau
 * @param array $Colonnes les colonnes constituant le tableau
 * @param string $JSCallback la fonction javascript de callback à utiliser. Aucune si non définie.
 */
function ViewHelper_Html_ajaxTable($URL, $Titre, array $Colonnes, $JSCallback = null)
{
	$R = '
<table class="ajax-table" data-source="' . $URL . '" data-callback="' . $JSCallback . '">
	<caption>' . $Titre . '</caption>
<thead>
<tr>';
	foreach($Colonnes as $Colonne)
	{
		$R .= '	<th>' . $Colonne . "</th>\n";
	}

	$R .= '</tr>
</thead>
<tbody>
<tr>
	<td colspan="' . count($Colonnes) . '" style="text-align:center;">
		<img src="/public/images/global/loader.gif" alt="Chargement en cours..." />
	</td>
</tr>
</tbody>
</table>';

	return $R;
}

/**
 * Génère le code HTML eDevoir.
 *
 * @param string $markup la balise à utiliser. Strong par défaut.
 */
function ViewHelper_Html_eDevoir($markup='strong')
{
	return '<' . $markup . ' class="edevoir"><span>e</span>Devoir</' . $markup . '>';
}


/**
 * Initialise le Typographe pour une utilisation avec la documentation.
 * @see ViewHelper_Html_fromTex
 */
function initTypo()
{
	include PATH . '/lib/typo/Typo.php';
	//Typo::addOption(PARSE_MATH);

	//Gestion de la documentation
	Typo::addBalise('#\\\\doc\[([a-z_-]+)\]{(.+)}#isU', '<a href="/$1.htm">$2</a>');
	Typo::addBalise('#\\\\doc\[([a-z]+/[a-z_-]+)\]{(.+)}#isU', '<a href="/documentation/$1">$2</a>');
	Typo::addBalise('#\\\\eDevoir#', ViewHelper_Html_eDevoir());

	//Empêcher de mettre en forme le texte dans les ref.
	Typo::$Escape_And_Prepare['#\\\\doc\[.+(oe).+\]{(.+)}#isU']=array(
		'Protect' => 'DOC-REF',
		'RegexpCode'=>1,
 	);
}

/**
 * Renvoie le contenu d'un fichier TeX mis en forme HTML.
 *
 * @param string $URL
 *
 * @return string du HMTL.
 */
function ViewHelper_Html_fromTex($URL)
{
	if(!class_exists('Typo', false))
	{
		initTypo();
	}

	Typo::setTexteFromFile($URL);
	$HTML = Typo::Parse();

	$HTML = preg_replace_callback(
		'`__([A-Z_]+)__`',
		create_function(
			'$Constante',
			'return constant($Constante[1]);'
		),
		$HTML
	);
	return $HTML;
}