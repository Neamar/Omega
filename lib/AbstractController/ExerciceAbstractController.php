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

			//Vérifier que l'on a les accès.
			//Partir du postulat qu'on les a, puis examiner toutes les possibilités pour ne pas les avoir.
			$CanAccess = true;
			
			$CanAccess = $CanAccess && !is_null($this->Exercice);
			
			if($_GET['module'] == 'eleve')
			{
				$CanAccess = $CanAccess && $this->Exercice->Createur == $_SESSION['Eleve']->ID;
			}
			elseif($_GET['module'] == 'correcteur')
			{
				$CanAccess = $CanAccess && $this->Exercice->Correcteur != $_SESSION['Correcteur']->ID;
			}
			else 
			{
				$CanAccess = false;
			}
			
			if(!$CanAccess)
			{
				$this->View->setMessage("warning", "Impossible d'accéder à l'exercice " . $Data['data'], 'eleve/acces_impossible');

				$this->redirect("/eleve/exercice/");
			}
			
			//Récupérer les données pour la vue :
			$this->View->Exercice = $this->Exercice;
		}
	}
	
	/**
	 * Calcule le fil d'Ariane.
	 * @see AbstractController::computeBreadcrumbs()
	 * 
	 * @return array le fil calculé
	 */
	protected function computeBreadcrumbs()
	{
		$Ariane = array();
		
		$Ariane[self::build('index', null, 'index', $this->Module)] = ucfirst($this->Module);
		$Ariane[self::build('index', null, $this->Controller, $this->Module)] = ucfirst($this->Controller);
		
		if(is_array($this->Data) && isset($this->Data['data']))
		{
			$Ariane[self::build('index', $this->Data, $this->Controller, $this->Module)] = 'Exercice #' . $this->Data['data'];
		}
		if($this->Action != 'index')
		{
			$Ariane[self::build($this->Action, $this->Data, $this->Controller, $this->Module)] = ucfirst($this->Action);
		}
		
		return $Ariane;
	}
	
	public function sujetActionWd()
	{
		$this->View->setTitle("Affichage du sujet de l'exercice « " . $this->Exercice->Titre . ' »');

		$this->View->Fichiers = $this->Exercice->getFiles(array('SUJET'));
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