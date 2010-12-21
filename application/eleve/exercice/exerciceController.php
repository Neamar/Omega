<?php
/**
 * exerciceController.php - 26 oct. 2010
 * 
 * Actions de base pour un élève.
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
 * Contrôleur d'index du module élève.
 * 
 * @category Controller
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 *
 */
class Eleve_ExerciceController extends ExerciceAbstractController
{
	/**
	 * Page d'accueil du module ; connecter le membre si nécessaire, puis afficher les infos du compte et les liens utiles. 
	 * 
	 */
	public function indexAction()
	{
		$this->View->setTitle('Accueil exercice');
	}
	
	/**
	 * Page d'accueil d'un exercice.
	 * 
	 */
	public function indexAction_wd()
	{
		$this->View->setTitle('Accueil exercice #');
	}
	
	/**
	 * Création d'un nouvel exercice
	 */
	public function creationAction()
	{
		$this->View->setTitle('Création d\'un exercice');
		$this->View->addScript('/public/js/eleve/exercice/creation.js');
		
		//Charger la liste des matières pour le combobox :
		$this->View->Matieres = SQL::queryAssoc('SELECT Matiere FROM Matieres', 'Matiere','Matiere');
		
		//Charger la liste des classes pour le combobox :
		$this->View->Classes = SQL::queryAssoc('SELECT ID, Nom FROM Classes ORDER BY ID DESC', 'ID', 'Nom');
		
		//Charger la liste des types d'exercices pour le combobox :
		$this->View->Types = SQL::queryAssoc('SELECT Type, Details FROM Types', 'Type', 'Details');
		
		//Créer la liste des demandes supportées :
		$this->View->Demandes = array(
			'COMPLET'=>'Corrigé complet',
			'AIDE'=>'Pistes de résolution',
		);
		
		if(isset($_POST['creation-exercice']))
		{
			if(!isset($this->View->Classes[$_POST['classe']]))
			{
				$this->View->setMessage("error", "Cette classe n'existe pas.");
			}
			elseif(!isset($this->View->Matieres[$_POST['matiere']]))
			{
				$this->View->setMessage("error", "Cette matière n'existe pas.");
			}
			elseif(!isset($this->View->Types[$_POST['type']]))
			{
				$this->View->setMessage("error", "Ce type d'exercice n'existe pas.");
			}
			elseif(!isset($this->View->Demandes[$_POST['demande']]))
			{
				$this->View->setMessage("error", "Cette demande n'existe pas.");
			}
			elseif(!is_numeric($_POST['rendu_heure'])
				|| $_POST['rendu_heure'] < 0
				|| $_POST['rendu_heure'] > 23)
			{
				$this->View->setMessage("error", "Heure de rendu invalide.");
			}
			elseif(!is_numeric($_POST['annulation_heure'])
				|| $_POST['annulation_heure'] < 0
				|| $_POST['annulation_heure'] > 23)
			{
				$this->View->setMessage("error", "Heure d'annulation invalide.");
			}
			elseif(!preg_match('#^([0-3]?[0-9])/([0-1]?[0-9])/(20[0-1][0-9])$#', $_POST['rendu_date'], $_POST['rendu_array'])
				|| (($_POST['rendu_ts'] = mktime($_POST['rendu_heure'], 0, 0, $_POST['rendu_array'][2], $_POST['rendu_array'][1], $_POST['rendu_array'][3])) === false))
			{
				$this->View->setMessage("error", "Date de rendu invalide.");
			}
			elseif(!preg_match('#^([0-3]?[0-9])/([0-1]?[0-9])/(20[0-1][0-9])$#', $_POST['annulation_date'], $_POST['annulation_array'])
				|| (($_POST['annulation_ts'] = mktime($_POST['annulation_heure'], 0, 0, $_POST['annulation_array'][2], $_POST['annulation_array'][1], $_POST['annulation_array'][3])) === false))
			{
				$this->View->setMessage("error", "Date d'annulation invalide.");
			}
			elseif($_POST['rendu_ts'] < time() + 2*3600)
			{
				$this->View->setMessage("error", "Le délai spécifié pour le rendu est trop court ou dans le passé. Nous ne faisons pas machine à remonter le temps, désolé.");
			}
			elseif($_POST['rendu_ts'] < $_POST['annulation_ts'])
			{
				$this->View->setMessage("error", "La date de rendu doit être postérieure à la date d'annulation automatique.");
			}
			elseif(!is_numeric($_POST['auto_accept']) || $_POST['auto_accept'] < 0)
			{
				$this->View->setMessage("error", "La valeur spécifiée pour l'acceptation automatique doit être numérique positive.");
			}
			elseif($_POST['auto_accept'] > $_SESSION['Eleve']->getPoints())
			{
				$this->View->setMessage("error", "La valeur spécifiée pour l'acceptation automatique est supérieure à votre solde. Valeur maximum : " . $_SESSION['Eleve']->getPoints());
			}
			else
			{
				$ToInsert = array
				(
					'Hash' => Exercice::generateHash(),
					'Createur' => $_SESSION['Eleve']->ID,
					'_IP' => 'INET_ATON("' . Sql::escape($_SERVER['REMOTE_ADDR']) . '")',
					'_Creation' => 'NOW()',
					'TimeoutEleve' => Sql::getDate($_POST['annulation_ts']),
					'Expiration' => Sql::getDate($_POST['rendu_ts']),
					'Matiere' => $_POST['matiere'],
					'Classe' => $_POST['classe'],
					'Section' => substr($_POST['section'], 0, 20),
					'Type' => $_POST['type'],
					'Demande' => $_POST['demande'],
					'InfosEleve' => $_POST['infos'],
					'Modificateur' => $_SESSION['Eleve']->getRaise(),
				);
				
				if($_POST['auto_accept'] > 0)
				{
					$ToInsert['Autoaccept'] = $_POST['auto_accept'];
				}
				
				if(Sql::insert('Exercices', $ToInsert))
				{
					$this->redirect('/eleve/exercice/ajout/' . $ToInsert['Hash']);
				}
				else
				{
					$this->View->setMessage("error", "Impossible de créer cet exercice. Veuillez réessayer dans quelques minutes.");
				}
			}
		}
	}
	
	/**
	 * Ajoute des fichiers à un exercice si possible.
	 */
	public function ajoutAction_wd()
	{
		$this->View->setTitle("Ajout de fichiers à l'exercice ");
	}
}