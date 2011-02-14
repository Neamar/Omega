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
		
		if(isset($_POST['remboursement-exercice']))
		{
			//Demande de remboursement sur un exercice non remboursable
			//On ne peut pas utiliser empty qui renvoie true sur 0.
			if(isset($_POST['remboursement'][0]) && $this->View->EstRemboursable)
			{
				$PourcentageAffichable = intval($_POST['remboursement']);
				$Pourcentage = $PourcentageAffichable / 100;
				$Correcteur = $this->Exercice->getCorrecteur();
				$Eleve = $this->Exercice->getEleve();
				$Banque = Membre::getBanque();
				
				//Propriétés à mettre à jour sur l'exercice
				$ToUpdate = array(
					'Remboursement' => $PourcentageAffichable
				);		
				if($Pourcentage == 0)
				{
					//Rejet de la demande de renseignement
					$this->Exercice->closeExercice('Rejet de la réclamation', $this->getMembre(), $ToUpdate);
					//TODO: dispatch
					
					$this->View->setMessage('ok', "Décision correctement enregistrée. Dommage pour l'élève !");
					$this->redirect('/administrateur/reclamations');
				}
				else
				{
					//Événements à dispatcher (Nom évènement => données)
					$ToDispatch = array();
					
					if($Pourcentage > 0 && $Pourcentage < 1)
					{
						//Remboursement de l'élève au détriment du correcteur.
						$InversePourcentage = 1 - $Pourcentage;
						
						$PaiementCorrecteur = (int) round($this->Exercice->priceAsked() * $InversePourcentage);
						$PaiementEleve = (int) round($this->Exercice->pricePaid() * $Pourcentage);
						$MessageCorrecteur = 'Paiement partiel (' . ($InversePourcentage*100) . '%)';
						$MessageEleve = 'Remboursement partiel (' . $PourcentageAffichable . '%)';
						$MessageExercice = 'Remboursement partiel de l\'élève et paiement du correcteur';
						$MessageAdministrateur = 'Les échanges de monnaie ont été effectués.';
					}
					elseif($Pourcentage == 1)
					{
						//Remboursement total de l'élève
						$PaiementEleve = $this->Exercice->pricePaid();
						$PaiementCorrecteur = 0;
						$MessageEleve = 'Remboursement total';
						$MessageExercice = 'Remboursement de l\'exercice à l\'élève.';
						$MessageAdministrateur = 'Le remboursement a été effectué dans son intégralité.';
					}
					elseif($Pourcentage > 1 && $Pourcentage <= MAX_REMBOURSEMENT / 100)
					{
						//Remboursement + dédommagement
						$PaiementEleve = (int) round($this->Exercice->pricePaid() * $Pourcentage);
						$PaiementCorrecteur = 0;
						$MessageEleve = 'Remboursement et dédommagement (' . $PourcentageAffichable . '%)';
						$MessageExercice = 'Dédommagement de l\'élève.';
						$MessageAdministrateur = 'Le dédommagement a été effectué. Dommage pour nous :(';
					}
					else
					{
						throw new Exception('Valeur de remboursement non autorisée.');
						exit();
					}
					
					Sql::start();
					$OK = true;
					
					if($PaiementCorrecteur > 0)
					{
						$OK = $OK && $Correcteur->credit($PaiementCorrecteur, $MessageCorrecteur . 'pour l\'exercice «&nbsp;' . $this->Exercice->Titre . '&nbsp;»', $this->Exercice);
						$OK = $OK && $Banque->debit($PaiementCorrecteur, $MessageCorrecteur, $this->Exercice);
					}
					
					$OK = $OK && $Eleve->credit($PaiementEleve, $MessageEleve . ' pour l\'exercice «&nbsp;' . $this->Exercice->Titre . '&nbsp;»', $this->Exercice);
					$OK = $OK && $Banque->debit($PaiementEleve, $MessageEleve, $this->Exercice);
					
					if(!$OK)
					{
						Sql::rollback();
						throw new Exception('Erreur critique à la résolution des contraintes de remboursement.');
						exit();
					}
					
					$this->Exercice->setStatus('REMBOURSE', $this->getMembre(), $MessageExercice, $ToUpdate);
					Sql::commit();
					
					$this->View->setMessage('ok', $MessageAdministrateur);
					$this->redirect('/administrateur/reclamations');
				}//Fin pourcentage == 0
			}//Fin isset($_POST['remboursement'][0]) && $this->View->EstRemboursable
		}//isset($_POST['remboursement-exercice'])
		
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