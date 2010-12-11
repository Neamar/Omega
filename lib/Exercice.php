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
	public static $Props;
	
	protected $Foreign = array(
		'Createur'=>'Eleve',
		//'Matiere'=>'Matiere',
		'Classe'=>'Classe',
		'Type'=>'Type',
		//'Statut'=>'Statut',
		'Correcteur'=>'Correcteur'
	);
	
	public $Hash;
	public $Createur;
	public $Creation;
	public $TimeoutEleve;
	public $Expiration;
	public $Matiere;
	public $Classe;
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
	public function setStatus($Status,$ChangeAuthor, $ChangeMessage, array $Changes=array())
	{
		$Changes['Statut'] = $Status;
		
		$this->setAndSave($Changes);
		
		$ToInsert = array(
			'Exercice'=>$this->getFilteredId(),
			'Membre'=>DbObject::filterID($ChangeAuthor),
			'Action'=>$ChangeMessage,
			'Statut'=>$Status);
		
		SQL::insert('Exercices_Logs', $ToInsert);
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
	public function isCancellable()
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
}

Exercice::$Props = init_props('Exercice');