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
		$this->ajax('SELECT
				DATE_FORMAT(Exercices.Creation,"%d/%c/%y à %Hh"),
				Exercices.Titre,
				DATE_FORMAT(Exercices.Expiration,"%d/%c/%y à %Hh"),
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
		$this->ajax('SELECT
				DATE_FORMAT(Exercices_Logs.Date,"%d/%c/%y à %Hh"),
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
}