<?php
/**
 * exerciceController.php - 30 déc. 2010
 * 
 * Actions pour un correcteur sur un exercice.
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
 * Contrôleur d'exercice du module correcteur.
 * 
 * @category Controller
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 *
 */
class Correcteur_ExerciceController extends ExerciceAbstractController
{
	/**
	 * Demander à réserver un article.
	 */
	public function reservationActionWd()
	{
		$this->canAccess(array('ATTENTE_CORRECTEUR'), 'Vous ne pouvez plus réserver cet exercice !');
		
		$this->View->setTitle(
			'Réservation de « ' . $this->Exercice->Titre . ' »',
			"Cette page permet de consulter un exercice avant de le réserver en indiquant votre prix."
		);
	}
	
	/**
	 * Vérifie que l'exercice associé à la page est disponible.
	 * Overridé par les classes filles.
	 * @see ExerciceAbstractController::hasAccess
	 * 
	 * @return bool true si l'exercice peut être accédé.
	 */
	protected function hasAccess(Exercice $Exercice)
	{
		return (($Exercice->Correcteur == $_SESSION['Correcteur']->ID) || $Exercice->Statut == 'ATTENTE_CORRECTEUR');
	}
}