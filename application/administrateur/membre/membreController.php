<?php
/**
 * membreController.php - 12 févr. 2011
 * 
 * Actions réalisables sur les membres
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
 * Contrôleur membre du module administration
 *
 * @category Default
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 */
class Administrateur_MembreController extends AbstractController
{
	public function indexAction()
	{
		$this->View->setTitle(
			'Recherche d\'un membre',
			'Cette page permet de rechercher un membre, qu\'il soit élève ou correcteur.'
		);
		$this->View->addScript();
	}
	
	/**
	 * Renvoie tous les membres répondant à la demande.
	 * Utilisé dans le text-input de index.
	 */
	public function _searchActionWd()
	{
		$Reponses = Sql::queryAssoc(
			'SELECT ID, Mail AS label, Type AS category
			FROM Membres
			WHERE Mail LIKE "' . SQL::escape($this->Data['data']) . '%"',
			'ID'
		);
		
		$this->json($Reponses);
	}
	
	public function eleveActionWd()
	{
		$Eleve = Eleve::load('-1 OR Membres.Mail="' . Sql::escape($this->Data['data']) . '"', false);
		if(is_null($Eleve))
		{
			$this->View->setMessage('warning', "Ce membre n'existe pas.");
			$this->redirect('/administrateur/membre/');
		}
		
		$this->View->setTitle(
			'Informations élève pour ' . $Eleve->Mail,
			'Cette page affiche les différentes informations connues sur cet élève.'
		);
		
		$this->View->Eleve = $Eleve;
		
		$this->View->Membre = $this->concat('/administrateur/membre/membre/' . $this->Data['data']);
		$this->View->Membre->Membre = $Eleve;
	}
	
	/**
	 * !Fonction spéciale, qui n'est théoriquement jamais appelée directement.
	 */
	public function membreActionWd()
	{
		$this->View->setTitle(
			'Informations membre',
			'Cette page affiche les différentes informations de la personne demandée en tant que membre.'
		);
	}
}