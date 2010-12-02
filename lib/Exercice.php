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
	const OBJECT_NAME = 'Exercice';
	
	protected $Foreign = array(
		'Createur'=>'Eleve',
		'Matiere'=>'Matiere',
		'Classe'=>'Classe',
		'Type'=>'Type',
		'Statut'=>'Statut',
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
	public $Majoration;
	public $Statut;
	public $Correcteur;
	public $TimeoutCorrecteur;
	public $InfosCorrecteur;
	public $Enchere;
	public $NbRefus;
	public $Remboursement;
	public $Notation;
	public $Retard;
	
	public function __construct()
	{
		
	}
}