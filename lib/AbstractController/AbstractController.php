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
 * @link      http://edevoir.com
 */

/**
 * Contrôleur abstrait.
 *
 * @category Controller
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 *
 */
abstract class AbstractController
{
	/**
	 * Le nom du contrôleur ; exemple : 'points'
	 * @var string
	 */
	protected $Controller;

	/**
	 * Le nom du module ; exemple : 'eleve'
	 * @var string
	 */
	protected $Module;

	/**
	 * La vue associée au contrôleur.
	 * @var View
	 */
	protected $View;

	/**
	 * Les données associées.
	 * @var string
	 */
	protected $Data;

	/**
	 * La page actuelle doit-elle renvoyer des données au format AJAX ?
	 * @var bool
	 */
	protected $IsAjax;

	/**
	 * La page actuelle doit-elle être renvoyée dans le template ?
	 * @var bool
	 */
	protected $IsTemplate;

	/**
	 * Contrôleur par défaut, prenant en paramètres les différentes composantes de l'URL.
	 *
	 * @param string $module
	 * @param string $controller
	 * @param string $view
	 * @param string $data
	 */
	public function __construct($Module,$Controller,$View,$Data)
	{
		$this->Module= $Module;
		$this->Controller= $Controller;
		$this->Data = $Data;

		$this->View = new View($View, $this);
		$this->View->setMeta('viewFile', OO2FS::viewPath($View, $Data, $Controller, $Module));

		//Si format Ajax, la vue commence par un underscore par convention.
		if(substr($View, 0, 1)=='_')
		{
			$this->IsAjax = true;
			$this->IsTemplate = false;
		}
		else
		{
			$this->IsAjax = false;
			$this->IsTemplate = true;
		}
		
		//Réucpérer les enregistrements qui devaient être sauvegardés pour le futur (i.e. maintenant)
		if(isset($_SESSION['Futur']))
		{
			foreach($_SESSION['Futur'] as $MetaKey => $V)
			{
				$this->View->setMeta($MetaKey, $V);
			}
			
			unset($_SESSION['Futur']);
		}
	}

	/**
	 * Récupère la vue associée au contrôleur.
	 * @return View la vue associée.
	 */
	public function getView()
	{
		return $this->View();
	}
	
	/**
	 * Récupère le contrôleur associé au contrôleur objet.
	 * @return string le contrôleur associé
	 */
	public function getController()
	{
		return $this->Controller;
	}
	
	/**
	 * Récupère le module associé au contrôleur.
	 * @return string le module associé
	 */
	public function getModule()
	{
		return $this->Module;
	}

	/**
	 * Méthode de base pour toutes les pages AJAX.
	 * Une fois appellée, cette méthode prend la main et reroute la requête pour un traitement AJAX.
	 * 
	 * @param string $Query la requête (brute) à effectuer
	 * @param string $OrderBy l'ordre de tri, la date décroissante par défaut
	 * 
	 * @return array un tableau de résultat contenant la requête.
	 */
	protected function ajax($Query, $OrderBy = 'Date DESC')
	{
		$ResultatsSQL = Sql::query($Query . "\nORDER BY " . $OrderBy);
		$Resultats = array();
		while($Resultat = mysql_fetch_row($ResultatsSQL))
		{
			$Resultats[] = $Resultat;
		}
		
		$this->View->Datas = $Resultats;
		$this->View->setMeta('viewFile', LIB_PATH . '/Views/ajax.phtml');
		return $Resultats;
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
			if($this->View->issetMeta('message'))
			{
				$_SESSION['Futur'] = array(
					'messageClass' => $this->View->getMeta('messageClass'),
					'messageDoc' => $this->View->getMeta('messageDoc'),
					'message' => $this->View->getMeta('message')
				);
			}
			
			header('Location:' . URL . $URL);
		}
		else
		{
			Debug::fail('Impossible de rediriger après l\'envoi des headers.');
		}
	}

	/**
	 * Concatène un autre contrôleur avec l'actuel.
	 * En cas de conflits, les données de l'ancien contrôleur ont la priorité.
	 * Contrat, ne fait pas de tests : l'appel avec des paramètres incorrects fera une erreur probablement critique.
	 * Contrat, ne vérifie pas la récursion.
	 * 
	 * @param string $URL l'URL dont les données doivent être concaténées.
	 */
	public function concat($URL)
	{
		list($Module,$Controller,$View,$Data) = AbstractController::fromURL($URL);
		
		$ControllerPath = OO2FS::controllerPath($Controller, $Module);
		$ControllerName = OO2FS::controllerClass($Controller, $Module);
		$ViewName = OO2FS::viewFunction($View, $Data, $Controller, $Module);

		if(!class_exists($ControllerName, false))
		{
			include $ControllerPath;
		}
		
		$ConcatController = new $ControllerName($Module, $Controller, $View, $Data);
		$ConcatController->$ViewName();

		$this->View->merge($ConcatController->getView());
	}

	/**
	 * Renvoie la vue.
	 */
	public function renderView()
	{
		//La vue
		$V = $this->View;

		if($this->IsAjax)
		{
			echo json_encode($V->Datas);
		}
		else
		{
			$V->render();
		}
	}

	/**
	 * Découpe une URL pour renvoyer le contrôleur à appeler
	 *
	 * @param string $URL une URL à décortiquer
	 * @return array Un tableau récupérable avec list($Module,$Controller,$View,$Data) = AbstractController::fromURL();
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
	public static function build($View,$Data=null, $Controller=null, $Module=null)
	{
		if(is_null($controller))
		{
			$Controller = $this->Controller;
		}
		if(is_null($Module))
		{
			$Module = $this->Module;
		}

		//Simplification des URLS.
		if(!is_null($Data))
		{
			$Data = '/' . $Data;
		}
		else
		{
			$Data = '';
		}

		if($Controller!='index')
		{
			$Controller = '/' . $Controller;
		}
		else if($View=='index')
		{
			$Controller = '/';
		}
		else
		{
			$Controller = '';
		}


		if($View!='index')
		{
			$View = '/' . $View;
		}
		else if($Controller!='/')
		{
			$View = '/';
		}
		else
		{
			$View = '';
		}
			
		return '/' . $Module . $Controller . $View . $Data;
	}
}