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
	
	/**
	 * Gère la documentation au format TeX
	 * 
	 * @param string $methodName le nom à utiliser, e.g. confidentialiteAction
	 * @param array $args les arguments (vides)
	 * @throws Exception si la méthode ou le fichier n'existe pas
	 */
	public function __call($methodName, array $args)
	{
		//Si on arrive du site, mettre une option pour fermer la page.
		if(isset($_SERVER['HTTP_REFERER']) && preg_match('`^' . preg_quote(URL) . '/(eleve|correcteur)/`', $_SERVER['HTTP_REFERER']))
		{
			$this->View->setMessage('info', 'Vous avez fini de lire ? <a href=# onclick=window.close() >Fermer cette page</a>.');
		}
		
		if(!substr($methodName, -6) == 'Action')
		{
			throw new Exception("Méthode " . $methodName . " inconnue.", 1);
			return;
		}
		
		$Action = substr($methodName, 0, -6);
		$TexPath = APPLICATION_PATH . '/documentation/' . $this->Controller . '/views/' . $Action . '.tex';
		
		if(!isset(self::$Pages[$this->Controller][$Action]))
		{
			throw new Exception("Page " . $methodName . " inconnue.", 1);
		}
		elseif(!file_exists($TexPath))
		{
			throw new Exception("Méthode " . $methodName . " inconnue (aucun fichier).", 1);
		}
		else
		{
			//Dévier la vue vers la vue générique pour les fichiers TeX
			$this->View->setFile(OO2FS::viewPath('generic', null, 'index', 'documentation'));
			$this->View->texPath = $TexPath;
			
			//Renseigner le titre :
			$this->View->setTitle(self::$Pages[$this->Controller][$Action]);
		}
	}
	
	/**
	 * Transforme un document pur LaTeX en texte utilisable par le Typographe.
	 */
	private function fromLatex($Action)
	{
		//Renseigner le titre :
		$this->View->setTitle(self::$Pages[$this->Controller][$Action]);
		
		//Charger le contenu
		$this->View->Content = file_get_contents(APPLICATION_PATH . '/documentation/index/views/' . $this->View->getMeta('name') . '.tex');
		
		//Dévier vers la vue LaTeX
		$this->View->setFile(OO2FS::viewPath('generic_latex', null, 'index', 'documentation'));
	}
}