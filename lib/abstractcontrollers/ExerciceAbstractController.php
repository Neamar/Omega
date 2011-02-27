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

				$this->redirect('/' . $this->getModule() . '/exercice/');
			}
			
			//Lien vers la FAQ
			if($this->Exercice->isFaq() && $this->getAction() != 'faq')
			{
				$this->View->setSeelink('/' . $this->getModule() . '/exercice/faq/' . $this->Exercice->Hash, 'Chat exercice');
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
		$this->View->setTitle("Affichage du sujet de l'exercice «&nbsp;" . $this->Exercice->Titre . '&nbsp;»');

		$this->View->Type = 'Eleve';
		$this->View->Fichiers = $this->Exercice->getFiles(array('SUJET'));
		
		$this->deflectView(OO2FS::genericViewPath('exercice/fichiers_wd'));
	}
	
	/**
	 * Afficher le corrigé
	 */
	public function corrigeActionWd()
	{
		$this->canAccess(array('ENVOYE', 'TERMINE', 'REFUSE', 'REMBOURSE'), 'Le corrigé n\'est pas encore disponible.');
		
		$this->View->setTitle("Affichage du corrigé de l'exercice «&nbsp;" . $this->Exercice->Titre . '&nbsp;»');

		$this->View->Type = 'Correcteur';
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
		
		$this->View->setTitle("Affichage de la réclamation déposée sur l'exercice «&nbsp;" . $this->Exercice->Titre . '&nbsp;»');

		$this->View->Type = 'Reclamation';
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
		if(!$this->Exercice->isFaq())
		{
			$this->View->setMessage('warning', 'La FAQ n\'est pas encore ouverte.');
			$this->redirectExercice();
		}
		
		if(strtotime($this->Exercice->Expiration) < time() - DELAI_FAQ * 24 * 3600)
		{
			$this->View->setMessage('warning', 'La FAQ est fermée, vous ne pouvez plus poster de question.');
			$this->View->Ouvert = false;
		} 
		else
		{
			$this->View->Ouvert = true;
		}
		
		$this->View->addScript('/public/js/eleve/exercice/faq.js');
		
		//Ajout d'une question
		if($this->View->Ouvert && isset($_POST['faq-question-exercice']))
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
					'Membre' => $this->getMembre()->ID
				);
				
				if(Sql::insert('Exercices_FAQ', $ToInsert))
				{
					$Params = array(
						'Exercice' => $this->Exercice,
						'Eleve' => $this->Exercice->getEleve(),
						'Correcteur' => $this->Exercice->getCorrecteur(),
						'Question' => $_POST['question'],
						'Membre' => $this->getMembre()
					);
					
					Event::dispatch(Event::MEMBRE_FAQ_QUESTION, $Params);
					
					$this->View->setMessage('ok', 'Message enregistré.');
					unset($_POST['question']);
					
					$this->redirectExercice('/' . $this->getModule() . '/exercice/faq/');
				}
				else
				{
					$this->View->setMessage('error', 'Impossible de sauvegarder votre message.');
				}
			}
		}
		
		//Ajout d'une réponse
		if(isset($_POST['faq-reponse-exercice']))
		{
			if(empty($_POST['reponse']))
			{
				$this->View->setMessage('warning', 'Message vide.');
			}
			elseif(empty($_POST['question']) || ($_POST['question'] = intval($_POST['question'])) == 0)
			{
				$this->View->setMessage('error', 'Un problème a été détecté avec la question référente.');
			}
			elseif(Sql::singleColumn('SELECT COUNT(*) AS S FROM Exercices_FAQ WHERE Exercice = "' . DbObject::filterID($this->Exercice->ID) . '" AND ID=' . $_POST['question'] . ' AND ISNULL(Parent)', 'S') == 0)
			{
				$this->View->setMessage('error', 'Impossible de trouver la question auquel se réfère ce message.');
			}
			else 
			{
				$ToInsert = array(
					'Exercice' => $this->Exercice->ID,
					'_Creation' => 'NOW()',
					'Texte' => $_POST['reponse'],
					'Statut' => 'OK',
					'Membre' => $this->getMembre()->ID,
					'Parent' => $_POST['question']
				);
				
				if(Sql::insert('Exercices_FAQ', $ToInsert))
				{
					$Params = array(
						'Exercice' => $this->Exercice,
						'Eleve' => $this->Exercice->getEleve(),
						'Correcteur' => $this->Exercice->getCorrecteur(),
						'Question' => Sql::singleColumn('SELECT Texte FROM Exercices_FAQ WHERE Exercice = "' . DbObject::filterID($this->Exercice->ID) . '" AND ID=' . $_POST['question'] . ' AND ISNULL(Parent)', 'Texte'),
						'Reponse' => $_POST['reponse'],
						'Membre' => $this->getMembre()
					);
					
					Event::dispatch(Event::MEMBRE_FAQ_REPONSE, $Params);
					
					$this->View->setMessage('ok', 'Message enregistré.');
					unset($_POST['question'], $_POST['reponse']);
				}
				else
				{
					$this->View->setMessage('error', 'Impossible de sauvegarder votre message.');
				}
			}
		}
		
		$this->View->FAQ = Sql::queryAssoc(
			'SELECT Exercices_FAQ.ID, Exercices_FAQ.Creation, Exercices_FAQ.Texte, Exercices_FAQ.Parent, Exercices_FAQ.Statut, LOWER(Membres.Type) AS Type
			FROM Exercices_FAQ
			LEFT JOIN Membres ON (Exercices_FAQ.Membre = Membres.ID)
			WHERE Exercice = "' . DbObject::filterID($this->Exercice->ID) . '"
			ORDER BY COALESCE(Exercices_FAQ.Parent, Exercices_FAQ.ID), Exercices_FAQ.Creation',
			'ID'
		);
		
		$this->deflectView(OO2FS::genericViewPath('exercice/faq_wd'));
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
	
	/**
	 * Crée un token à partir de l'identifiant du correcteur, du bigfish et de l'exercice.
	 * Ce token permet de valider l'envoi d'une ressource sur un exercice sans utiliser le système de sessions.
	 * 
	 * En effet, uploadify passe par un fichier flash qui ne fait pas transiter les identifiants de sessions.
	 * Autrement dit, il n'est pas possible d'avoir _ressource dans le "dossier" de l'exercice (/correcteur/exercice/_ressource/HASH)
	 * puisque le script redirigerait automatiquement vers la page de connexion. 
	 * Afin de pallier au problème, la page _ressource est rendue disponible hors connexion.
	 * Cependant, elle demande un token justifiant que la personne derrière est habilitée à l'envoi de données sur cet exercice.
	 * 
	 * @param int $ID l'identifiant du membre
	 * @param string $Hash le hash de l'exercice (en version longue)
	 * 
	 * @return string un token.
	 */
	protected function computeToken($ID, $Hash)
	{
		return sha1(substr($Hash, -10) . SALT . ($ID * ord($Hash[0])));
	}
}