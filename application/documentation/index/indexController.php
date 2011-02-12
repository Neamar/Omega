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
class Documentation_IndexController extends AbstractController
{
	/**
	 * Liste des pages de documentation 
	 * 
	 * @var array
	 */
	public static $Pages = array
	(
		'index' => array
		(
			'index' => "Accueil de la documentation",
			'fonctionnement' => "Fonctionnement du site",
			'faq' => "Questions fréquemment posées",
			'exemples' => "Exemples de corrigés rendus",
			'parents' => "À l'intention des parents",
			'eleves' => "À l'intention des élèves",
			'points' => "Le système de points",
			'cgu' => "Conditions générales d'utilisation",
			'cgv' => "Conditions générales de vente",
			'confidentialite' => "Protection de la vie privée",
			'contact' => "Nous contacter",
			'legal' => "Mentions légales",
			'matieres' => "Liste des matières",
			'niveaux' => "Liste des classes",
			'tex' => "Comment insérer des formules mathématiques dans un élément de la FAQ ?",
		),
		'eleve' => array
		(
			'index' => "Aide des élèves",
			'fonctionnement' => "Comment faire corriger mon devoir ?",
			'inscription' => "Comment m'inscrire en tant qu'élève ?",
			'validation' => "Comment valider mon compte ?",
			'probleme_connexion' => "Pourquoi ne puis-je pas me connecter ?",
			'deconnexion' => "Pourquoi ai-je été déconnecté ?",
			'depot' => "Comment déposer de l'argent sur mon compte ?",
			'creation' => "Comment ajouter un devoir à faire corriger ?",
			'demande_complet' => "À quoi correspond une demande « Corrigé complet » ?",
			'demande_aide' => "À quoi correspond une demande « Piste de résolution » ?",
			'champ_rendu' => "À quoi correspond la date d'expiration ?",
			'champ_annulation' => "À quoi correspond la date d'annulation ?",
			'champ_info' => "À quoi sert le cadre « Informations complémentaires » ?",
			'auto_accept' => "Comment fonctionne l'acceptation automatique ?",
			'ajout' => "Comment ajouter des fichiers à mon exercice ?",
			'fichiers' => "Quels types de fichiers sont autorisés ?",
			'erreurs_upload' => "J'ai obtenu un message « erreur à l'envoi du fichier » ?",
			'supplement' => "Pourquoi dois-je payer un supplément « suractivité » ?",
			'acces_impossible' => "Pourquoi ne puis-je pas accéder à un exercice ?",
			'annulation' => "Comment annuler mon devoir ?",
			'offre' => "Comment puis-je accepter (ou refuser) une offre ?",
			'acceptation' => "Que se passe-t-il une fois l'offre du correcteur acceptée ?",
			'vie_privee' => "J'ai eu une alerte vie privée, à quoi cela correspond-il ?",
			'faq' => "Comment utiliser la FAQ pour poser des questions sur mon exercice ?",
			'envoi_gratuit' => "Pourquoi ai-je été remboursé alors que je n'ai reçu qu'une partie du travail ?",
			'retard' => "Que se passe-t-il en cas de retard ?",
			'envoye' => "J'ai reçu un mail m'indiquant que le corrigé est disponible, que faire ?",
			'contestation' => "Le travail a été mal réalisé, comment puis-je me faire rembourser ?",
			'remboursement' => "Que se passe-t-il une fois que la procédure de remboursement est lancée ?",
			'mecontentement_remboursement' => "J'ai été partiellement remboursé mais je ne suis toujours pas satisfait, que faire ?",
			'contestation_tardive' => "J'ai dit que l'exercice me convenait ou j'ai laissé passer la limite, mais je souhaite contester. Comment faire ?",
			'retrait' => "Comment faire pour retirer des points ?",
			'bloque' => "Mon compte est bloqué, pourquoi ? Que faire ?",
			'desinscription' => "Comment faire pour me désinscrire ?",
		),
		'correcteur' => array
		(
			'index' => "Aide des correcteurs",
			'fonctionnement' => "Comment corriger un devoir ?",
			'inscription' => "Comment m'inscrire en tant que correcteur ?",
			'pourquoi_autoentreprise' => "Pourquoi ouvrir mon auto-entreprise ?",
			'pourquoi_cv' => "Pourquoi fournir mon CV à l'inscription ?",
			'comment_autoentreprise' => "Comment ouvrir mon auto-entreprise ?",
			'fermer_autoentreprise' => "Comment fermer mon auto-entreprise ?",
			'validation' => "Pourquoi mon compte n'est-il pas automatiquement validé ?",
			'probleme_connexion' => "Pourquoi ne puis-je pas me connecter ?",
			'implications' => "Qu'implique l'inscription sur le site ? Suis-je libre de refuser les offres qui me sont faites ?",
			'matieres' => "Comment indiquer les matières dans lesquelles je suis compétent ?",
			'options' => "Comment modifier les options de mon compte ?",
			'marche' => "Comment choisir un devoir pour le corriger ?",
			'reservation' => "Comment réserver un exercice ?",
			'prix' => 'Quelle somme demander ?',
			'champ_annulation' => "À quoi correspond la date d'annulation ?",
			'demande_complet' => "À quoi correspond une demande « Corrigé complet » ?",
			'demande_aide' => "À quoi correspond une demande « Piste de résolution » ?",
			'champ_info' => "À quoi sert le cadre « Informations complémentaires » ?",
			'limite_reservation' => "Pourquoi ne puis-je plus réserver d'exercices ?",
			'refus' => "Que se passe-t-il si mon offre est refusée par l'élève ?",
			'faq' => "Qu'est ce que la FAQ exercice ? Comment s'en servir ? Quelles sont mes contraintes ?",
			'tex' => "Qu'est ce que le LaTeX ? Je ne peux pas envoyer un document Word ?",
			'aide_tex' => "Comment utiliser LaTeX ?",
			'alerte' => "J'ai eu une alerte, à quoi cela correspond-il ?",
			'envoi' => "Comment envoyer mon corrigé ?",
			'envoi_gratuit' => "Pourquoi puis-je envoyer mon corrigé gratuitement ?",
			'paiement' => "J'ai envoyé mon corrigé, pourquoi ne suis-je pas encore payé ?",
			'retard' => "Que se passe-t-il si je ne rends pas le corrigé dans les temps ?",
			'qualite' => "Que se passe-t-il si mon travail n'est pas de qualité ?",
			'contestation' => "Mon travail peut-il faire l'objet d'une contestation ?",
			'bloque' => "Je suis bloqué. Pourquoi ? Que puis-je faire pour me débloquer ?",
			'retrait' => "Comment retirer de l'argent du site vers mon compte ?",
			'declaration' => "Comment déclarer mon argent ?",
			'desinscription' => "Comment me désinscrire ?",
		),
	);
	
	/**
	 * Récupère le titre d'une page.
	 * 
	 * @param string $section
	 * @param string $page
	 * 
	 * @return le titre de la page $section/$page.
	 */
	public static function getTitle($section, $page)
	{
		if(isset(self::$Pages[$section][$page]))
		{
			return self::$Pages[$section][$page];
		}
		else
		{
			return 'Page inconnue.';
		}
	}
	
	/**
	 * Modifie légèrement le fil pour lui donner plus de cohérence dans le cas de la documentation
	 * @see AbstractController::computeBreadcrumbs()
	 * 
	 * @return array le fil
	 */
	public function computeBreadcrumbs()
	{
		$Ariane = parent::computeBreadcrumbs();
		
		if($this->Action != 'index')
		{
			$Ariane[self::build($this->Action, null, $this->Controller, $this->Module)] = self::$Pages[$this->Controller][$this->Action];
		}
		
		return $Ariane;
	}
	
	/**
	 * Accueil de la documentation.
	 */
	public function indexAction()
	{
		$this->View->setTitle(self::$Pages[$this->Controller]['index']);
		
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
	
	/**
	 * Gère la documentation au format TeX
	 * 
	 * @param string $methodName le nom à utiliser, e.g. confidentialiteAction
	 * @param array $args les arguments (vides)
	 * @throws Exception si la méthode ou le fichier n'existe pas
	 */
	public function __call($methodName, array $args)
	{
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
}