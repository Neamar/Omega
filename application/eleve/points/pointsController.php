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
	
	/**
	 * Opération d'ajouts de points
	 */
	public function ajoutAction()
	{
		$this->View->setTitle(
			'Ajout de points',
			'Sélectionnez la méthode avec laquelle vous souhaitez procéder à l\'ajout de points.'
		);
	}
	
	/**
	 * Retour de paypal
	 */
	public function ajout_paypalAction()
	{
		$this->View->setTitle(
			'Ajout de points par Paypal',
			'Vous allez ajouter des points sur votre compte eDevoir à partir d\'un compte Paypal.'
		);
		$this->View->addScript('/public/js/membre/points/ajout_paypal.js');
		
		$this->View->Membre = $this->getMembre();
	}
	
	public function ajout_paypalActionWd()
	{
		if($this->Data['data'] == 'ok' && isset($_POST['txn_id']))
		{
			$Montant = Sql::singleColumn('SELECT Montant FROM Entrees WHERE Membre = ' . $this->getMembre()->getFilteredId() . ' AND Hash = "' . Sql::escape($_POST['txn_id']) . '"', 'Montant');
			
			if(!is_null($Montant))
			{
				$this->View->setMessage('ok', "La transaction s'est correctement déroulée ! " . $Montant . ' points ont été ajoutés sur votre compte.');
			}
			else
			{
				$this->View->setMessage('warning', "La transaction semble s'être correctement terminée, mais nous n'avons pas encore reçu la confirmation de Paypal. Pas de panique, les points arrivent probablement sous peu ; surveillez votre compte !", 'index/contact');
			}
		}
		else
		{
			$this->View->setMessage('error', 'La transaction n\'a pas été effectuée.', 'index/contact');
		}
		
		$this->redirect('/' . $this->getModule() . '/points/');
	}
	
	public function ajout_monelibAction()
	{
		$this->View->setTitle(
			'Ajout de points par Monelib',
			'Vous allez ajouter des points sur votre compte eDevoir à partir de Monelib.'
		);
		$this->View->addScript('/public/js/membre/points/ajout_paypal.js');
		
		$Membre = $this->getMembre();
		
		$PosListe = array(
			1 => array(
				//https://www.monelib.com/repaymentViewer.php?ext_frm_access=42
				'pos' => 626936,
				'points' => 25
			),
			2 => array(
				//https://www.monelib.com/repaymentViewer.php?ext_frm_access=73
				'pos' => 162538,
				'points' => 30
			),
			3 => array(
				//https://www.monelib.com/repaymentViewer.php?ext_frm_access=123
				'pos' => 2021819,
				'points' => 45
			),
		);
			
			
		if(isset($_POST['code'], $_POST['palier']) && isset($PosListe[$_POST['palier']]))
		{
			$Zos = 2043;
			$Pos = $PosListe[$_POST['palier']]['pos'];
			$URL =  'http://www.monelib.com/accessScript/check.php?ext_frm_online=1&ext_frm_pos=' . $Pos . '&ext_frm_zos=' . $Zos . '&ext_frm_code0=' . $_POST['code'] . '&ext_frm_validateuse=1';

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $URL);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
			$data = curl_exec($ch);
			curl_close($ch);
			
			if($data == 'OK')
			{
				$Points = $PosListe[$_POST['palier']]['points'];
				
				//Tout semble en ordre, procéder au paiement
				Sql::start();
				
				//Donner l'argent au membre
				$Membre->credit((int) $Points, 'Ajout via monelib.');
				
				//Et logger l'entrée
				$ToLog = array(
					'code' => $_POST['code'],
					'zos' => $Zos,
					'pos' => $Pos,
					'palier' => $_POST['palier']
				);
				
				$ToInsert = array(
					'Membre' => $Membre->ID,
					'Montant' => $Points,
					'_Date' => 'NOW()',
					'Hash' => 'monelib_' . intval($_POST['palier']) . '_' . $_POST['code'],
					'Data' => serialize($ToLog)
				);
				
				if(Sql::insert('Entrees', $ToInsert))
				{
					Sql::commit();
					$this->View->setMessage('ok', "Transaction Monelib effectuée avec succès ! Votre compte a été crédité de " . $Points . ' points.');
					$this->redirect('/' . $this->getModule() . '/points/');
				}
				else
				{
					Sql::rollback();
					$this->View->setMessage('error', "Transaction Monelib abandonnée ; ce code a déjà été utilisé.", 'index/contact');
				}
			}
			else
			{
				$this->View->setMessage('error', "Code Monelib incorrect ou déjà utilisé.");
				unset($_POST['code'], $_POST['palier']);
			}
		}
		
		$this->View->Membre = $Membre;
		$this->View->addScript('/public/js/membre/points/ajout_monelib.js');
	}
}