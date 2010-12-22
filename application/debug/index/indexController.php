<?php
/**
 * indexController.php - 26 oct. 2010
 * 
 * Fonctions de debuggage
 * 
 * PHP Version 5
 * 
 * @category  Controller
 * @package   Root
 * @author    Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @copyright 2010 Matthieu Bacconnier
 * @license   Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link      http://devoirminute.com
 */

/**
 * Contrôleur d'index du module élève.
 * 
 * @category Controller
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://devoirminute.com
 *
 */
class Debug_IndexController extends AbstractController
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
	 * Page d'accueil du module.
	 * 
	 */
	public function indexAction()
	{
		$this->View->setTitle('Module de debug');
	}
	
	/**
	 * Dumping de la session
	 * 
	 */
	public function sessionAction()
	{
		$this->View->setTitle('Dump de la session.');
	}
	
	/**
	 * Structure d'une session.
	 * 
	 */
	public function sessionStructAction()
	{
		$this->View->setTitle('Dump de la structure session.');
	}
	
	/**
	 * Dumping des données serveur
	 * 
	 */
	public function serverAction()
	{
		$this->View->setTitle('Dump des données serveur.');
	}
	
	/**
	 * Page par défaut.
	 * 
	 */
	public function pageAction()
	{
		$this->View->setTitle('Exemple de contenu HTML');
		
		$this->View->setMessage("error", "Texte du message d'erreur", "foo/bar");
	}
}