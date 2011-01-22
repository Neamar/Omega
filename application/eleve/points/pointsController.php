<?php
/**
 * pointsController.php - 22 janv. 2011
 * 
 * Gestion des points du compte de l'élève
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
class Eleve_PointsController extends PointsAbstractController
{
	/**
	 * Retrait de points
	 * @see PointsAbstractController::retraitAction()
	 */
	public function retraitAction()
	{
		$this->View->Message = "Vous souhaitez reconvertir vos points en euros ?<br />
		Il vous suffit d'indiquer votre Relevé d'Identité Bancaire et le montant en points que vous souhaitez récupérer.<br />
		Attention, selon le service utilisé, vous ne récupérerez pas forcément la somme initiale.";
		
		parent::retraitAction();
	}
}