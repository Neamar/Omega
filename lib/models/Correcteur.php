<?php
/**
 * Correcteur.php - 6 déc. 2010
 * 
 * Un correcteur du site.
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
 * Un correcteur
 *
 * @category Default
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 */
class Correcteur extends Membre
{
	const TABLE_NAME = 'Correcteurs';
	const SQL_QUERY = 'SELECT * FROM %TABLE%
	LEFT JOIN Membres ON Membres.ID = %TABLE%.ID
	WHERE %TABLE%.ID=%ID%';
	
	public static $Props;
	
	public $Nom;
	public $Prenom;
	public $Telephone;
	public $Siret;
	public $SiretOK;
	
	/**
	 * Renvoie l'identité du correcteur
	 */
	public function identite()
	{
		return $this->Prenom . '&nbsp;' . $this->Nom;
	}
	
	/**
	 * Renvoie vrai si le correcteur peut réserver un exercice.
	 * 
	 * @return bool
	 */
	public function isAbleToBook()
	{
		return ($this->getBooked() <= MAX_EXERCICE_RESERVES);
	}
	
	/**
	 * Renvoie le nombre d'exercices crées en attente (non acceptés, non annulés, non terminés)
	 * 
	 * @return int
	 */
	public function getBooked()
	{
		return SQL::singleColumn(
			'SELECT COUNT(*) AS Nb
			FROM Exercices
			WHERE Correcteur = ' . $this->getFilteredId() . '
			AND Statut IN ("ATTENTE_ELEVE", "EN_COURS")',
			'Nb'
		);
	}
}
Correcteur::$Props = initProps('Correcteur');