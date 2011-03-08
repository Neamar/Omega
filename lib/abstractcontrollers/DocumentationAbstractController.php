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
class DocumentationAbstractController extends AbstractController
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
			'cgu' => "Conditions Générales d'Utilisation",
			'cgv' => "Conditions Générales de Vente",
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
			'parrain' => "Qu'est-ce qu'un compte parrain ?",
			'validation' => "Comment valider mon compte ?",
			'probleme_connexion' => "Pourquoi ne puis-je pas me connecter ?",
			'deconnexion' => "Pourquoi ai-je été déconnecté ?",
			'depot' => "Comment déposer de l'argent sur mon compte ?",
			'creation' => "Comment ajouter un devoir à faire corriger ?",
			'demande_complet' => "À quoi correspond une demande «&nbsp;Corrigé complet&nbsp;» ?",
			'demande_aide' => "À quoi correspond une demande «&nbsp;Piste de résolution&nbsp;» ?",
			'champ_rendu' => "À quoi correspond la date d'expiration ?",
			'champ_annulation' => "À quoi correspond la date d'annulation ?",
			'demande_aide' => 'À quoi correspond une demande de type «&nbsp;aide&nbsp;» ?',
			'demande_complet' => 'À quoi correspond une demande de type «&nbsp;complet&nbsp;» ?',
			'champ_info' => "À quoi sert le cadre «&nbsp;Informations complémentaires&nbsp;» ?",
			'auto_accept' => "Comment fonctionne l'acceptation automatique ?",
			'ajout' => "Comment ajouter des fichiers à mon exercice ?",
			'fichiers' => "Quels types de fichiers sont autorisés ?",
			'erreurs_upload' => "J'ai obtenu un message «&nbsp;erreur à l'envoi du fichier&nbsp;» ?",
			'supplement' => "Pourquoi dois-je payer un supplément «&nbsp;suractivité&nbsp;» ?",
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
			'pourquoi_ci' => "Pourquoi envoyer ma carte d'identité à l'inscription ?",
			'champ_telephone' => "Quels numéros de téléphones sont acceptés ?",
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
			'demande_aide' => 'À quoi correspond une demande de type «&nbsp;aide&nbsp;» ?',
			'demande_complet' => 'À quoi correspond une demande de type «&nbsp;complet&nbsp;» ?',
			'champ_annulation' => "À quoi correspond la date d'annulation ?",
			'demande_complet' => "À quoi correspond une demande «&nbsp;Corrigé complet&nbsp;» ?",
			'demande_aide' => "À quoi correspond une demande «&nbsp;Piste de résolution&nbsp;» ?",
			'champ_info' => "À quoi sert le cadre «&nbsp;Informations complémentaires&nbsp;» ?",
			'limite_reservation' => "Pourquoi ne puis-je plus réserver d'exercices ?",
			'refus' => "Que se passe-t-il si mon offre est refusée par l'élève ?",
			'faq' => "Qu'est ce que la FAQ exercice ? Comment s'en servir ? Quelles sont mes contraintes ?",
			'tex' => "Qu'est ce que le LaTeX ? Je ne peux pas envoyer un document Word ?",
			'aide_tex' => "Comment utiliser LaTeX ?",
			'aide_ressource' => 'Comment ajouter des images à mon corrigé ?',
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
	
	public function __construct($Module, $Controller, $View)
	{
		parent::__construct($Module, $Controller, $View);
		$this->View->addStyle('/public/css/documentation/Typo.css');
	}
	
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
			throw new Exception('Page de documentation inconnue');
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
		
		if($this->Action != 'index' && isset(self::$Pages[$this->Controller][$this->Action]))
		{
			$Ariane[self::build($this->Action, null, $this->Controller, $this->Module)] = self::$Pages[$this->Controller][$this->Action];
		}
		
		return $Ariane;
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
		$TexPath = APPLICATION_PATH . '/documentation/' . $this->Controller . '/views/' . strtolower($Action) . '.tex';

		if(!isset(self::$Pages[$this->Controller][$Action]))
		{
			go404('Cette page de documentation n\'existe pas.');
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
	protected function fromLatex($Action)
	{
		//Renseigner le titre :
		$this->View->setTitle(self::$Pages[$this->Controller][$Action]);
		
		//Charger le contenu
		$this->View->Content = file_get_contents(APPLICATION_PATH . '/documentation/index/views/' . strtolower($this->View->getMeta('name')) . '.tex');
		
		//Dévier vers la vue LaTeX
		$this->View->setFile(OO2FS::viewPath('generic_latex', null, 'index', 'documentation'));
	}
}