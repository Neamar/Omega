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
				
				$ToUpdate = array(
					'Remboursement' => $PourcentageAffichable
				);
				
				if($Pourcentage == 0)
				{
					//Rejet de la demande de renseignement
					$this->Exercice->closeExercice('Rejet de la réclamation', $this->getMembre(), $ToUpdate);
					
					$this->View->setMessage('ok', "Décision correctement enregistrée. Dommage pour l'élève !");
					$this->redirect('/administrateur/reclamations');
				}
				elseif($Pourcentage > 0 && $Pourcentage < 1)
				{
					//Remboursement de l'élève au détriment du correcteur.
					$InversePourcentage = 1 - $Pourcentage;
					
					$PaiementCorrecteur = (int) round($this->Exercice->priceAsked() * $InversePourcentage);
					$PaiementEleve = (int) round($this->Exercice->pricePaid() * $Pourcentage);

					Sql::start();
					$Correcteur->credit($PaiementCorrecteur, 'Paiement partiel (' . ($InversePourcentage*100) . '%) pour l\'exercice «&nbsp;' . $this->Exercice->Titre . '&nbsp;»', $this->Exercice);
					$Banque->debit($PaiementCorrecteur, 'Paiement partiel exercice.', $this->Exercice);
					
					$Eleve->credit($PaiementEleve, 'Remboursement partiel (' . $PourcentageAffichable . '%) pour l\'exercice «&nbsp;' . $this->Exercice->Titre . '&nbsp;»', $this->Exercice);
					$Banque->debit($PaiementEleve, 'Remboursement partiel exercice.', $this->Exercice);
					
					$this->Exercice->setStatus('REMBOURSE', $this->getMembre(), "Remboursement partiel et paiement du correcteur.", $ToUpdate);
					Sql::commit();
					
					$this->View->setMessage('ok', "Les échanges de monnaie ont été effectués.");
					$this->redirect('/administrateur/reclamations');
				}
				elseif($Pourcentage == 1)
				{
					//Remboursement total de l'élève
					Sql::start();
					$PaiementEleve = $this->Exercice->pricePaid();
					$Eleve->credit($PaiementEleve, 'Remboursement total pour l\'exercice «&nbsp;' . $this->Exercice->Titre . '&nbsp;»', $this->Exercice);
					$Banque->debit($PaiementEleve, 'Remboursement total exercice.', $this->Exercice);
					
					$this->Exercice->setStatus('REMBOURSE', $this->getMembre(), "Remboursement total", $ToUpdate);
					Sql::commit();
					
					$this->View->setMessage('ok', "Le remboursement a été effectué.");
					$this->redirect('/administrateur/reclamations');
				}
				elseif($Pourcentage > 1 && $Pourcentage <= MAX_REMBOURSEMENT / 100)
				{
					//Remboursement + dédommagement
					Sql::start();
					$PaiementEleve = (int) round($this->Exercice->pricePaid() * $Pourcentage);
					$Eleve->credit($PaiementEleve, 'Remboursement et dédommagement (' . $PourcentageAffichable . '%) pour l\'exercice «&nbsp;' . $this->Exercice->Titre . '&nbsp;»', $this->Exercice);
					$Banque->debit($PaiementEleve, 'Remboursement et dédommagement exercice.', $this->Exercice);
					
					$this->Exercice->setStatus('REMBOURSE', $this->getMembre(), "Remboursement et dédommagement (' . $Pourcentage . '%)", $ToUpdate);
					Sql::commit();
					
					$this->View->setMessage('ok', "Le dédommagement a été effectué. Dommage pour nous :'(");
					$this->redirect('/administrateur/reclamations');
				}
			}
		}
		
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