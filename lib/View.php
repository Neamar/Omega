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
 *
 */
class View
{
	/**
	 * 
	 * La liste des données contenues par la vue
	 * @var array
	 */
	private $_Datas;
	
	/**
	 * Les méta données de la vue : le contrôleur parent, le nom, le titre de la page...
	 * @var array
	 */
	private $_Metas;

	/**
	 * Initialise une nouvelle vue.
	 *
	 * @param string $name le nom de la vue.
	 * @param AbstractController $controller le contrôleur parent.
	 */
	public function __construct($Name, AbstractController $Controller)
	{
		$this->_Datas = array();
		$this->_Metas = array(
			'name'=>$Name,
			'controller'=>$Controller,
			'script'=>array(
				'http://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js'=>true,
			),
			'meta'=>array(),
			'style'=>array(
				'/public/css/base.css'=>'true',
			),
		);
	}

	/**
	 * Récupérer la liste des données contenues dans la vue sous forme d'un tableau associatif.
	 *
	 * @return array toutes les données de la vue
	 */
	public function toArray()
	{
		return $this->_Datas;
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
		return $this->_Datas[$Key];
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
		return $this->_Metas[$Key];
	}

	/**
	 * Règle une nouvelle valeur sur la vue
	 *
	 * @param string $Key le nom de la valeur
	 * @param string $Value la valeur
	 */
	public function __set($Key,$Value)
	{
		$this->_Datas[$Key] = $Value;
	}
	
	/**
	 * Règle une nouvelle méta donnée
	 *
	 * @param string $Key le nom de la valeur
	 * @param string $Value la valeur
	 */
	public function setMeta($Key,$Value)
	{
		$this->_Metas[$Key] = $Value;
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
		return isset($this->_Datas[$Key]);
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
		return isset($this->_Metas[$Key]);
	}

	/**
	 * Efface la valeur d'une clé.
	 *
	 * @param string $Key la clé à effacer.
	 */
	public function __unset($Key)
	{
		unset($this->_Datas[$Key]);
	}
	
	/**
	 * Efface la valeur d'une clé méta.
	 *
	 * @param string $Key la clé à effacer.
	 */
	public function unsetMeta($Key)
	{
		unset($this->_Metas[$Key]);
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
			include LIB_PATH . '/ViewHelper/' . $File . '.php';
		}

		return call_user_func_array($Helper, $args);
	}
	
	/**
	 * Définit le titre de la page Web (balise <title>)
	 * 
	 * @param string $Title
	 */
	public function setTitle($Title)
	{
		self::setMeta('title', $Title);
	}
	
	/**
	 * Définit le message à afficher en tête de page
	 * 
	 * @param string $Title
	 */
	public function setMessage($Type, $Message)
	{
		self::setMeta('message', $Message);
		self::setMeta('messageClass', $Type);
	}
	
	/**
	 * Ajoute un script en haut de page
	 * 
	 * @param string $Src l'URL du script à ajouter
	 */
	public function addScript($Src)
	{
		$this->_Metas['script'][$Src] = true;
	}
	
	/**
	 * Supprime un script en haut de page
	 * 
	 * @param string $Src le script à supprimer
	 */
	public function removeScript($Src)
	{
		unset($this->_Metas['script'][$Src]);
	}
	
	/**
	 * Ajoute une donnée de balise méta
	 * 
	 * @param string $Meta le nom de la balise
	 * @param string $Valeur la valeur à donner
	 */
	public function addMeta($Meta, $Valeur)
	{
		$this->_Metas['meta'][$Meta] = $Valeur;
	}
	
	/**
	 * Supprime une donnée de balise méta
	 * 
	 * @param string $Meta la balise à supprimer
	 */
	public function removeMeta($Meta)
	{
		unset($this->_Metas['meta'][$Meta]);
	}
	
	/**
	 * Ajoute une feuille de style externe
	 * 
	 * @param string $Src l'URL à ajouter
	 */
	public function addStyle($Src)
	{
		$this->_Metas['style'][$Src] = true;
	}
	
	/**
	 * Supprime une feuille de style externe
	 * 
	 * @param string $Src l'URL à supprimer
	 */
	public function removeStyle($Src)
	{
		unset($this->_Metas['style'][$Src]);
	}
	
	/**
	 * Écrit le contenu de la balise <head>
	 * 
	 * Dans l'ordre :
	 * Titre
	 * Balises Meta
	 * Feuilles de style
	 * Scripts
	 */
	public function renderHead()
	{
		echo '	<title>' . $this->getMeta('title') . '</title>' . "\n";
		
		foreach($this->_Metas['meta'] as $Meta=> $Value)
		{
			echo '	<meta name="' . $Meta . '" value="' . $Value . '" />' . "\n";
		}
		
		foreach($this->_Metas['style'] as $URL=>$_)
		{
			echo '	<link href="' . $URL . '" rel="stylesheet" type="text/css" media="screen" />' . "\n";
		}
		
		foreach($this->_Metas['script'] as $URL=>$_)
		{
			echo '	<script type="text/javascript" src="' . $URL . '"></script>' . "\n";
		}
	}

	
	/**
	 * Écrit la vue sur la sortie standard
	 */
	public function render()
	{
		include DATA_PATH . '/layouts/template.phtml';
	}
}