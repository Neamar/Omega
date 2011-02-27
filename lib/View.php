<?php
/**
 * View.php - 10 nov. 2010
 *
 * Gestion des vues.
 *
 * PHP Version 5
 *
 * @category  View
 * @package   Root
 * @author    Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @copyright 2010 Matthieu Bacconnier
 * @license   Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link      http://edevoir.com
 */

/**
 * Classe permettant la gestion de la vue et le chargement rapide des aides de vues.
 * Il suffit d'appeller $VH->nomAideVue pour enclencher le chargement et l'utilisation.
 *
 * @category View
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 * @method string Exercice() Exercice(Exercice $Exercice, string $Tab) affiche un exercice
 */
class View
{
	/**
	 *
	 * La liste des données contenues par la vue
	 * @var array
	 */
	protected $Datas;

	/**
	 * Les méta données de la vue : le contrôleur parent, le nom, le titre de la page...
	 * @var array
	 */
	protected $Metas;

	/**
	 * Le contrôleur possédant cette vue
	 * @var AbstractController
	 */
	protected $Controller;

	/**
	 * Initialise une nouvelle vue.
	 *
	 * @param string $name le nom de la vue.
	 * @param AbstractController $controller le contrôleur parent.
	 */
	public function __construct($Name, AbstractController $Controller)
	{
		$this->Datas = array();
		$this->Metas = array(
			'name'=>$Name,
			'script'=>array(
				'http://ajax.googleapis.com/ajax/libs/jquery/1.5.0/jquery.min.js' => true,
				'/public/js/jquery-ui.min.js' => true,
				'/public/js/jquery.prettyPhoto.js' => true,
				'/public/js/default.js' => true,
			),
			'meta'=>array(),
			'style'=>array(
				'/public/css/head.css' => true,
				'/public/css/base.css' => true,
				'/public/css/' . $Controller->getModule() . '.css' => true,
				'/public/css/ui/ui.css' => true,
				'/public/css/prettyPhoto/prettyPhoto.css' => true,
			),
		);

		$this->Controller = $Controller;
	}

	/**
	 * Récupérer la liste des données contenues dans la vue sous forme d'un tableau associatif.
	 *
	 * @return array toutes les données de la vue
	 */
	public function toArray()
	{
		return $this->Datas;
	}

	/**
	 * Récupérer la liste des méta-données contenues dans la vue sous forme d'un tableau associatif.
	 *
	 * @return array toutes les méta-données de la vue
	 */
	public function metaToArray()
	{
		return $this->Metas;
	}

	/**
	 * Récupère une valeur de la vue.
	 *
	 * @param string $Key le nom de la valeur désirée
	 *
	 * @return string la valeur associée
	 */
	public function __get($Key)
	{
		return $this->Datas[$Key];
	}

	/**
	 * Récupère une des méta-informations
	 *
	 * @param string $Key
	 *
	 * @return mixed la valeur
	 */
	public function getMeta($Key)
	{
		return $this->Metas[$Key];
	}

	/**
	 * Récupérer le contrôleur de la vue.
	 *
	 * @return AbstractController le contrôleur.
	 */
	public function getController()
	{
		return $this->Controller;
	}

	/**
	 * Règle une nouvelle valeur sur la vue
	 *
	 * @param string $Key le nom de la valeur
	 * @param string $Value la valeur
	 */
	public function __set($Key,$Value)
	{
		$this->Datas[$Key] = $Value;
	}

	/**
	 * Règle une nouvelle méta donnée
	 *
	 * @param string $Key le nom de la valeur
	 * @param string $Value la valeur
	 */
	public function setMeta($Key,$Value)
	{
		$this->Metas[$Key] = $Value;
	}

	/**
	 * Teste l'existence d'une clé.
	 *
	 * @param string $Key la clé à tester
	 *
	 * @return bool true si la clé existe.
	 */
	public function __isset($Key)
	{
		return isset($this->Datas[$Key]);
	}

	/**
	 * Teste l'existence d'une clé méta.
	 *
	 * @param string $Key la clé à tester
	 *
	 * @return bool true si la clé existe.
	 */
	public function issetMeta($Key)
	{
		return isset($this->Metas[$Key]);
	}

	/**
	 * Efface la valeur d'une clé.
	 *
	 * @param string $Key la clé à effacer.
	 */
	public function __unset($Key)
	{
		unset($this->Datas[$Key]);
	}

	/**
	 * Efface la valeur d'une clé méta.
	 *
	 * @param string $Key la clé à effacer.
	 */
	public function unsetMeta($Key)
	{
		unset($this->Metas[$Key]);
	}

	/**
	 * Gestion des ViewHelper.
	 *
	 * @param string $func la fonction à appeler
	 * @param array $arg les paramètres de la fonction
	 */
	public function __call($func,array $args)
	{
		$Helper = 'ViewHelper_' . $func;
		if(!function_exists($Helper))
		{
			list($File) = explode('_', $func);
			include OO2FS::viewHelperPath($File);
		}

		return call_user_func_array($Helper, $args);
	}

	/**
	 * Définit le titre de la page Web (balise <title>)
	 *
	 * @param string $Title
	 * @param string $Intro le texte présentant la page
	 */
	public function setTitle($Title, $Intro = null)
	{
		self::setMeta('title', $Title);

		if($Intro != null)
		{
			self::setMeta('intro', $Intro);
		}
	}

	/**
	 * Définit le message à afficher en tête de page
	 *
	 * @param string $Title
	 */
	public function setMessage($Type, $Message, $DocLink = null)
	{
		self::setMeta('message', $Message);
		self::setMeta('messageClass', $Type);
		self::setMeta('messageDoc', $DocLink);
	}

	/**
	 * Définit la liste des liens composant le chemin / breadcrumb.
	 *
	 * @param array $Breads le tableau, du type Lien => Caption. Order matters.
	 */
	public function setBreadcrumbs(array $Breads)
	{
		self::setMeta('breadcrumbs', $Breads);
	}

	/**
	 * Ajoute une URL "à voir aussi" qui s'affichera à droite du breadcrumb.
	 *
	 * @param string $URL
	 * @param string $Caption
	 */
	public function setSeelink($URL, $Caption)
	{
		self::setMeta('seelink', array('url' => $URL, 'caption' => $Caption));
	}

	/**
	 * Ajoute un script en haut de page
	 *
	 * @param string $Src l'URL du script à ajouter. Si vide, ajoutera le fichier js associé à la page dans le fichier public/js
	 */
	public function addScript($Src = '')
	{
		if(empty($Src))
		{
			$Src = '/public/js/' . $this->Controller->getModule() . '/' . $this->Controller->getController() . '/' . $this->Controller->getAction() . '.js';
		}

		$this->Metas['script'][$Src] = true;
	}

	/**
	 * Supprime un script en haut de page
	 *
	 * @param string $Src le script à supprimer
	 */
	public function removeScript($Src)
	{
		unset($this->Metas['script'][$Src]);
	}

	/**
	 * Ajoute une donnée de balise méta
	 *
	 * @param string $Meta le nom de la balise
	 * @param string $Valeur la valeur à donner
	 */
	public function addMeta($Meta, $Valeur)
	{
		$this->Metas['meta'][$Meta] = $Valeur;
	}

	/**
	 * Supprime une donnée de balise méta
	 *
	 * @param string $Meta la balise à supprimer
	 */
	public function removeMeta($Meta)
	{
		unset($this->Metas['meta'][$Meta]);
	}

	/**
	 * Ajoute une feuille de style externe
	 *
	 * @param string $Src l'URL à ajouter
	 */
	public function addStyle($Src)
	{
		$this->Metas['style'][$Src] = true;
	}

	/**
	 * Supprime une feuille de style externe
	 *
	 * @param string $Src l'URL à supprimer
	 */
	public function removeStyle($Src)
	{
		unset($this->Metas['style'][$Src]);
	}

	/**
	 * Définit le fichier de vue à utiliser.
	 *
	 * @param unknown_type $URL
	 */
	public function setFile($URL)
	{
		$this->setMeta('viewFile', $URL);
	}

	/**
	 * Écrit le contenu de la balise <head>
	 *
	 * Dans l'ordre :
	 * Titre
	 * Balises Meta
	 * Feuilles de style
	 * Scripts
	 *
	 * @return html
	 */
	public function renderHead()
	{
		$Head = '	<title>' . $this->getMeta('title') . '</title>' . PHP_EOL;

		foreach($this->Metas['meta'] as $Meta=> $Value)
		{
			$Head .= '	<meta name="' . $Meta . '" value="' . $Value . '" />' . PHP_EOL;
		}

		foreach($this->Metas['style'] as $URL=>$_)
		{
			$Head .= '	<link href="' . $URL . '" rel="stylesheet" type="text/css" media="screen" />' . PHP_EOL;
		}

		foreach($this->Metas['script'] as $URL=>$_)
		{
			$Head .= '	<script type="text/javascript" src="' . $URL . '"></script>' . PHP_EOL;
		}

		return $Head;
	}

	/**
	 * Renvoie le message enregistré pour la page (s'il existe)
	 *
	 * @return html
	 */
	public function renderMessage()
	{
		if($this->issetMeta('message'))
		{
			if($this->issetMeta('messageDoc'))
			{
				$Parties = explode("/", $this->getMeta("messageDoc"));
				$Content = $this->Doc_box($Parties[0], $Parties[1], $this->getMeta('message'), 'message ' . $this->getMeta('messageClass'));
			}
			else
			{
				$Content = '<aside class="message ' . $this->getMeta('messageClass')  . '">
	<p>' . $this->getMeta('message') . '</p>
</aside>';
			}

			return '<div id="content-message" class="' . $this->getMeta('messageClass')  . '">
' . $Content . '
</div><!-- /content-message -->
<div id="bottom-message" class="' . $this->getMeta('messageClass')  . '"></div>';
		}
		else
		{
			return '<div id="bottom-message"></div>';
		}
	}

	/**
	 * Renvoie le contenu du ruban à afficher sur la page.
	 *
	 * @return string une liste HTML.
	 */
	public function renderRibbon()
	{
		$RibbonParts = include OO2FS::ribbonPath($this->Controller->getModule());

		//Si les liens sont précisés, les enregistrer pour le rendu depuis ->renderLinks()
		if(isset($RibbonParts['links']))
		{
			$this->setMeta('links', $RibbonParts['links']);
		}

		$R = '
	<div id="ribbon-left">
		' . $RibbonParts['left'] . '
	</div>
	<div id="ribbon-center">
		' . $RibbonParts['center'] . '
	</div>
	<div id="ribbon-right">
		' . $RibbonParts['right'] . '
	</div>
';
		return $R;
	}

	/**
	 * Renvoie le fil d'Ariane de la page
	 *
	 * @return string une liste HTML.
	 */
	public function renderBreadcrumbs()
	{
		//Un tableau de mots pour lesquels un accent n'est pas du luxe/
		//Les clés représentent la valeur non accentuée (en ucfirst), les valeurs le remplacement sémantiquement correct.
		$Remplacements = array(
			'Eleve' => 'Élève', //as in /eleve/
			'Desinscription' => 'Désinscription', //as in /eleve/desinscription
			'Recuperation' => 'Récupération', //as in /eleve/recuperation
			'Reclamation' => 'Réclamation', //as in /eleve/exercice/reclamation/hash
			'Corrige' => 'Corrigé', //as in /eleve/exercice/corrige/hash
			'Matieres' => 'Matières', //as in /correcteur/options/matiere
			'Matieres_rapide' => 'Matières_rapide', //as in /correcteur/options/matiere_rapide
		);

		$R = '';

		//Lien "voir aussi"
		if($this->issetMeta('seelink'))
		{
			$SeeLink = $this->getMeta('seelink');
			$R .= '<div class="see-link"><a href="' . $SeeLink['url'] . '">' . $SeeLink['caption'] . '</a></div>' . PHP_EOL;
		}

		$Ariane = array('/' => $this->Html_eDevoir('span')) + self::getMeta('breadcrumbs');

		//Mettre au format microdata décrit par Google
		//http://www.google.com/support/webmasters/bin/answer.py?hl=en&answer=185417
		foreach($Ariane as $Url => &$Caption)
		{
			//Réduire les parties trop longues
			if(isset($Caption[61]))
			{
				$Caption = trim(mb_substr($Caption, 0, 57, 'UTF-8')) . '&hellip;';
			}

			if(isset($Remplacements[$Caption]))
			{
				$Caption = $Remplacements[$Caption];
			}

			$Caption = '
		<div itemscope itemtype="http://data-vocabulary.org/Breadcrumb">
			<a href="' . $Url . '" itemprop="url">
			   	<span itemprop="title">' . str_replace('_', ' ', $Caption) . '</span>
			</a>
		</div>
	';
		}
		$R .= $this->Html_List($Ariane);

		return $R;
	}

	public function renderLinks()
	{
		if(!$this->issetMeta('links'))
		{
			//Liens par défaut
			$this->setMeta(
				'links',
				array(
					'/' => 'Accueil',
					'/eleve/connexion' => 'Connexion élève',
					'/correcteur/connexion' => 'Connexion correcteur'
				)
			);
		}

		$Liens = $this->getMeta('links');

		//Wrapper dans un span pour le CSS
		foreach($Liens as &$Caption)
		{
			$Caption = '<span>' . $Caption . '</span>';
		}

		return $this->Html_listAnchor($Liens);
	}

	/**
	 * Renvoie le message de titre et l'introduction.
	 *
	 * @return html
	 */
	public function renderTitle()
	{
		$Title = '	<h1>' . $this->getMeta('title') . '</h1>' . PHP_EOL;

		if($this->issetMeta('intro'))
		{
			$Title .= '	<p class="intro">' . $this->getMeta('intro') . '</p>' . PHP_EOL;
		}

		return $Title;
	}

	/**
	 * Écrit le contenu du fichier vue sur la sortie standard
	 *
	 * @return void tout est écrit.
	 */
	public function renderContent()
	{
			include $this->getMeta('viewFile');
	}

	/**
	 * Écrit la vue sur la sortie standard
	 *
	 * @return void tout est écrit via echo.
	 */
	public function render()
	{
		include DATA_PATH . '/layouts/template.phtml';
	}
}