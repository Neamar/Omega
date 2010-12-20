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
	private static $Pages = array
	(
		'index' => array
		(
			'index' => 'Accueil de la documentation',
			'fonctionnement' => 'Fonctionnement du site',
		),
		'eleve' => array
		(
			'index' => 'Aide des élèves',
		)
	);
}