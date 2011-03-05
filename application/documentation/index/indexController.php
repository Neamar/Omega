<?php
/**
 * indexController.php - 26 oct. 2010
 * 
 * Contrôleur pour la documentation.
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
 * Contrôleur de base pour toute la documentation.
 * 
 * @category Controller
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 *
 */
class Documentation_IndexController extends DocumentationAbstractController
{
	public function __construct($Module, $Controller, $View)
	{
		//Forcer l'utilisation d'une page en .htm
		if($View != 'index' && strpos($_SERVER['REQUEST_URI'], 'documentation') !== false)
		{
			redirect('/' . $View . '.htm', 301);
		}
		
		parent::__construct($Module, $Controller, $View);
	}
	
	/**
	 * Accueil de la documentation.
	 */
	public function indexAction()
	{
		$this->View->setTitle(self::$Pages[$this->Controller]['index']);
		$this->View->addScript('/public/js/documentation/index.js');
		$this->View->Pages = self::$Pages;
	}
	
	/**
	 * Liste des matières supportées
	 */
	public function matieresAction()
	{
		$this->View->setTitle(self::$Pages[$this->Controller]['matieres']);
		
		$this->View->Matieres = SQL::queryAssoc('SELECT Matiere FROM Matieres', 'Matiere', 'Matiere');
	}
	
	/**
	 * Liste des niveaux supportés
	 */
	public function niveauxAction()
	{
		$this->View->setTitle(self::$Pages[$this->Controller]['niveaux']);
		
		$this->View->Classes = SQL::queryAssoc('SELECT Classe, DetailsClasse FROM Classes ORDER BY Classe DESC', 'Classe', 'DetailsClasse');
	}
	
	/**
	 * Liste des balises.
	 */
	public function texAction()
	{
		$this->View->setTitle(self::$Pages[$this->Controller]['tex'], 'Petit aide mémoire à l\'intention de ceux qui souhaient utiliser LaTeX');
	}
	
	public function contactAction()
	{
		$this->View->setTitle(self::$Pages[$this->Controller]['contact'], 'Formulaire de contact pour communiquer avec l\'équipe du site');
		$this->View->Valeurs = array('Question', 'Éclaircissement', 'Problème technique', 'Réclamation', 'Justification', 'Autre');
		
		if(isset($_POST['contact']))
		{
			if(empty($_POST['mail']))
			{
				$this->View->setMessage('error', 'Merci de nous indiquer un mail auquel nous pourrons vous répondre');
			}
			elseif(!Validator::mail($_POST['mail']))
			{
				$this->View->setMessage('error', 'Adresse mail invalide');
			}
			elseif(empty($_POST['sujet']))
			{
				$this->View->setMessage('error', 'Merci de spécifier le sujet de votre message');
			}
			elseif(empty($_POST['message']))
			{
				$this->View->setMessage('error', 'Merci de compléter votre demande en entrant un message');
			}
			elseif(!in_array($_POST['categorie'], $this->View->Valeurs))
			{
				$this->View->setMessage('error', 'Merci de sélectionner la raison du contact.');
			}
			elseif(!Validator::captcha())
			{
				$this->View->setMessage('error', 'Le captcha rentré est invalide ; merci de rééssayer.');
			}
			else
			{
				External::mail(
					($_POST['categorie'] == 'Problème technique'?'webmaster@edevoir.com':'contact@edevoir.com'),
					$_POST['categorie'] . ' : ' . $_POST['sujet'],
					$_POST['message'],
					$_POST['mail']
				);
				$this->View->MessageEnvoye = true;
				$this->View->setMessage('ok', 'Message envoyé ! Vous devriez recevoir une réponse dans les 48h ouvrées.');
			}
			
		}
	}
	
	public function legalAction()
	{
		$this->fromLatex('legal');
	}
	
	public function cguAction()
	{
		$this->fromLatex('cgu');
	}
	
	public function cgvAction()
	{
		$this->fromLatex('cgv');
	}
}