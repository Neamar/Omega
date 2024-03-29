<?php 
/**
 * Html.php - 26 oct. 2010
 *
 * Offrir des primitives de haut niveau pour la gestion des éléments HTML de documentation.
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
 * Récupérer l'URL vers une page de la documentation
 * 
 * @param string $section
 * @param string $page
 */
function ViewHelper_Doc_link($section, $page)
{
	if($section == 'index')
	{
		$URL = '/' . $page . '.htm'; 
	}
	else
	{
		$URL = '/documentation/' . $section . '/' . $page;
	}
	
	return $URL;
}	
/**
 * Génère un lien vers la page de documentation spécifiée.
 * 
 * @param string $section la section de documentation (eleve par exemple)
 * @param string $page la page de la section (exemple : inscription)
 * @param string $caption le titre de la page ; si non spécifié, le titre de la page spécifiée.
 * 
 * @return string le code HTML demandé.
 */
function ViewHelper_Doc_anchor($section, $page, $caption = null)
{
	$title = DocumentationAbstractController::getTitle($section, $page);
	if(is_null($caption))
	{
		$caption = $title;
	}
	return '<a href="' . ViewHelper_Doc_link($section, $page) . '" title="Accès à la documentation : ' . $title . '" target="_blank" class="doc-link">' . $caption . '</a>';
}

/**
 * Ajoute un champ d'aide après un élément de type input (ou n'importe quel élèment de formulaire)
 * 
 * @param string $section la section de documentation (eleve par exemple)
 * @param string $page la page de la section (exemple : inscription)
 * @param string $caption le titre de la page ; si non spécifié, le titre de la page spécifiée.
 */
function ViewHelper_Doc_input($section, $page, $caption = null)
{
	return '<span class="doc-input">' . ViewHelper_Doc_anchor($section, $page, $caption) . '</span>';
}

/**
 * Ajoute un champ d'aide après un élément de type input (ou n'importe quel élèment de formulaire)
 * 
 * @param string $section la section de documentation (eleve par exemple)
 * @param string $page la page de la section (exemple : inscription)
 * @param string $caption le titre de la page ; si non spécifié, le titre de la page spécifiée.
 */
function ViewHelper_Doc_inputBr($section, $page, $caption = null)
{
	return ViewHelper_Doc_input($section, $page, $caption) . "<br />\n";
}

/**
 * Afifche une boîte de documentation avec le texte $caption et un lien vers l'aide.
 * 
 * @param string $section
 * @param string $page
 * @param string $caption
 */
function ViewHelper_Doc_box($section, $page, $caption, $class='doc-box')
{
	return '<aside class="' . $class . '">
	<p>' . $caption . '</p>
	
	<p class="doc-box-link">' . ViewHelper_Doc_anchor($section, $page) . '</p>
	
</aside>
';
}