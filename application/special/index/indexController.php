<?php
/**
 * indexController.php - 8 mar. 2011
 * 
 * Fonctions spéciales ne rentrant dans aucun module.
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
 * Contrôleur d'index du module special.
 * 
 * @category Controller
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 *
 */
class Special_IndexController extends AbstractController
{	
	/**
	 * Page de retour après une transaction Paypal.
	 * 
	 */
	public function from_paypalAction()
	{
		exit();
	}
}