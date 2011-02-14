<?php
/**
 * exerciceController.php - 14 févr. 2011
 * 
 * Actions de base pour un administrateur sur un exercice
 * 
 * PHP Version 5
 * 
 * @category  Controller
 * @package   Root
 * @author    Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @copyright 2010 Matthieu Bacconnier
 * @license   Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link      http://edevoir.com
 */
 
/**
 * Contrôleur d'exercice du module administrateur.
 *
 * @category Controller
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 */
class Administrateur_ExerciceController extends ExerciceAbstractController
{
	/**
	 * Page d'accueil du module.
	 * @see Administrateur_IndexController::reclamationsAction
	 * 
	 */
	public function indexAction()
	{
		$this->redirect('/administrateur/reclamations');
	}
	
	/**
	 * Statuer sur une demande de remboursement
	 */
	public function remboursementActionWd()
	{
		$this->canAccess(array('REFUSE'), "Cet exercice n'a pas besoin d'action de votre part.");

		$this->View->setTitle(
			"Régler la contestation sur l'exercice «&nbsp;" . $this->Exercice->Titre . '&nbsp;»',
			"Cette page permet de conclure une procédure de réclamation en attribuant les torts à l'un ou l'autre des deux camps."
		);
		$this->View->addScript();
		
		$this->View->EstRemboursable = ($this->Exercice->Reclamation == 'REMBOURSEMENT');
				
		$this->View->Sujet = $this->concat('/administrateur/exercice/sujet/' . $this->Exercice->Hash);
		$this->View->Corrige = $this->concat('/administrateur/exercice/corrige/' . $this->Exercice->Hash);
		$this->View->Reclamation = $this->concat('/administrateur/exercice/reclamation/' . $this->Exercice->Hash);
	}
	
	/**
	 * Vérifie que l'exercice associé à la page est disponible.
	 * En tant qu'admin, on peut accéder à tout.
	 * @see ExerciceAbstractController::hasAccess
	 * 
	 * @return bool true si l'exercice peut être accédé.
	 */
	protected function hasAccess(Exercice $Exercice)
	{
		return true;
	}
}