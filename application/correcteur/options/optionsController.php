<?php
/**
 * optionsController.php - 17 janv. 2011
 * 
 * Gestion des options du compte correcteur.
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
 * Les différentes pages d'option
 *
 * @category Controller
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 */
class Correcteur_OptionsController extends OptionsAbstractController
{
	/**
	 * Page d'options globales.
	 */
	public function indexAction()
	{
		$this->View->setTitle(
			'Options correcteur',
			'Modifiez ici vos informations de compte, ou vos capacités pour chaque matière.'
		);
		
		$this->View->Compte = $this->concat('/correcteur/options/compte');
		$this->View->Matieres = $this->concat('/correcteur/options/matieres');
	}
	
	/**
	 * Page d'options pour la mise à jour du compte
	 */
	public function compteAction()
	{
		$this->View->setTitle(
			'Modifications du compte',
			"Cette page vous permet de modifier les informations de votre compte.<br />
Si vous ne l'avez pas encore fait, vous pourrez aussi spécifier votre numéro de SIRET."
		);

		if(isset($_POST['edition-compte']))
		{
			$ToUpdate = $this->editAccount($_POST, $_SESSION['Correcteur']);
			if($ToUpdate == FAIL)
			{
				//La mise à jour ne doit pas être effectuée.
				//Le message a été défini par editAccount.
			}
			elseif(!Validator::phone($_POST['telephone']))
			{
				$this->View->setMessage('error', "Vous devez indiquer un numéro de téléphone valide (0X XX XX XX XX).");
			}
			elseif(!empty($_POST['siret']) && !is_null($_SESSION['Correcteur']->Siret))
			{
				$this->View->setMessage('error', "Impossible de redéfinir le SIRET.");
			}
			elseif(!empty($_POST['siret']) && !Validator::siret($_POST['siret']))
			{
				$this->View->setMessage('error', "Numéro de SIRET invalide. Si vous n'avez pas encore de SIRET, laissez le champ vide.");
			}
			else
			{
				if($_POST['telephone'] != $_SESSION['Correcteur']->Telephone)
				{
					$ToUpdate['Telephone'] = preg_replace('`[^0-9]`', '', $_POST['telephone']);
				}
				if(!empty($_POST['siret']))
				{
					$ToUpdate['Siret'] = $_POST['siret'];
				}
				
				//Ne commiter que s'il y a des modifications.
				if(empty($ToUpdate))
				{
					$this->View->setMessage('warning', "Aucune modification.");
				}
				else
				{
					$_SESSION['Correcteur']->setAndSave($ToUpdate);
					$this->View->setMessage('ok', "Modifications du compte enregistrées.");
				}
			}
		}
	}
	
	/**
	 * Page d'options pour la mise à jour des compétences
	 */
	public function matieresAction()
	{
		$this->View->setTitle(
			'Modifications des compétences',
			"Cette page vous permet de modifier vos compétences ; et ainsi de filtrer les exercices pour n'afficher que ceux qui vous correspondent."
		);
		$this->View->setSeelink('/correcteur/options/matieres_rapide', 'Faciliter la saisie des compétences ?');
		
		$this->View->addScript();
		//Charger la liste des matières :
		$Matieres = SQL::queryAssoc('SELECT Matiere FROM Matieres', 'Matiere', 'Matiere');
		
		//Charger la liste des classes :
		$this->View->Classes = SQL::queryAssoc('SELECT Classe, DetailsClasse FROM Classes ORDER BY Classe DESC', 'Classe', 'DetailsClasse');
		
		if(isset($_POST['edition-competences']))
		{
			Sql::query('DELETE FROM Correcteurs_Capacites WHERE Correcteur = ' . $_SESSION['Correcteur']->getFilteredId());
			foreach($Matieres as $Matiere)
			{
				$ID = preg_replace('`[^-a-zA-Z]`', '', $Matiere);
				if(!empty($_POST['check_' . $ID]))
				{
					$Debut = intval($_POST['start_' . $ID]);
					$Fin = intval($_POST['end_' . $ID]);
					
					if($Debut < $Fin)
					{
						$this->View->setMessage('error', "Impossible de commencer à être compétent après avoir fini de l'être ! (Début > Fin pour la matière " . $Matiere . ')');
						break;
					}
					else
					{
						$ToInsert = array
						(
							'Correcteur' => $_SESSION['Correcteur']->getFilteredId(),
							'Matiere' => $Matiere,
							'Commence' => $Debut,
							'Finit' => $Fin,
						);
						
						Sql::insert('Correcteurs_Capacites', $ToInsert);
					}
				}
			}
			
			if(!$this->View->issetMeta('message'))
			{
				$this->View->setMessage('ok', "Compétences enregistrées.");
			}
		}
		
		$this->View->Matieres = $Matieres;
		$this->View->Defaults = SQL::queryAssoc('SELECT Matiere, Commence, Finit FROM Correcteurs_Capacites WHERE Correcteur = ' . $_SESSION['Correcteur']->getFilteredId(), 'Matiere');
	}
	
	/**
	 * Page d'options pour la mise à jour (rapide) des compétences
	 */
	public function matieres_RapideAction()
	{
		$this->View->setTitle(
			'Modifications rapide des compétences',
			"Cette page vous permet de modifier facilement vos compétences."
		);
		$this->View->setSeelink('/correcteur/options/matieres', 'Plus de précision pour vos compétences ?');
		
		$this->View->addScript('/public/js/correcteur/options/matieres.js');
		//Charger la liste des matières :
		$Matieres = SQL::queryAssoc('SELECT Matiere FROM Matieres', 'Matiere', 'Matiere');
		
		//Charger la liste des classes :
		$this->View->Classes = SQL::queryAssoc('SELECT Classe, DetailsClasse FROM Classes ORDER BY Classe DESC', 'Classe', 'DetailsClasse');
		

		if(isset($_POST['edition-competences-rapide']))
		{
			Sql::query('DELETE FROM Correcteurs_Capacites WHERE Correcteur = ' . $_SESSION['Correcteur']->getFilteredId());
			$Debut = intval($_POST['start_all']);
			$Fin = intval($_POST['end_all']);
			
			if($Debut < $Fin)
			{
				$this->View->setMessage('error', "Impossible de commencer à être compétent après avoir fini de l'être ! (Début > Fin)");
			}
			else
			{
				foreach($Matieres as $Matiere)
				{
					$ID = preg_replace('`[^-a-zA-Z]`', '', $Matiere);
					if(!empty($_POST['check_' . $ID]))
					{
						$ToInsert = array
						(
							'Correcteur' => $_SESSION['Correcteur']->getFilteredId(),
							'Matiere' => $Matiere,
							'Commence' => $Debut,
							'Finit' => $Fin,
						);
						
						Sql::insert('Correcteurs_Capacites', $ToInsert);
					}
				}
			}
			
			if(!$this->View->issetMeta('message'))
			{
				$this->View->setMessage('ok', "Compétences enregistrées.");
			}
		}
		
		$this->View->Matieres = $Matieres;
		//Charger les compétences déjà définies :
		$this->View->Defaults = SQL::queryAssoc('SELECT Matiere, Commence, Finit FROM Correcteurs_Capacites WHERE Correcteur = ' . $_SESSION['Correcteur']->getFilteredId(), 'Matiere');
		//Et en charger une qui servira de base :
		$this->View->Competences = Sql::singleQuery('SELECT Commence, Finit FROM Correcteurs_Capacites WHERE Correcteur = ' . $_SESSION['Correcteur']->getFilteredId() . ' LIMIT 1');
		
		if(!is_null($this->View->Competences) && !$this->View->issetMeta('message'))
		{
			$this->View->setMessage('warning', "Attention ! En utilisant cette page, vous perdrez aussi les <a href=\"/correcteur/options/matieres\">modifications plus avancées</a> que vous auriez pu apporter.");
		}
	}
}