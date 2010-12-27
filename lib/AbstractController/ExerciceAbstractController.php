<?php
/**
 * exerciceAbstractController.php - 26 oct. 2010
 * 
 * Actions de base communes aux contrôleurs d'exercices : récupération par l'ID, vérification des droits...
 * 
 * PHP Version 5
 * 
 * @category  Controller
 * @package   Root
 * @author    Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @copyright 2010 Matthieu Bacconnier
 * @license   Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link      http://eDevoir.com
 */

/**
 * Couche d'abstraction pour les exercices
 * 
 * @category Controller
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://eDevoir.com
 *
 */
abstract class ExerciceAbstractController extends AbstractController
{
	/**
	 * L'exercice sur lequel porte la page
	 * 
	 * @var Exercice
	 */
	protected $Exercice;
	
	/**
	 * Si nécessaire, vérifier que l'utilisateur actuel a bien le droit de récupérer l'exercice
	 *
	 * @param string $module
	 * @param string $controller
	 * @param string $view
	 * @param string $data
	 */
	public function __construct($Module,$Controller,$View,$Data)
	{
		parent::__construct($Module, $Controller, $View, $Data);
		
		//La page porte sur un exercice en particulier
		if(is_array($Data) && isset($Data['data']))
		{
			//Récupérer l'exercice :
			$this->Exercice = Exercice::load($Data['data']);
			if(is_null($this->Exercice)
			||
			(!isset($_SESSION['Eleve']) && !isset($_SESSION['Correcteur']) && !isset($_SESSION['Admin'])
			||
			(
				(isset($_SESSION['Eleve']) && $this->Exercice->Createur != $_SESSION['Eleve']->ID)
				||
				(isset($_SESSION['Correcteur']) && $this->Exercice->Correcteur != $_SESSION['Correcteur'])
			)))
			{
				$this->View->setMessage("warning", "Impossible d'accéder à l'exercice " . $Data['data'], 'eleve/acces_impossible');

				$this->redirect("/eleve/exercice/");
			}
			
			//Récupérer les données pour la vue :
			$this->View->Exercice = $this->Exercice;
		}
	}
	
	/**
	 * Vérifie que l'exercice associé à la page est dans un des statuts tolérés.
	 * Sinon, affiche un message d'erreur et redirige vers l'accueil de l'exercice.
	 * 
	 * @param array $Status la lsite des status possibles
	 * @param string $Message le message à afficher en cas d'erreur (type warning)
	 * 
	 * @return uniquement si autorisé, sinon redirect.
	 */
	protected function canAccess(array $Status, $Message = "Vous ne pouvez pas (ou plus) accéder à cette page.")
	{
		if(!in_array($this->Exercice->Statut, $Status))
		{
			$this->View->setMessage("warning", $Message);
			$this->redirect("/eleve/exercice/index/" . $this->Exercice->Hash);
		}
	}
	
	/**
	 * Redirige vers une page de l'exercice actuel.
	 * Si aucun paramètre n'est spécifié, redirige vers la page d'accueil de l'exercice.
	 * 
	 * @param string $URL la base de l'URL, auquel sera accolée le hash.
	 * 
	 * @return jamais.
	 */
	protected function redirectExercice($URL = '/eleve/exercice/index/')
	{
		$this->redirect($URL . $this->Exercice->Hash);
	}
}