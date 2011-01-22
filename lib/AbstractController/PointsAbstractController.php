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
			elseif(!isset($_POST['type']) || !in_array($_POST['type'], array('rib', 'paypal')))
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
			elseif($_POST['type'] == 'paypal' && strlen($_POST['paypal']) > 29)
			{
				$this->View->setMessage("error", "Votre adresse paypal ne doit pas dépasser 30 caractères.");
			}
			else
			{
				if($_POST['type'] == 'paypal')
				{
					$Ordre = $_POST['paypal'];
				}
				else
				{
					$Ordre = $_POST['rib-banque'] . '-' . $_POST['rib-guichet'] . '-' . $_POST['rib-compte'] . '-' . $_POST['rib-cle'];
				}
				
				Sql::start();
				if(!$Membre->debit($_POST['retrait'], 'Virement pour ' . $Ordre))
				{
					Sql::rollback();
					$this->View->setMessage('error', 'Impossible de débiter une telle somme. La transaction a été annulée');
				}
				else
				{
					$ToInsert = array(
						'Membre' => $Membre->getFilteredId(),
						'_Date' => 'NOW()',
						'Montant' => $_POST['retrait'],
						'Type' => strtoupper($_POST['type']),
						'Beneficiaire' => $Ordre,
						'Statut' => 'INDETERMINE'
					);
					
					if(!Sql::insert('Virements', $ToInsert))
					{
						Sql::rollback();
						$this->View->setMessage('error', "Impossible d'enregistrer la demande de virement. Ce problème devrait se résoudre de lui même sous peu.");
					}
					else
					{
						Sql::commit();
						
						$this->View->setMessage('info', "Nous avons bien reçu votre demande, nous la traiterons dans les plus brefs délais (usuellement dans la semaine).");
						$this->redirect('/' . $this->getModule() . '/points/');
					}
				}
			}
		}
		
	}
}