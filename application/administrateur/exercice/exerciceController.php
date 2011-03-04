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
	
	public function indexActionWd()
	{
		$this->View->setTitle(
			'Exercice «&nbsp;' . $this->Exercice->Titre . '&nbsp;»',
			"Informations sur l'exercice."
		);
	}
	
	/**
	 * Chat de l'exercice
	 * @see ExerciceAbstractController::faqActionWd()
	 */
	public function faqActionWd()
	{
		//Lest tests d'accès sont effectués dans la méthode parente	
		$this->View->setTitle(
			"Chat de l'exercice",
			"Ne s'exprimer sur cette page qu'au strict minimum. C'est le correcteur qui devrait se charger de répondre aux questions..."
		);
		
		//Enregistrer et récupérer les données
		parent::faqActionWd();
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
					Event::dispatch(
						Event::ELEVE_EXERCICE_TERMINE,
						array(
							'Exercice' => $this->Exercice,
							'Correcteur' => $Correcteur,
							'Message' => 'la réclamation déposée par l\'élève est injustifiée'
						)
					);
					
					$this->View->setMessage('ok', "Décision correctement enregistrée. Dommage pour l'élève !");
					$this->redirect('/administrateur/reclamations');
				}
				else
				{
					/**
					 * Événements à dispatcher (Nom évènement => données)
					 * Le nom évènement correspond à l'une des constantes statiques de la classe Event
					 * Les données sont un tableau auquel les clés Exercice, Pourcentage, PaiementCorrecteur, PaiementEleve, Eleve et Correcteur seront automatiquement rajoutées.
					 * @var array
					 */
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
						$ToDispatch[Event::MEMBRE_EXERCICE_COMPENSATION] = array();
					}
					elseif($Pourcentage == 1)
					{
						//Remboursement total de l'élève
						$PaiementEleve = $this->Exercice->pricePaid();
						$PaiementCorrecteur = 0;
						$MessageEleve = 'Remboursement total';
						$MessageExercice = 'Remboursement de l\'exercice à l\'élève.';
						$MessageAdministrateur = 'Le remboursement a été effectué dans son intégralité.';
						$ToDispatch[Event::MEMBRE_EXERCICE_REMBOURSEMENT] = array();
					}
					elseif($Pourcentage > 1 && $Pourcentage <= MAX_REMBOURSEMENT / 100)
					{
						//Remboursement + dédommagement
						$PaiementEleve = (int) round($this->Exercice->pricePaid() * $Pourcentage);
						$PaiementCorrecteur = 0;
						$MessageEleve = 'Remboursement et dédommagement (' . $PourcentageAffichable . '%)';
						$MessageExercice = 'Dédommagement de l\'élève.';
						$MessageAdministrateur = 'Le dédommagement a été effectué. Dommage pour nous :(';
						$ToDispatch[Event::MEMBRE_EXERCICE_DEDOMMAGEMENT] = array();
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
					
					//Envoyer les évènements
					foreach($ToDispatch as $Event => $Params)
					{
						$Params['Exercice'] = $this->Exercice;
						$Params['Eleve'] = $Eleve;
						$Params['Correcteur'] = $Correcteur;
						$Params['Pourcentage'] = $PourcentageAffichable;
						$Params['PaiementEleve'] = $PaiementEleve;
						$Params['PaiementCorrecteur'] = $PaiementCorrecteur;
						
						Event::dispatch($Event, $Params);
					}
					
					//Gestion du blocage du correcteur
					if(isset($_POST['bloquer']))
					{
						$Correcteur->setAndSave(array('Statut' => 'BLOQUE'));
						Event::dispatch(Event::MEMBRE_BLOQUE, array('Membre' => $Membre));
						$MessageAdministrateur .= '<br />Le correcteur a été bloqué.';
					}
					
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