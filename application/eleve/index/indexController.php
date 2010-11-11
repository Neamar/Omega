<?php
/**
 * indexController.php - 26 oct. 2010
 * 
 * Actions de base pour un élève.
 * 
 * PHP Version 5
 * 
 * @category  Controller
 * @package   Root
 * @author    Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @copyright 2010 Matthieu Bacconnier
 * @license   Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link      http://monsite.fr
 */

/**
 * Contrôleur d'index du module élève.
 * 
 * @category Controller
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://monsite.fr
 *
 */
class Eleve_IndexController extends AbstractController
{
	/**
	 * Constructeur.
	 * 
	 * @param string $module nom de module
	 * @param string $controller nom de contrôleur
	 * @param string $view nom de vue
	 * @param string $data données
	 */
	public function __construct($module,$controller,$view,$data)
	{
		parent::__construct($module, $controller, $view, $data);
	}
	
	/**
	 * Page d'accueil du module ; afficher les infos du compte et les liens utiles. 
	 * 
	 * @return void
	 */
	public function indexAction()
	{

	}
}