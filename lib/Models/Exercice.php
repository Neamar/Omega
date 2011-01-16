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
	const SQL_QUERY = 'SELECT Exercices.*, Classes.DetailsClasse, Types.DetailsType, Demandes.DetailsDemande, Statuts.DetailsStatut
FROM %TABLE%
LEFT JOIN Classes ON (Classes.Classe = %TABLE%.Classe)
LEFT JOIN Types ON (Types.Type = %TABLE%.Type)
LEfT JOIN Demandes ON (Demandes.Demande = %TABLE%.Demande)
LEFT JOIN Statuts ON (Statuts.Statut = %TABLE%.Statut)
WHERE Hash="%ID%"';
	
	public static $Props;
	
	/**
	 * Quelles sont les différentes possibilités de changement de statut.
	 * Sorte de diagramme d'état entre les différentes étapes.
	 * 
	 * @var array
	 */
	public static $Workflow = array(
		'VIERGE' 				=> array('ANNULE', 'ATTENTE_CORRECTEUR'),
		'ATTENTE_CORRECTEUR'	=> array('ANNULE', 'ATTENTE_ELEVE'),
		'ATTENTE_ELEVE'			=> array('ANNULE', 'ATTENTE_CORRECTEUR', 'EN_COURS'),
		'EN_COURS'				=> array('ENVOYE', 'ANNULE'),
		'ENVOYE'				=> array('TERMINE', 'REFUSE'),
		'REFUSE'				=> array('TERMINE', 'REMBOURSE'),
		'TERMINE'				=> array(),
		'ANNULE'				=> array(),
	);
	
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
	public $Titre;
	public $Createur;
	public $Creation;
	public $TimeoutEleve;
	public $Expiration;
	public $Matiere;
	public $Classe;
	public $DetailsClasse;
	public $Section;
	public $Type;
	public $DetailsType;
	public $Demande;
	public $DetailsDemande;
	public $InfosEleve;
	public $Autoaccept;
	public $Modificateur;
	public $Statut;
	public $DetailsStatut;
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
	 * @param Membre $ChangeAuthor l'auteur du changement
	 * @param string $ChangeMessage
	 * @param array $Changes autres changements à apporter à l'objet en base de données
	 */
	public function setStatus($Status, Membre $ChangeAuthor, $ChangeMessage, array $Changes = array())
	{
		if(!in_array($Status, self::$Workflow[$this->Statut]))
		{
			Debug::fail('Impossible de passer du statut ' . $this->Statut . ' au statut ' . $Status);
		}
		
		$Changes['Statut'] = $Status;
		
		$this->setAndSave($Changes);
		
		$this->log('Exercices_Logs', $ChangeMessage, $ChangeAuthor, $this, array('Statut' => $Status));
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
	 * URL => array(Type, ThumbURL, NomUpload, Description)
	 * @see Exercice::getSortedFiles
	 * 
	 * @param array $Types le type des fichiers (SUJET, CORRIGE ou RECLAMATION). Tous par défaut.
	 * 
	 * @return array un tableau d'éléments tels que décrits plus hauts.
	 */
	public function getFiles(array $Types = array('SUJET','CORRIGE','RECLAMATION'))
	{
		$Types = $this->buildType($Types);
		
		$Query = 'SELECT Type, URL, ThumbURL, NomUpload, Description
		FROM Exercices_Fichiers
		WHERE Exercice = ' . $this->ID . '
		AND Type IN ' . $Types . '
		ORDER BY Type, ID';
		
		return Sql::queryAssoc($Query, 'URL');
	}
	
	/**
	 * Renvoie tous les fichiers de l'exercice, correctement triés dans un tableau.
	 * 
	 * @return array array('SUJET' =>array(files), 'CORRIGE'=>array(files), 'RECLAMATION'=> =>array(files))
	 */
	public function getSortedFiles()
	{
		$Liens = array(
			'SUJET' => array(),
			'CORRIGE' => array(),
			'RECLAMATION' => array()
		);
		
		$Files = $this->getFiles();
		foreach ($Files as $URL => $Infos)
		{
			$Liens[$Infos['Type']][$URL] = $Infos;
		}
		
		return $Liens;
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
	 * Récupère le nombre de fichiers du type spécifié.
	 * 
	 * @param array $Types le type des fichiers (SUJET, CORRIGE ou RECLAMATION). Tous par défaut.
	 * 
	 * @return int le nombre d'éléments du type spécifié.
	 */
	public function getFilesCountByType()
	{
		$Types = $this->buildType($Types);
		
		$Query = 'SELECT Type, COUNT(*) AS Nb
		FROM Exercices_Fichiers
		WHERE Exercice = ' . $this->ID . '
		GROUP BY Type';
		
		$R = Sql::queryAssoc($Query, 'Type', 'Nb');
		$Defaults = array(
			'SUJET'=>0,
			'CORRIGE'=>0,
			'RECLAMATION'=>0
		);
		return array_merge($Defaults, $R);
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