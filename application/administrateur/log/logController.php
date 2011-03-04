<?php
/**
 * logController.php - 16 févr. 2011
 * 
 * Logs et statistiques
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
 * Contrôleur log du module administration
 *
 * @category Default
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 */
class Administrateur_LogController extends AbstractController
{
	public function indexAction()
	{
		//TODO : faire un index
		exit('TODO');
	}
	
	/**
	 * Dernières actions sur les exercices
	 */
	public function exerciceAction()
	{
		$this->View->setTitle(
			"Informations Exercices",
			"Cette page permet de consulter en quasi temps réel les informations sur les exercices."
		);
	}
	
	/**
	 * Derniers exercices
	 */
	public function _exerciceAction()
	{
		$this->ajax(
			'SELECT
				DATE_FORMAT(Exercices.Creation,"%d/%m/%y à %Hh"),
				Exercices.Titre,
				DATE_FORMAT(Exercices.Expiration,"%d/%m/%y à %Hh"),
				CONCAT("<a href=/administrateur/exercice/index/", Exercices.Hash,">Consulter</a>")
			FROM Exercices',
			'Exercices.Creation DESC'
		);
	}
	
	/**
	 * Dernières actions sur les exercices
	 */
	public function _exercice_logAction()
	{
		$this->ajax(
			'SELECT
				DATE_FORMAT(Exercices_Logs.Date,"%d/%m/%y à %Hh"),
				Exercices.Titre,
				Exercices_Logs.Action, 
				CONCAT("<a href=/administrateur/exercice/index/", Exercices.Hash,">Consulter</a>")
			FROM Exercices_Logs
			JOIN Exercices ON (Exercices_Logs.Exercice = Exercices.ID)'
		);
	}
	
	public function eleveAction()
	{
		$this->View->setTitle(
			'Informations sur les élèves',
			'Cette page affiche les différentes informations des élèves.'
		);
	}
	
	/**
	 * Derniers élèves inscrits
	 */
	public function _eleveAction()
	{
		$this->ajax(
			'SELECT
				DATE_FORMAT(Creation,"%d/%m/%y à %Hh"),
				Mail,
				Statut,
				DetailsClasse, 
				CONCAT("<a href=/administrateur/membre/eleve/", Mail,">Consulter</a>")
			FROM Membres
			JOIN Eleves ON (Eleves.ID = Membres.ID)
			JOIN Classes ON (Classes.Classe = Eleves.Classe)',
			'Creation DESC'
		);
	}
	
	/**
	 * Dernières actions des élèves
	 */
	public function _eleve_logAction()
	{
		$this->ajax(
			'SELECT
				DATE_FORMAT(Date,"%d/%m/%y à %Hh"),
				Mail,
				Action,
				IF(Delta < 0, CONCAT("<span style=color:red>", Delta, "</span>"), CONCAT("<span style=color:green>", Delta, "</span>")),
				CONCAT("<a href=/administrateur/membre/eleve/", Mail,">Consulter</a>")
			FROM Logs
			JOIN Membres ON (Membres.ID = Logs.Membre)
			WHERE Membres.TYPE = "ELEVE"'
		);
	}
	
	public function correcteurAction()
	{
		$this->View->setTitle(
			'Informations sur les correcteurs',
			'Cette page affiche les différentes informations des correcteurs.'
		);
	}
	
	/**
	 * Derniers correcteurs inscrits
	 */
	public function _correcteurAction()
	{
		$this->ajax(
			'SELECT
				DATE_FORMAT(Creation,"%d/%m/%y à %Hh"),
				Mail,
				Statut,
				CONCAT("<a href=/administrateur/membre/correcteur/", Mail,">Consulter</a>")
			FROM Membres
			JOIN Correcteurs ON (Correcteurs.ID = Membres.ID)',
			'Creation DESC'
		);
	}
	
	/**
	 * Dernières actions des correcteurs
	 */
	public function _correcteur_logAction()
	{
		$this->ajax(
			'SELECT
				DATE_FORMAT(Date,"%d/%m/%y à %Hh"),
				Mail,
				Action,
				IF(Delta < 0, CONCAT("<span style=color:red>", Delta, "</span>"), CONCAT("<span style=color:green>", Delta, "</span>")),
				CONCAT("<a href=/administrateur/membre/eleve/", Mail,">Consulter</a>")
			FROM Logs
			JOIN Membres ON (Membres.ID = Logs.Membre)
			WHERE Membres.Type = "CORRECTEUR"'
		);
	}
	
	public function membreAction()
	{
		$this->View->setTitle(
			'Informations sur les membres',
			'Cette page affiche les différentes informations des membres.'
		);
	}
	
	/**
	 * Derniers membres inscrits
	 */
	public function _membreAction()
	{
		$this->ajax(
			'SELECT
				DATE_FORMAT(Creation,"%d/%m/%y à %Hh"),
				Mail,
				Statut,
				LOWER(Type),
				CONCAT("<a href=/administrateur/membre/", LOWER(Type), "/", Mail,">Consulter</a>")
			FROM Membres',
			'Creation DESC'
		);
	}
	
	/**
	 * Dernières actions des membres
	 */
	public function _membre_logAction()
	{
		$this->ajax(
			'SELECT
				DATE_FORMAT(Date,"%d/%m/%y à %Hh"),
				Mail,
				Action,
				LOWER(Type),
				IF(Delta < 0, CONCAT("<span style=color:red>", Delta, "</span>"), CONCAT("<span style=color:green>", Delta, "</span>")),
				CONCAT("<a href=/administrateur/membre/", LOWER(Type), "/", Mail,">Consulter</a>")
			FROM Logs
			JOIN Membres ON (Membres.ID = Logs.Membre)'
		);
	}

	
	/**
	 * Infos globales affichées sur l'accueil de l'administration
	 */
	public function _globalAction()
	{
		$Data = array();
		
		$Data[] = array(
			'Exercices crées',
			1,
			2,
			7,
		);
	
		
		$Data[] = array(
			'&Delta; points',
			1,
			2,
			7,
		);
		
		$this->json($Data);
	}
}