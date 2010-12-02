<?php
/**
 * Membre.php - 2 déc. 2010
 * 
 * Un membre du site.
 * Classe abstraite.
 * 
 * PHP Version 5
 * 
 * @category  Db
 * @package   Root
 * @author    Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @copyright 2010 Matthieu Bacconnier
 * @license   Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link      http://edevoir.com
 * @param string
 * @param string
 */
 
/**
 * Documentation de la classe
 *
 * @category Db
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 */
class Membre extends DbObject
{
	const TABLE_NAME = 'Membres';
	const OBJECT_NAME = 'Membre';

	public function __construct()
	{
		
	}
}