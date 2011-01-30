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

			if(is_null($this->Exercice) || !$this->hasAccess($this->Exercice))
			{
				$this->View->setMessage("warning", "Impossible d'accéder à l'exercice " . $Data['data'], 'eleve/acces_impossible');

				$this->redirect('/' . $_GET['module'] . '/exercice/');
			}
			
			//Lien vers la FAQ
			if($this->Exercice->isFaq())
			{
				$this->View->setSeelink('/' . $_GET['module'] . '/exercice/faq/' . $this->Exercice->Hash, 'Chat exercice');
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
	
	/**
	 * Afficher le sujet
	 */
	public function sujetActionWd()
	{
		$this->View->setTitle("Affichage du sujet de l'exercice « " . $this->Exercice->Titre . ' »');

		$this->View->Infos = $this->Exercice->InfosEleve;
		$this->View->Fichiers = $this->Exercice->getFiles(array('SUJET'));
		
		$this->deflectView(OO2FS::genericViewPath('exercice/fichiers_wd'));
	}
	
	/**
	 * Afficher le corrigé
	 */
	public function corrigeActionWd()
	{
		$this->canAccess(array('ENVOYE', 'TERMINE', 'REFUSE', 'REMBOURSE'), 'Le corrigé n\'est pas encore disponible.');
		
		$this->View->setTitle("Affichage du corrigé de l'exercice « " . $this->Exercice->Titre . ' »');

		$this->View->Infos = $this->Exercice->InfosCorrecteur;
		$this->View->Fichiers = $this->Exercice->getFiles(array('CORRIGE'));
		
		$this->deflectView(OO2FS::genericViewPath('exercice/fichiers_wd'));
	}
	
	/**
	 * Afficher la contestation.
	 */
	public function reclamationActionWd()
	{
		$this->canAccess(array('TERMINE', 'REFUSE', 'REMBOURSE'), 'Aucune réclamation n\'a été émise.');
		
		if($this->Exercice->Reclamation == 'NON')
		{
			$this->View->setMessage('warning', 'Aucune réclamation n\'a été émise.');
			$this->redirectExercice();
		}
		
		$this->View->setTitle("Affichage de la réclamation déposée sur l'exercice « " . $this->Exercice->Titre . ' »');

		$this->View->Infos = $this->Exercice->InfosReclamation;
		$this->View->Fichiers = $this->Exercice->getFiles(array('RECLAMATION'));
		
		$this->deflectView(OO2FS::genericViewPath('exercice/fichiers_wd'));
	}
	
	public function zipActionWd()
	{
		//Désactiver le templating.
		$this->UseTemplate = false;
		
		//Charger les fichiers nécessaires :
		$this->View->Files = $this->Exercice->getSortedFiles();
		
		//Et dévier la vue :
		$this->deflectView(OO2FS::genericViewPath('exercice/zip_wd'));
	}
	
	/**
	 * Chat de l'exercice
	 */
	public function faqActionWd()
	{
		if(isset($_POST['faq-question-exercice']))
		{
			if(empty($_POST['question']))
			{
				$this->View->setMessage('warning', 'Message vide.');
			}
			else 
			{
				$ToInsert = array(
					'Exercice' => $this->Exercice->ID,
					'_Creation' => 'NOW()',
					'Texte' => $_POST['question'],
					'Statut' => 'OK',
					'Membre' => $_SESSION[ucfirst($this->getModule())]->ID
				);
				
				if(Sql::insert('Exercices_FAQ', $ToInsert))
				{
					$this->View->setMessage('ok', 'Message enregistré.');
				}
				else
				{
					$this->View->setMessage('error', 'Impossible de sauvegarder votre message.');
				}
			}
		}
	}
	
	/**
	 * Termine un exercice.
	 * Peut-être appelé à la notation, sur un timeout après DELAI_REMBOURSEMENT, ou sur une décision administrateur.
	 * 
	 * @param string $Message le message. Le texte "et paiement du correcteur" sera automatiquement ajouté.
	 * @param Membre $ChangeAuthor l'auteur du changement
	 * @param array $Changes les modifications à apporter en plus
	 * @throws Exception l'exercice ne peut être clos maintenant.
	 */
	protected function cloreExercice($Message, Membre $ChangeAuthor, array $Changes = array())
	{
		if(!in_array($this->Exercice->Statut, array('ENVOYE', 'REFUSE')))
		{
			throw new Exception('Impossible de clore ici.');
		}
		
		$Correcteur = $this->Exercice->getCorrecteur();
		$this->Exercice->Enchere = (int) $this->Exercice->Enchere;
		Sql::start();
		$Correcteur->credit($this->Exercice->Enchere, 'Paiement pour l\'exercice « ' . $this->Exercice->Titre . ' »', $this->Exercice);
		Membre::getBanque()->debit($this->Exercice->Enchere, 'Paiement exercice.');
		$this->Exercice->setStatus('TERMINE', $ChangeAuthor, $Message . " et paiement du correcteur.", $Changes);
		
		Sql::commit();
		
		Event::dispatch(Event::ELEVE_EXERCICE_TERMINE);
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
			$this->View->setMessage('warning', $Message);
			$this->redirectExercice();
		}
	}
	
	/**
	 * Vérifie que l'exercice associé à la page est disponible.
	 * Overridé par les classes filles.
	 * 
	 * @return bool true si l'exercice peut être accédé.
	 */
	protected function hasAccess(Exercice $Exercice)
	{
		return false;
	}
	
	/**
	 * Redirige vers une page de l'exercice actuel.
	 * Si aucun paramètre n'est spécifié, redirige vers la page d'accueil de l'exercice.
	 * 
	 * @param string $URL la base de l'URL, auquel sera accolée le hash.
	 * 
	 * @return jamais.
	 */
	protected function redirectExercice($URL = null)
	{
		if(is_null($URL))
		{
			$URL = '/' . $this->Module . '/exercice/index/';
		}
		
		$this->redirect($URL . $this->Exercice->Hash);
	}
}