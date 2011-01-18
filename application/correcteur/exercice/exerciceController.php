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
	 * Page d'index du module
	 */
	public function indexAction()
	{
		$this->View->setTitle(
			'Mes exercices',
			"Cette page permet de consulter d'un coup d'œil les dernières actions sur les exercices que vous avez réservé."
		);
		
		$this->View->ExercicesActifs = Sql::queryAssoc(
			'SELECT Hash, Titre, Expiration
			FROM Exercices
			WHERE Correcteur = ' . $_SESSION['Correcteur']->getFilteredId() . '
			AND Statut = "EN_COURS"
			ORDER BY Expiration',
			'Hash'
		);
		
	}
	
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
			elseif(!preg_match(Validator::DATE_REGEXP, $_POST['annulation_date'], $_POST['annulation_array'])
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

				Event::dispatch(
					Event::CORRECTEUR_EXERCICE_PROPOSITION,
					array(
						'Exercice' => $this->Exercice,
						'Prix' => $_POST['prix'],
					)
				);
				
				$this->View->setMessage("info", "Vous avez fait votre proposition ! Vous serez informés par mail de son résultat.");
				$this->redirect('/correcteur/');
			}
		}
	}
	
	public function envoiActionWd()
	{
		$this->View->setTitle(
			'Rédaction du corrigé de « ' . $this->Exercice->Titre . ' »',
			"Cette page permet de rédiger le corrigé d'un exercice."
		);
		
		if(isset($_POST['envoi-exercice']))
		{
			$Template = file_get_contents(DATA_PATH . '/layouts/template.tex');
			$Remplacements = array(
				'__TITRE__' => $this->Exercice->Titre,
				'__CONTENU__' => $_POST['corrige']
			);
			$Contenu = str_replace(array_keys($Remplacements), array_values($Remplacements), $Template);
			
			$CorrigeURL = PATH . '/public/exercices/' . $this->Exercice->LongHash . '/Corrige/head.tex';
			file_put_contents($CorrigeURL, $Contenu);
			
			unset($Template, $Contenu);
			
			$Erreurs = $this->compileTex($CorrigeURL);
			
			if(empty($Erreurs))
			{
				$this->View->setMessage('info', 'Corrigé compilé avec succès.');
			}
			else
			{
				$this->View->setMessage('error', 'Des erreurs se sont produites, empêchant la compilation du document.');
				$this->View->Erreurs = $Erreurs;
			}
		}
	}
	
	/**
	 * Liste les actions des exercices
	 */
	public function _actionsAction()
	{
		//TODO : à compléter
		$this->ajax(
			'SELECT DATE_FORMAT(Date,"%d/%c/%y à %Hh"), CONCAT(Matiere, \' : <a href="/eleve/exercice/index/\', Hash, \'">\', Titre, \'</a>\'), Action
			FROM Exercices_Logs
			LEFT JOIN Exercices ON (Exercices_Logs.Exercice = Exercices.ID)
			LEFT JOIN Membres ON (Membres.ID = Exercices_Logs.Membre)
			WHERE 
			(
				Exercices.Correcteur = ' . $_SESSION['Correcteur']->getFilteredId() . '
				AND NouveauStatut NOT IN("VIERGE", "ATTENTE_CORRECTEUR")
				AND
				(
					Membres.Type = "ELEVE"
					OR
					Membres.ID = ' . $_SESSION['Correcteur']->getFilteredId() . '
				)
			)'
		);
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
	
	/**
	 * Compile un document LaTeX en PDF. Le PDF se trouve dans le même dossier que le fichier TeX.
	 * 
	 * @param string $URL le fichier TeX à compiler.
	 * 
	 * @return array une liste des erreurs rencontrées (ou un tableau vide si succès)
	 */
	protected function compileTex($URL)
	{
		$OutputDir = substr($URL, 0, strrpos($URL, '/'));
		exec('/usr/bin/pdflatex -halt-on-error -output-directory ' . escapeshellarg($OutputDir) . ' ' . escapeshellarg($URL), $Return, $Code);
		$Erreurs = array();
		foreach($Return as $Line)
		{
			if(isset($Line[0]) && $Line[0] == '!')
			{
				$Erreurs[] = substr($Line, 2);
			}
		}
		
		return $Erreurs;
	}
}