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
	/**
	 * Le membre ayant demandé le chargement de la page
	 *
	 * @var Membre
	 */
	protected $Membre;

	/**
	 * Dévie automatiquement la vue vers une solution générique.
	 *
	 * @param string $Module
	 * @param string $Controller
	 * @param string $View
	 * @param string $Data
	 */
	public function __construct($Module, $Controller, $View, $Data)
	{
		parent::__construct($Module, $Controller, $View, $Data);
	}
	/**
	 * Index du contrôleur de points
	 */
	public function indexAction()
	{
		$this->View->setTitle(
			'Informations solde',
			'Cette page liste les différentes informations disponibles concernant vos points.'
		);
		
		$this->deflectView(OO2FS::genericViewPath('points/index'));
	}
	
	/**
	 * Opération de retrait de points
	 */
	public function retraitAction()
	{
		$this->View->setTitle(
			'Retrait de points',
			'Retirez ici des points vers un compte bancaire ou paypal.'
		);
		$this->View->addScript('/public/js/membre/points/retrait.js');

		$this->View->Membre = $this->getMembre();

		if($this->getMembre()->getPoints() == 0)
		{
			$this->View->setMessage('error', 'Vous n\'avez aucun point à convertir.');
			$this->redirect('/' . $this->getModule() . '/points/');
		}
		
		$DernierVirement = strtotime(
			Sql::singleColumn(
				'SELECT Date
				FROM Virements
				WHERE Membre = ' . $this->getMembre()->getFilteredId() . '
				ORDER BY ID DESC
				LIMIT 1',
				'Date'
			)
		);
		if($DernierVirement != false && $DernierVirement > time() - DELAI_VIREMENT * 24 * 3600)
		{
			$this->View->setMessage(
				"error",
				"Impossible d'effectuer plus d'un virement tous les " . DELAI_VIREMENT . ' jours. Dernière demande de virement : ' . date('d/m/Y à H\h', $DernierVirement),
				'correcteur/retrait'
			);
			$this->redirect('/' . $this->getModule() . '/points/');
		}

		if(isset($_POST['retrait-points']))
		{
			$_POST['retrait'] = intval($_POST['retrait']);
			if(empty($_POST['password']))
			{
				$this->View->setMessage('error', "Pour des raisons de sécurité, vous devez fournir votre mot de passe.");
			}
			elseif(!$this->getMembre()->comparePass(Util::hashPass($_POST['password'])))
			{
				$this->View->setMessage('error', "Mot de passe non valide.");
			}
			elseif($_POST['retrait'] == 0)
			{
				$this->View->setMessage('error', "Valeur à retirer invalide ou nulle.");
			}
			elseif($_POST['retrait'] > $this->getMembre()->getPoints())
			{
				$this->View->setMessage('error', "Vous ne pouvez pas retirer autant !");
			}
			elseif(!isset($_POST['type']) || !in_array($_POST['type'], array('rib', 'paypal')))
			{
				$this->View->setMessage('error', "Choisissez le type de virement.");
			}
			elseif($_POST['type'] == 'rib' && !Validator::rib(array('codebanque' => $_POST['rib-banque'], 'codeguichet' => $_POST['rib-guichet'], 'nocompte' => $_POST['rib-compte'], 'key' => $_POST['rib-cle'])))
			{
				$this->View->setMessage('error', "Numéro de RIB invalide.");
			}
			elseif($_POST['type'] == 'paypal' && !Validator::paypal($_POST['paypal']))
			{
				$this->View->setMessage('error', "Compte paypal invalide.");
			}
			elseif($_POST['type'] == 'paypal' && strlen($_POST['paypal']) > 29)
			{
				$this->View->setMessage('error', "Votre adresse paypal ne doit pas dépasser 30 caractères.");
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

				//Début de la transaction SQL
				Sql::start();
				if(!$this->getMembre()->debit($_POST['retrait'], 'Virement pour ' . $Ordre))
				{
					Sql::rollback();
					$this->View->setMessage('error', 'Impossible de débiter une telle somme. La transaction a été annulée');
				}
				else
				{
					$ToInsert = array(
						'Membre' => $this->getMembre()->getFilteredId(),
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

						Event::dispatch(
							Event::MEMBRE_POINTS_RETRAIT,
							array(
								'montant' => $_POST['retrait'] / EQUIVALENCE_POINT,
								'type' => $_POST['type'],
								'ordre' => $Ordre,
								'ip' => $_SERVER['REMOTE_ADDR'],
								'membre' => $this->getMembre()
							)
						);
						
						$this->View->setMessage('info', "Nous avons bien reçu votre demande, nous la traiterons dans les plus brefs délais (usuellement dans la semaine).");
						$this->redirect('/' . $this->getModule() . '/points/');
					}
				}
			}
		}//Fin test $_POST
		
		$this->deflectView(OO2FS::genericViewPath('points/retrait'));
	}

	/**
	 * Liste les actions sur le compte.
	 */
	public function _actionsAction()
	{
		$Query = '
			SELECT DATE_FORMAT(Date,"%d/%m/%y à %Hh"), Action, Delta
			FROM Logs
			WHERE Membre = ' . $this->getMembre()->getFilteredID() . '
			ORDER BY Logs.Date DESC';

		$ResultatsSQL = Sql::query($Query);
		$Resultats = array();
		$Points = $this->getMembre()->getPoints();
		
		//Afficher plus d'enregistrements.
		//Comme on ne passe pas par le helper ->ajax, on est obligé de faire le boulot à la main et de le simuler.
		//FIXME : on peut optimiser la consommation mémoire en utilisant un SQL_CALC_FOUND_ROWS si nécessaire.
		$Limit = isset($_POST['limit'])?intval($_POST['limit']):AJAX_LIMITE;

		while($Resultat = mysql_fetch_row($ResultatsSQL))
		{
			$Delta = $Resultat[2];
			if($Delta > 0)
			{
				$Resultat[2] = '<small style="color:green;">' . $Delta . '</small>';
			}
			else
			{
				$Resultat[2] = '<small style="color:red;">' . $Delta . '</small>';
			}
			$Resultat[2] = $this->View->Points($Points) . ' (' . $Resultat[2] . ')';
			$Points -= $Delta; // Remonter dans le temps.

			$Resultats[] = $Resultat;
			
			if(count($Resultats) >= $Limit)
			{
				$Resultats[] = '+';
				break;
			}
		}

		$this->json($Resultats);
	}
}