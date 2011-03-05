<?php
/**
 * correcteurController.php - 30 dec. 2010
 * 
 * Documentation correcteur.
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
class Documentation_CorrecteurController extends DocumentationAbstractController
{
	/**
	 * Accueil du module correcteur.
	 * @see Documentation_IndexController::indexAction()
	 */
	public function indexAction()
	{
		parent::indexAction();
		
		$this->View->Pages = self::$Pages[$this->Controller];
	}
}