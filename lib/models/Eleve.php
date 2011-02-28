<?php
/**
 * Eleve.php - 3 déc. 2010
 * 
 * Un élève. La classe étend les membres génériques et fournit quelques méthodes utiles.
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
 * Un élève inscrit au site.
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
	LEFT JOIN Classes ON Classes.Classe = %TABLE%.Classe
	WHERE %TABLE%.ID=%ID%';
	
	public static $Props;

	public $Classe;
	public $DetailsClasse;
	public $Section;
	
	/**
	 * Renvoie le pourcentage multiplicateur de suractivité pour l'élève
	 * 
	 * @return int un nombre >=100.
	 */
	public function getRaise()
	{
		static $Raise = 0;
		
		if($Raise == 0)
		{
			$Raise = SQL::singleColumn(
				'SELECT COUNT(DISTINCT(DATE(Creation))) AS Nb
				FROM Exercices
				WHERE Createur=' . $this->getFilteredId() . '
				AND Creation BETWEEN
					"' . SQL::getDate(time()-CALCUL_CUMUL*3600*24) . '"
					AND
					CURDATE()
				',
				'Nb'
			);
			
			$Raise = min(POURCENTAGE_MAX_SURACTIVITE, 100 + $Raise * POURCENTAGE_SURACTIVITE);
		}
		return $Raise;
	}
	
	/**
	 * Renvoie vrai si l'élève peut créer un nouvel exercice
	 * 
	 * @return bool
	 */
	public function isAbleToCreate()
	{
		return ($this->getCreated()<=MAX_EXERCICE_CREES);
	}
	
	/**
	 * Renvoie le nombre d'exercices crées en attente (non acceptés, non annulés, non terminés)
	 * 
	 * @return int
	 */
	public function getCreated()
	{
		return SQL::singleColumn(
			'SELECT COUNT(*) AS Nb
			FROM Exercices
			WHERE Createur=' . $this->getFilteredId() . '
			AND Statut IN ("ATTENTE_CORRECTEUR", "ATTENTE_ELEVE")', $Nb
		);	
	}
}

Eleve::$Props = initProps('Eleve');