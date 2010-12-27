<?php
/**
 * indexController.php - 26 oct. 2010
 * 
 * Contrôleur pour la documentation.
 * 
 * PHP Version 5
 * 
 * @category  Controller
 * @package   Root
 * @author    Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @copyright 2010 Matthieu Bacconnier
 * @license   Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link      http://edevoir.com
 */

/**
 * Contrôleur de base pour toute la documentation.
 * 
 * @category Controller
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 *
 */
class Documentation_IndexController extends AbstractController
{
	private static $_Pages = array
	(
		'foo' => array
		(
			'bar' => "Titre de la page d'aide"
		),
		'index' => array
		(
			'index' => "Accueil de la documentation",
			'fonctionnement' => "Fonctionnement du site",
			'tex' => "Comment insérer des formules mathématiques dans un élément de la FAQ ?",
		),
		'eleve' => array
		(
			'index' => "Aide des élèves",
			'inscription' => "Comment m'inscrire en tant qu'élève ?",
		),
	);
	
	/**
	 * Récupère le titre d'une page.
	 * 
	 * @param string $section
	 * @param string $page
	 * 
	 * @return le titre de la page $section/$page.
	 */
	public static function getTitle($section, $page)
	{
		if(isset(self::$_Pages[$section][$page]))
		{
			return self::$_Pages[$section][$page];
		}
		else
		{
			return 'Page inconnue.';
		}
	}
}