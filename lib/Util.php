<?php
/**
 * Util.php - 22 déc. 2010
 * 
 * Fonctions utilitaires
 * Fichier à ne pas utiliser comme dépotoir !
 * 
 * PHP Version 5
 * 
 * @category  Default
 * @package   Root
 * @author    Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @copyright 2010 Matthieu Bacconnier
 * @license   Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link      http://edevoir.com
 * @param string
 * @param string
 */
 
/**
 * Fonctions utilisées un peu partout.
 * Ce n'est pas une classe poubelle !
 * 
 * @category Default
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 */
class Util
{
	/**
	 * Renvoie l'extension du fichier passé en paramètre
	 * 
	 * @param string $Filename nom du fichier
	 * 
	 * @return string l'extension en minuscule
	 */
	public static function extension($Filename)
	{
		return strtolower(substr(strrchr($Filename, '.'), 1));
	}
	
	/**
	 * Hashe le mot de passe fourni
	 * 
	 * @param string $Pass
	 * 
	 * @return string du sha1.
	 */
	public static function hashPass($Pass)
	{
		return sha1(SALT . $Pass);
	}
}