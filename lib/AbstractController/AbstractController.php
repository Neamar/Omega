<?php
/**
 * AbstractController.php - 26 oct. 2010
 *
 * Contrôleur de base pour le site. Implémente les fonctionnalités de base qui seront étendues par les enfants.
 *
 * PHP Version 5
 *
 * @category  Controller
 * @package   Root
 * @author    Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @copyright 2010 Matthieu Bacconnier
 * @license   Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link      http://devoirminute.com
 */

/**
 * Contrôleur abstrait.
 *
 * @category Controller
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://devoirminute.com
 *
 */
abstract class AbstractController
{
	/**
	 * Le nom du contrôleur ; exemple : 'points'
	 * @var string
	 */
	protected $controller;

	/**
	 * Le nom du module ; exemple : 'eleve'
	 * @var string
	 */
	protected $module;

	/**
	 * La vue associée au contrôleur.
	 * @var View
	 */
	protected $view;

	/**
	 * Les données associées.
	 * @var string
	 */
	protected $data;

	/**
	 * La page actuelle doit-elle renvoyer des données au format AJAX ?
	 * @var bool
	 */
	protected $isAjax;

	/**
	 * La page actuelle doit-elle être renvoyée dans le template ?
	 * @var bool
	 */
	protected $isTemplate;

	/**
	 * Contrôleur par défaut, prenant en paramètres les différentes composantes de l'URL.
	 *
	 * @param string $module
	 * @param string $controller
	 * @param string $view
	 * @param string $data
	 */
	public function __construct($module,$controller,$view,$data)
	{
		$this->module= $module;
		$this->controller= $controller;
		$this->data = $data;

		$this->view = new View($view, $this);

		//Si format Ajax, la vue commence par un underscore par convention.
		if(substr($view, 0, 1)=='_')
		{
			$this->isAjax = true;
			$this->isTemplate = false;
		}
		else
		{
			$this->isAjax = false;
			$this->isTemplate = true;
		}
	}

	/**
	 * Récupère la vue associée au contrôleur.
	 * @return View la vue associée.
	 */
	public function getView()
	{
		return $this->view();
	}

	/**
	 * Redirige le visiteur sur la page spécifiée
	 *
	 * @param string $URL l'adresse de redirection : /eleve/points/ajout
	 */
	public function redirect($URL)
	{
		if(!headers_sent())
		{
			header('Location:' . URL . $URL);
		}
		else
		{
			Debug::fail('Impossible de rediriger après l\'envoi des headers.');
		}
	}

	/**
	 * Concatène un autre contrôleur avec l'actuel.
	 * En cas de conflits, les données du nouveau contrôleur remplacent celles de l'ancien.
	 * Attention, ne fais pas de tests : l'appel avec des paramètres incorrects fera une erreur probablement critique.
	 * @param string $vue
	 * @param string $data
	 * @param string $controleur
	 * @param string $module
	 */
	public function concat($view,$data=null, $controller=null, $module=null)
	{
		$ControllerPath = APPLICATION_PATH . '/' . $_GET['module'] . '/' . $_GET['controller'] . '/' . $_GET['controller'] . 'Controller.php';
		$ControllerName = $_GET['controller'] . 'Controller';
		$ViewName = $_GET['view'] . 'Action' . (empty($_GET['data'])?'':'_wd');

		include $ControllerPath;

		$ConcatController = new $ControllerName();
		$ConcatController->$ViewName();

		$this->view = array_merge($this->view, $ConcatController->getView());
	}

	/**
	 * Renvoie la vue.
	 */
	public function renderView()
	{
		//La vue
		$V = $this->view;

		if($this->isAjax)
		{
			echo json_encode($V->toArray());
		}
		else
		{
			include OO2FS::viewPath($this->view->getMeta('name'), $this->controller, $this->module);
		}
	}

	/**
	 * Découpe une URL pour renvoyer le contrôleur à appeler
	 *
	 * @param string $URL une URL à décortiquer
	 * @return array Un tableau récupérable avec list($module,$controleur,$vue,$data) = fromURL();
	 */
	public static function fromURL($URL)
	{
		$Parts = explode('/', substr($URL, 1), 4);
		$Size = count($Parts);

		// /eleve/
		if($Size==2 && $Parts[1] == '')
		{
			return array($Parts[0], 'index', 'index', null);
		}
		// /eleve/inscription
		else if($Size==2)
		{
			return array($Parts[0], 'index', $Parts[1], null);
		}
		// /eleve/points/
		else if($Size==3 && $Parts[2] == '')
		{
			return array($Parts[0], $Parts[1], 'index',null);
		}
		// /eleve/points/ajout
		else if($Size==3)
		{
			return array($Parts[0], $Parts[1], $Parts[2],null);
		}
		// /eleve/point/ajout/paypal
		else if($Size==4)
		{
			return array($Parts[0], $Parts[1], $Parts[2], $Parts[3]);
		}
		// Inconnu :(
		else
		{
			Debug::fail('URL indécodable.');
		}
	}


	/**
	 * Construit une URL à partir des composantes indiquées.
	 *
	 * @param string $vue
	 * @param string $data
	 * @param string $controleur
	 * @param string $module
	 * @return string $URL
	 */
	public static function build($view,$data=null, $controller=null, $module=null)
	{
		if(is_null($controller))
		{
			$controller = $this->controller;
		}
		if(is_null($module))
		{
			$module = $this->module;
		}

		//Simplification des URLS.
		if(!is_null($data))
		{
			$data = '/' . $data;
		}
		else
		{
			$data = '';
		}

		if($controller!='index')
		{
			$controller = '/' . $controller;
		}
		else if($view=='index')
		{
			$controller = '/';
		}
		else
		{
			$controller = '';
		}


		if($view!='index')
		{
			$view = '/' . $view;
		}
		else if($controller!='/')
		{
			$view = '/';
		}
		else
		{
			$view = '';
		}
			
		return '/' . $module . $controller . $view . $data;
	}
}