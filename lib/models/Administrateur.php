<?php
/**
 * Administrateur.php - 12 fév. 2011
 * 
 * Un administrateur du site.
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
 * Un administrateur
 *
 * @category Default
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 */
class Administrateur extends Membre
{
	const TABLE_NAME = 'Admins';
	const SQL_QUERY = 'SELECT * FROM %TABLE%
	LEFT JOIN Membres ON Membres.ID = %TABLE%.ID
	WHERE %TABLE%.ID=%ID%';
	
	public static $Props;
}

Administrateur::$Props = initProps('Administrateur');