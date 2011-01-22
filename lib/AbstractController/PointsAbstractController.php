<?php
/**
 * PointsAbstractController.php - 22 janv. 2011
 * 
 * Contrôleur abstrait pour la gestion des points d'un compte.
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
 * Gestion des points
 *
 * @category Controller
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 */
abstract class PointsAbstractController extends AbstractController
{
	public function retraitAction()
	{
		$this->View->setTitle(
			'Retrait de points',
			'Retirez ici des points vers un compte bancaire ou paypal.'
		);
		
		/**
		 * Le membre qui tente de retirer des points
		 * 
		 * @var Membre
		 */
		$Membre = $_SESSION[ucfirst($_GET['module'])];
		
		if($Membre->getPoints() == 0)
		{
			$Message = 'Vous n\'avez aucun point à convertir.';
		}
		
		//Si erreur, notifier puis dégager
		if(isset($Message))
		{
			$this->View->setMessage("error", $Message);
			$this->redirect('/' . $_GET['module'] . '/points/');
		}
		
		if(isset($_POST['retrait-points']))
		{
			$_POST['retrait'] = intval($_POST['retrait']);
			
			if($_POST['retrait'] == 0)
			{
				$this->View->setMessage("error", "Valeur invalide ou nulle.");
			}
			elseif($_POST['retrait'] > $Membre->getPoints())
			{
				$this->View->setMessage("error", "Vous ne pouvez pas retirer autant !");
			}
			elseif(!isset($_POST['type']))
			{
				$this->View->setMessage("error", "Choisissez le type de virement.");
			}
			elseif($_POST['type'] == 'rib' && !Validator::rib(array('codebanque' => $_POST['rib-banque'], 'codeguichet' => $_POST['rib-guichet'], 'nocompte' => $_POST['rib-compte'], 'key' => $_POST['rib-cle'])))
			{
				$this->View->setMessage("error", "Numéro de RIB invalide.");
			}
			elseif($_POST['type'] == 'paypal' && !Validator::paypal($_POST['paypal']))
			{
				$this->View->setMessage("error", "Compte paypal invalide.");
			}
			else
			{
				//Sql::start();
				//$Membre->debit($_POST['retraitPoints'], "Virement.");
			}
		}
		
	}
}