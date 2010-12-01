<?php
/**
 * test.php - 26 oct. 2010
 *
 * Réaliser des tests de base
 * 
 * PHP Version 5
 *
 * @category  Root
 * @package   Root
 * @author    Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @copyright 2010 Matthieu Bacconnier
 * @license   Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link      http://devoirminute.com
 */

class Mere
{
	protected  static $var = 'Mere';
	public static function func()
	{
		echo static::$var;
	}
}

class Fille extends Mere
{
	protected static $var = 'Fille';
}

Fille::func();