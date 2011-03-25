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
		$this->View->setTitle(
			"Données du site",
			"Cette section du site permet de consulter en quasi temps réel les informations enregistrées par le site."
		);
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
	
	public function rawAction()
	{
		$this->View->setTitle(
			'Données brutes',
			'Cette page affiche les différentes données de bas niveau disponible.'
		);
	}
	
	public function _rawAction()
	{
		$Logs = array_reverse(file(DATA_PATH . '/logs/' . date('Y-m-d') . '.log'));
		$Limit = isset($_POST['limit'])?intval($_POST['limit']):AJAX_LIMITE;
		$Total = count($Logs);
		$Logs = array_slice($Logs, 0, $Limit);
		
		$R = array();
		foreach($Logs as $Log)
		{
			$Data = explode('	', $Log);
			
			//Mettre en couleur les URLs
			$Data[1] = str_replace(
				array(
					'/administrateur',
					'/eleve',
					'/correcteur'
				),
				array(
					'<span style="color:#F16C7C">A</span>',
					'<span style="color:#E06C0E">E</span>',
					'<span style="color:#006699">C</span>',
				),
				$Data[1]
			);
			$R[] = array($Data[0], $Data[1], $Data[4]);
		}
		
		if($Total > $Limit)
		{
			$R[] = '+';
		}
		
		$this->json($R);		
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