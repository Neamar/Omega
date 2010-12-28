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
 * @param array $items la liste à créer. Les clés représentent l'url, les valeurs le texte du lien.
 * @param string $type ul ou ol.
 * 
 * @return string le code HTML demandé.
 */
function ViewHelper_Html_listAnchor(array $Items, $Type='ul')
{
	if(count($Items)==0)
	{
		return '';
	}
	
	$R = '<' . $Type . ">\n";
	foreach($Items as $URL => $Item)
	{
		$R .= '	<li><a href="' . $URL . '">' . $Item . "</a></li>\n";
	}
	$R .= '</' . $Type . ">\n";
	
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