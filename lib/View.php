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
 * @link      http://devoirminute.com
 */

/**
 * Classe permettant la gestion de la vue et le chargement rapide des aides de vues.
 * Il suffit d'appeller $VH->nomAideVue pour enclencher le chargement et l'utilisation.
 *
 * @category View
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://devoirminute.com
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
		$this->_Metas = array('name'=>$Name, 'controller'=>$Controller);
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
	 * Efface la valeur d'une clé.
	 *
	 * @param string $Key la clé à effacer.
	 */
	public function __unset($Key)
	{
		unset($this->_Datas[$Key]);
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
	
	public function setTitle($Title)
	{
		self::setMeta('title', $Title);
	}
	
	/**
	 * Écrit la vue sur la sortie standard
	 */
	public function render()
	{
		include PATH . '/layouts/template.phtml';
	}
}