<?php
/**
 * Exercice.php - 2 déc. 2010
 * 
 * Un objet exercice. L'objet le plus complexe de l'application.
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
class Exercice extends DbObject
{
	const TABLE_NAME = 'Exercices';
	const SQL_QUERY = 'SELECT Exercices.*, Classes.NomClasse
	FROM %TABLE%
	LEFT JOIN Classes ON (Classes.ID = %TABLE%.Classe)
	WHERE Hash="%ID%"';
	
	public static $Props;
	
	/**
	 * Génère un hash pour l'insertion d'un exercice.
	 * 
	 * @return string(40) un hash.
	 */
	public static function generateHash()
	{
		return sha1('EX_' . uniqid());	
	}
	
	/**
	 * Filtre les valeurs incorrectes de l'identifiant pour se prémunir de potentielles injections SQL.
	 * 
	 * @param string $ID l'identifiant (hash) à protéger
	 * 
	 * @return string(6) le hash filtré
	 */
	public static function filterID($ID=null)
	{
		return preg_replace('`[^a-zA-Z0-9]`', '', $ID);
	}
	
	protected $Foreign = array(
		'Createur'=>'Eleve',
		//'Matiere'=>'Matiere',
		'Classe'=>'Classe',
		'Type'=>'Type',
		//'Statut'=>'Statut',
		'Correcteur'=>'Correcteur'
	);
	
	public $Hash;
	public $LongHash;
	public $Createur;
	public $Creation;
	public $TimeoutEleve;
	public $Expiration;
	public $Matiere;
	public $Classe;
	public $NomClasse;
	public $Section;
	public $Type;
	public $Demande;
	public $InfosEleve;
	public $Autoaccept;
	public $Modificateur;
	public $Statut;
	public $Correcteur;
	public $TimeoutCorrecteur;
	public $InfosCorrecteur;
	public $Enchere;
	public $NbRefus;
	public $Remboursement;
	public $Notation;
	public $Retard;
	
	/**
	 * Modifie le statut de l'exercice
	 * 
	 * @param string $Status
	 * @param int $ChangeAuthor l'auteur du changement (id)
	 * @param string $ChangeMessage
	 * @param array $Changes autres changements à apporter à l'objet en base de données
	 */
	public function setStatus($Status, $ChangeAuthor, $ChangeMessage, array $Changes=array())
	{
		$Changes['Statut'] = $Status;
		
		$this->setAndSave($Changes);
		
		$ToInsert = array(
			'Exercice' => $this->getFilteredId(),
			'Membre' => DbObject::filterID($ChangeAuthor),
			'Action' => $ChangeMessage,
			'Statut' => $Status);
		
		SQL::insert('Exercices_Logs', $ToInsert);
	}
	
	public function getFilteredId()
	{
		return self::filterId($this->Hash);
	}
	
	/**
	 * Renvoie true si l'exercice peut être annulé
	 * 
	 * @return bool
	 */
	public function isCancellable()
	{
		$Cancellable = array('VIERGE','ATTENTE_CORRECTEUR','ATTENTE_ELEVE');
		
		return (in_array($this->Statut, $Cancellable));
	}
	
	/**
	 * Renvoie true si l'exercice est clos.
	 * 
	 * @return bool
	 */
	public function isClosed()
	{
		$Ended = array('ANNULE','TERMINE','REMBOURSE');
		
		return (in_array($this->Statut, $Ended));
	}
	
	/**
	 * Récupère le correcteur associé à l'exercice.
	 * 
	 * @return Correcteur
	 */
	public function getCorrecteur()
	{
		return $this->getForeignItem('Correcteur');
	}
	
	/**
	 * Récupère le créateur (élève) de l'exercice
	 * 
	 * @return Eleve
	 */
	public function getEleve()
	{
		return $this->getForeignItem('Createur');
	}
	
	/**
	 * Prix demandé par le correcteur
	 * 
	 * @return int
	 */
	public function priceAsked()
	{
		return $this->Enchere;
	}
	
	/**
	 * Prix payé par l'élève
	 * 
	 * @return int
	 */
	public function pricePaid()
	{
		return $this->priceAsked()*MARGE;
	}
	
	/**
	 * Récupère la liste des fichiers associés à l'exercice dans un tableau associatif de la forme
	 * URL => array(Type, ThumbURL, Description)
	 * 
	 * @param array $Types le type des fichiers (SUJET, CORRIGE ou RECLAMATION). Tous par défaut.
	 * 
	 * @return array un tableau d'éléments tels que décrits plus hauts.
	 */
	public function getFiles(array $Types = array('SUJET','CORRIGE','RECLAMATION'))
	{
		$Types = $this->buildType($Types);
		
		$Query = 'SELECT Type, URL, ThumbURL, Description
		FROM Exercices_Fichiers
		WHERE Exercice = ' . $this->ID . '
		AND Type IN ' . $Types;
		
		return Sql::queryAssoc($Query, 'URL');
	}
	
	/**
	 * Récupère le nombre de fichiers du type spécifié.
	 * 
	 * @param array $Types le type des fichiers (SUJET, CORRIGE ou RECLAMATION). Tous par défaut.
	 * 
	 * @return int le nombre d'éléments du type spécifié.
	 */
	public function getFilesCount(array $Types = array('SUJET','CORRIGE','RECLAMATION'))
	{
		$Types = $this->buildType($Types);
		
		$Query = 'SELECT COUNT(*) AS Nb
		FROM Exercices_Fichiers
		WHERE Exercice = ' . $this->ID . '
		AND Type IN ' . $Types;
		
		return Sql::singleColumn($Query, 'Nb');
	}
	
	/**
	 * Construit un tableau de type utilisable avec MySQL.
	 * 
	 * @param array $Types
	 * 
	 * @return string les valeurs échappées entre parenthèses.
	 */
	protected function buildType(array $Types)
	{
		foreach($Types as &$Type)
		{
			$Type = '"' . Sql::escape($Type) . '"';
		}
		return '(' . implode(',', $Types) . ')';
	}
}

Exercice::$Props = initProps('Exercice');