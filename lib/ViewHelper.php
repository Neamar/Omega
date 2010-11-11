<?php
/**
 * ViewHelper.php - 10 nov. 2010
 *
 * Gestion des aides de vue.
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
 * Classe permettant le chargement rapide des aides de vues.
 * Il suffit d'appeller $VH->nomAideVue pour enclencher le chargement et l'utilisation.
 *
 * @category ViewHelper
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://devoirminute.com
 *
 */
class ViewHelper
{
	/**
	 * Gestion des ViewHelper.
	 * 
	 * @param string $func
	 * @param array $arg
	 */
	public function __call($func,array $args)
	{
		$Helper = 'ViewHelper_' . $func;
		if(!function_exists($Helper))
		{
			list($File) = explode('_', $func);
			include LIB_PATH . '/ViewHelper/' . $File . '.php';
		}
		
		return call_user_func_array($Helper, $args);
	}
}