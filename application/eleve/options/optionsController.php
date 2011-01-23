<?php
/**
 * optionsController.php - 17 janv. 2011
 * 
 * Gestion des options du compte élève.
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
class Eleve_OptionsController extends OptionsAbstractController
{
	/**
	 * Page d'options globales.
	 */
	public function indexAction()
	{
		$this->View->setTitle(
			'Options élève',
			'Modifiez ici vos informations de compte.'
		);
		$this->View->Compte = $this->concat('/eleve/options/compte');
	}
	
	public function compteAction()
	{
		$this->View->setTitle(
			'Modifications du compte',
			'Cette page vous permet de modifier les informations de votre compte.'
		);

		//Charger la liste des classes pour le combobox :
		$this->View->Classes = SQL::queryAssoc('SELECT Classe, DetailsClasse FROM Classes ORDER BY Classe DESC', 'Classe', 'DetailsClasse');
		
		
		if(isset($_POST['edition-compte']))
		{
			$ToUpdate = $this->editAccount($_POST, $_SESSION['Eleve']);
			if($ToUpdate == FAIL)
			{
				//La mise à jour ne doit pas être effectuée.
				//Le message a été défini par editAccount.
			}
			elseif(!isset($this->View->Classes[$_POST['classe']]))
			{
				$this->View->setMessage('error', "Sélectionnez une classe dans la liste déroulante.");
			}
			else
			{
				if($_POST['classe'] != $_SESSION['Eleve']->Classe)
				{
					$ToUpdate['Classe'] = $_POST['classe'];
				}
				
				if($_POST['section'] != $_SESSION['Eleve']->Section)
				{
					$ToUpdate['Section'] = $_POST['section'];
				}
				
				//Ne commiter que s'il y a des modifications.
				if(empty($ToUpdate))
				{
					$this->View->setMessage('warning', "Aucune modification.");
				}
				else
				{
					$_SESSION['Eleve']->setAndSave($ToUpdate);
					$this->View->setMessage('ok', "Modifications du compte enregistrées.");
				}
			}
		}
	}
}