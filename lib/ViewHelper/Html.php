<?php 
/**
 * Html.php - 26 oct. 2010
 *
 * Offrir des primitives de bas niveau pour la gestion des éléments HTML.
 *
 * PHP Version 5
 *
 * @category  ViewHelper
 * @package   Root
 * @author    Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @copyright 2010 Matthieu Bacconnier
 * @license   Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link      http://devoirminute.com
 */

/**
 * Génère une liste avec les items spécifiés.
 * 
 * @param array $items la liste à créer
 * @param string $type ul ou ol.
 * 
 * @return string le code HTML demandé.
 */
function ViewHelper_Html_list(array $Items, $Type='ul')
{
	$R = '<' . $Type . ">\n";
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
function ViewHelper_Html_list_anchor(array $Items, $Type='ul')
{
	$R = '<' . $Type . ">\n";
	foreach($Items as $URL => $Item)
	{
		$R .= '	<li><a href="' . $URL . '">' . $Item . "</a></li>\n";
	}
	$R .= '</' . $Type . ">\n";
	
	return $R;
}