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
	
	public static $_Props;

	public $Classe;
	public $Section;
	
	/**
	 * Renvoie le pourcentage multiplicateur de suractivité pour l'élève
	 * 
	 * @return int un nombre >100.
	 */
	public function getRaise()
	{
		
		$Raise = SQL::singleColumn('SELECT COUNT(DISTINCT(DATE(Creation))) AS Nb
		FROM Exercices
		WHERE Createur=' . $this->getFilteredId() . '
		AND Creation > "' . SQL::getDate(time()-CALCUL_CUMUL*3600*24) . '"', 'Nb');
		
		$Raise = min(POURCENTAGE_MAX_SURACTIVITE, 100 + $Raise * POURCENTAGE_SURACTIVITE);
		return $Raise;
	}
}

Eleve::$_Props = init_props('Eleve');