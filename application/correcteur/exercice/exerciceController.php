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
	 * ATTENTE_CORRECTEUR => ATTENTE_ELEVE
	 */
	public function reservationActionWd()
	{
		$this->canAccess(array('ATTENTE_CORRECTEUR'), 'Vous ne pouvez plus réserver cet exercice !');
		
		$this->View->setTitle(
			'Réservation de « ' . $this->Exercice->Titre . ' »',
			"Cette page permet de consulter un exercice avant de le réserver en indiquant votre prix."
		);
		
		$this->View->addScript();
		
		if(isset($_POST['reservation-exercice']))
		{
			$_POST['prix'] = intval($_POST['prix']);
			if($_POST['prix'] == 0)
			{
				$this->View->setMessage('error', 'Désolé, nous ne faisons pas dans le bénévolat. Indiquez une valeur supérieure à 0.');
			}
			elseif($_POST['prix'] > MAX_SOMME)
			{
				$this->View->setMessage('error', "C'est une sacrée somme ! N'y aurait-il pas eu une erreur de saisie ? Somme max : " . MAX_SOMME);
			}
			elseif(!is_numeric($_POST['annulation_heure'])
				|| $_POST['annulation_heure'] < 0
				|| $_POST['annulation_heure'] > 23)
			{
				$this->View->setMessage("error", "Heure d'annulation invalide.");
			}
			elseif(!preg_match('#^([0-3]?[0-9])/([0-1]?[0-9])/(20[0-1][0-9])$#', $_POST['annulation_date'], $_POST['annulation_array'])
				|| (($_POST['annulation_ts'] = mktime($_POST['annulation_heure'], 0, 0, $_POST['annulation_array'][2], $_POST['annulation_array'][1], $_POST['annulation_array'][3])) === false))
			{
				$this->View->setMessage("error", "Date d'annulation invalide.");
			}
			elseif($_POST['annulation_ts'] <= time())
			{
				$this->View->setMessage("error", "La date d'annulation doit être dans le futur ! Les travaux prémonitoires ne sont pas supportés ici.");
			}
			elseif($_POST['annulation_ts'] >= strtotime($this->Exercice->Expiration) - 3600)
			{
				$this->View->setMessage("error", "La date d'expiration doit dépasser d'au moins une heure la date d'annulation !");
			}
			elseif($this->Exercice->Statut != 'ATTENTE_CORRECTEUR')
			{
				$this->View->setMessage('error', "Désolé, cet exercice n'est plus disponible à la réservation. Soyez plus rapide la prochaine fois :)");
			}
			else
			{
				$ToUpdate = array(
					'Correcteur' => $_SESSION['Correcteur']->ID,
					'Enchere' => $_POST['prix'],
					'TimeoutCorrecteur' => Sql::getDate($_POST['annulation_ts']),
					'InfosCorrecteur' => $_POST['infos'],
				);
				
				$this->Exercice->setStatus('ATTENTE_ELEVE', $_SESSION['Correcteur'], 'Proposition correcteur pour ' . $_POST['prix'] . ' points.', $ToUpdate);
				
				//Préparer l'envoi du mail.
				$Eleve = $this->Exercice->getEleve();
				$Datas = array(
					'mail' => $Eleve->Mail,
					'titre' => $this->Exercice->Titre,
					'hash' => $this->Exercice->Hash,
					'prix' => $_POST['prix'],
				);
				External::templateMail($Eleve->Mail, '/eleve/proposition', $Datas);

				$this->View->setMessage("info", "Vous avez fait votre proposition ! Vous serez informés par mail de son résultat.");
				$this->redirect('/correcteur/');
			}
		}
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