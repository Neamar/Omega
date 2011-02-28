<?php
/**
 * pointsController.php - 22 janv. 2011
 * 
 * Gestion des points du compte correcteur
 * 
 * PHP Version 5
 * 
 * @category  Controller
 * @package   Root
 * @author    Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @copyright 2010 Matthieu Bacconnier
 * @license   Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link      http://edevoir.com
 * @param string
 * @param string
 */
 
/**
 * Actions sur les points
 *
 * @category Controller
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 */
class Correcteur_PointsController extends PointsAbstractController
{
	/**
	 * Vérifie que le SIRET est valide.
	 * @see PointsAbstractController::retraitAction()
	 */
	public function retraitAction()
	{
		if(empty($_SESSION['Correcteur']->Siret))
		{
			$this->View->setMessage('error', "Impossible de retirer de l'argent tant que vous ne nous avez pas indiqué votre SIRET. <a href=\"/correcteur/options/compte\">Entrer cette information</a>.", "correcteur/pourquoi_autoentreprise");
		}
		elseif($_SESSION['Correcteur']->SiretOK == 0)
		{
			$this->View->setMessage('error', "Impossible de retirer de l'argent tant que votre SIRET n'a pas été validé.");
		}
		else
		{
			$this->View->Message = "Convertissez vos points durement gagnés en euros sonnants et trébuchants !";
			parent::retraitAction();
			return;
		}
		
		$this->redirect('/correcteur/points/');
	}
	
	/**
	 * Ajoute un message si le numéro de SIRET n'est pas présent.
	 * @see PointsAbstractController::indexAction()
	 */
	public function indexAction()
	{
		if(empty($this->getMembre()->Siret))
		{
			$this->View->Infos = 'Attention ! Vous ne pouvez pas retirer d\'argent tant que vous ne nous avez pas <a href="/correcteur/options/compte">indiqué votre SIRET</a>.';
		}
		
		parent::indexAction();
	}
}