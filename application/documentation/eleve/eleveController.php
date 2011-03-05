<?php
/**
 * eleveController.php - 26 oct. 2010
 * 
 * Documentation élève.
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
 * Contrôleur de documentation générique.
 * 
 * @category Controller
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 *
 */
class Documentation_EleveController extends DocumentationAbstractController
{
	
	/**
	 * Accueil du module eleve.
	 * @see Documentation_IndexController::indexAction()
	 */
	public function indexAction()
	{
		$this->View->setTitle(self::$Pages[$this->Controller]['index']);
		$this->View->addScript('/public/js/documentation/index.js');
		$this->View->Pages = self::$Pages[$this->Controller];
	}
}