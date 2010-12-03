<?php
/**
 * Eleve.php - 3 déc. 2010
 * 
 * Un élève. La classe étend les mzmbre génériques et fournit quelques méthodes utiles.
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
 * 
 *
 * @category Db
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 */
class Eleve extends Membre
{
	const TABLE_NAME = 'Eleves';
	const SQL_QUERY = 'SELECT * FROM %TABLE%
	LEFT JOIN Membres ON Membres.ID = %TABLE%.ID
	WHERE %TABLE%.ID=%ID%';
	
	public $Classe;
	public $Section;
}