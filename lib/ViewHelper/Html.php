<?php 
/**
 * Doc.php - 26 oct. 2010
 *
 * Offrir des primitives pour la gestion des liens de documentations
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
 * @param array $items la liste à créer. Les clés représentent l'url, les valeurs le texte du lien.
 * @param string $type ul ou ol.
 * 
 * @return string le code HTML demandé.
 */
function ViewHelper_Html_listAnchor(array $Items, $Type='ul')
{
	$R = '<' . $Type . ">\n";
	foreach($Items as $URL => $Item)
	{
		$R .= '	<li><a href="' . $URL . '">' . $Item . "</a></li>\n";
	}
	$R .= '</' . $Type . ">\n";
	
	return $R;
}