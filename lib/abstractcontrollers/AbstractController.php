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
	 * L'action (la vue non objet) 
	 * @var string
	 */
	protected $Action;

	/**
	 * La vue associée au contrôleur.
	 * @var View
	 */
	protected $View;

	/**
	 * Les données associées.
	 * @var array
	 */
	protected $Data;

	/**
	 * La page actuelle doit-elle être renvoyée dans le template ? (au milieu des breadcrumbs et autres titres,
	 * @see data/layouts/template.phtml
	 * @var bool
	 */
	protected $UseTemplate = true;
	
	/**
	 * Le membre actuellement connecté (s'il existe)
	 * @var Membre
	 */
	protected $Membre = null;

	/**
	 * Contrôleur par défaut, prenant en paramètres les différentes composantes de l'URL.
	 *
	 * @param string $module
	 * @param string $controller
	 * @param string $view
	 * @param array $data
	 */
	public function __construct($Module, $Controller, $View, array $Data = null)
	{
		$this->Module = strtolower($Module);
		$this->Controller = strtolower($Controller);
		$this->Action = $View;
		$this->Data = $Data;

		$this->View = new View($View, $this);
		$this->View->setFile(OO2FS::viewPath($View, $Data, $Controller, $Module));

		//Si format Ajax, la vue commence par un underscore par convention.
		if(substr($View, 0, 1)=='_')
		{
			$this->UseTemplate = false;
		}
		
		//Récupérer les enregistrements qui devaient être sauvegardés pour le futur (i.e. maintenant)
		if(isset($_SESSION['Futur']))
		{
			foreach($_SESSION['Futur'] as $MetaKey => $V)
			{
				$this->View->setMeta($MetaKey, $V);
			}
			
			unset($_SESSION['Futur']);
		}
		
		$this->View->setBreadcrumbs($this->computeBreadcrumbs());
	}

	/**
	 * Récupère la vue associée au contrôleur.
	 * @return View la vue associée.
	 */
	public function getView()
	{
		return $this->View;
	}
	
	/**
	 * Dévie la vue vers un nouveau fichier.
	 * 
	 * @param string $URL le chemin absolu vers la nouvelle vue.
	 */
	public function deflectView($URL)
	{
		$this->View->setMeta('viewFile', $URL);
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
	 * Récupère l'action associée au contrôleur objet.
	 * @return string l'action associée
	 */
	public function getAction()
	{
		return $this->Action;
	}
	
	/**
	 * Renvoie le membre actuellement connecté sur le module en cours.
	 * Une exception est lancée si le membre n'est pas défini à cet endroit.
	 * 
	 * @throws Exception le membre n'existe pas
	 * 
	 * @return Membre le membre actuellement connecté
	 */
	public function getMembre()
	{
		if(is_null($this->Membre))
		{
			$Cle = $this->getMembreIndex();
			if(!isset($_SESSION[$Cle]) || !$_SESSION[$Cle] instanceOf Membre)
			{
				throw new Exception("Le membre n'est pas disponible !");
				return null;
			}
			else
			{
				$this->Membre = $_SESSION[$Cle];
			}
		}
		
		return $this->Membre;
	}
	
	/**
	 * Renvoie l'index théorique qu'aurait le membre dans $_SESSION s'il était connecté.
	 * Notez qu'il peut être connecté ou non, cette fonctione ne renvoie que la clé théorique.
	 */
	public function getMembreIndex()
	{
		return ucfirst($this->getModule());
	}
	
	/**
	 * Calcule le fil d'Ariane.
	 * 
	 * @return array le fil calculé
	 */
	protected function computeBreadcrumbs()
	{
		$Ariane = array();
		
		$Ariane[self::build('index', null, 'index', $this->Module)] = ucfirst($this->Module);
		
		if($this->Controller != 'index')
		{
			$Ariane[self::build('index', null, $this->Controller, $this->Module)] = ucfirst($this->Controller);
		}
		
		if($this->Action != 'index')
		{
			$Ariane[self::build($this->Action, null, $this->Controller, $this->Module)] = ucfirst($this->Action);
		}
		
		return $Ariane;
	}

	/**
	 * Méthode de base pour toutes les pages AJAX.
	 * Une fois appellée, cette méthode prend la main et reroute la requête pour un traitement AJAX.
	 * 
	 * @param string $Query la requête (brute) à effectuer
	 * @param string $OrderBy l'ordre de tri, la date décroissante par défaut
	 * @param int $Limit le nombre de tuples à renvoyer.
	 * 
	 * @return array un tableau de résultat contenant la requête.
	 */
	protected function ajax($Query, $OrderBy = 'Date DESC', $Limit = null)
	{
		if(is_null($Limit))
		{
			$Limit = isset($_POST['limit'])?intval($_POST['limit']):AJAX_LIMITE;
		}
		
		//Mise en forme automatique de la requête.
		//Ajouter un SQL_CALC_FOUND_ROWS, l'order By et la limite.
		$Query = str_replace(array('SELECT ', "SELECT\n"), 'SELECT SQL_CALC_FOUND_ROWS ', $Query) . '
		ORDER BY ' . $OrderBy . '
		LIMIT ' . $Limit;
		
		$ResultatsSQL = Sql::query($Query);
		$Resultats = array();
		while($Resultat = mysql_fetch_row($ResultatsSQL))
		{
			$Resultats[] = $Resultat;
		}
		
		$NbTuples = Sql::singleColumn(
			'SELECT FOUND_ROWS() AS S',
			'S'
		);
		
		if($NbTuples > count($Resultats))
		{
			$Resultats[] = '+';
		}
			
		
		$this->json($Resultats);
		return $Resultats;
	}
	
	/**
	 * Prépare la vue à être utilisée "like JSON".
	 * Méthode de plus bas niveau que ->ajax(), qui se contente d'enregistrer les données et de détourner la vue.
	 * 
	 * @param array $Data les données (clés non pris en compte)
	 */
	protected function json(array $Data)
	{
		$this->View->jsonDatas = array_values($Data);
		$this->deflectView(OO2FS::genericViewPath('json'));
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
			
			redirect($URL);
		}
		else
		{
			throw new Exception('Impossible de rediriger après l\'envoi des headers.');
		}
	}

	/**
	 * Concatène un autre contrôleur avec l'actuel.
	 * En cas de conflits, les données de l'ancien contrôleur ont la priorité.
	 * Contrat, ne fait pas de tests : l'appel avec des paramètres incorrects fera une erreur probablement critique.
	 * Contrat, ne vérifie pas la récursion.
	 * 
	 * @param string $URL l'URL dont les données doivent être concaténées.
	 * 
	 * @return View la vue nouvelle crée.
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
		
		/**
		 * La nouvelle vue
		 * 
		 * @var View
		 */
		$NewView = $ConcatController->getView();
		
		//Fusionner metas, scripts et styles.
		foreach($NewView->getMeta('script') as $Src => $_)
		{
			$this->View->addScript($Src);
		}
		foreach($NewView->getMeta('meta') as $Meta => $Valeur)
		{
			$this->View->addMeta($Meta, $Valeur);
		}
		foreach($NewView->getMeta('style') as $Src => $_)
		{
			$this->View->addStyle($Src);
		}
		
		if($NewView->issetMeta('message'))
		{
			$this->View->setMessage(
				$NewView->getMeta('messageClass'),
				$NewView->getMeta('message'),
				(is_null($NewView->getMeta('messageDoc'))?null:$NewView->getMeta('messageDoc'))
			);
		}
		
		return $NewView;
	}

	/**
	 * Renvoie la vue.
	 */
	public function renderView()
	{
		//La vue
		$V = $this->View;

		if($this->UseTemplate)
		{
			$V->render();
		}
		else
		{
			//Ne rendre que le contenu, sans l'enrobage (titre, html entourant, breadcrumbs...)
			$V->renderContent();
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
			return array($Parts[0], $Parts[1], 'index', null);
		}
		// /eleve/points/ajout
		else if($Size==3)
		{
			return array($Parts[0], $Parts[1], $Parts[2], null);
		}
		// /eleve/point/ajout/paypal
		else if($Size==4)
		{
			return array($Parts[0], $Parts[1], $Parts[2], self::buildData($Parts[3]));
		}
		// Inconnu :(
		else
		{
			throw new Exception('URL indécodable.');
		}
	}
	
	/**
	 * Construit des données utilisables (type tableau) à partir d'une chaîne.
	 * 
	 * @param string $DatasString
	 * 
	 * @return array le tableau de données
	 */
	public static function buildData($DatasString)
	{
		$Datas = array();
		//Traiter les données si existantes
		if(!empty($DatasString))
		{
			$Components = explode('/', $DatasString);
			if(count($Components) % 2 == 1)
			{
				array_unshift($Components, 'data');
			}
			$Components = array_chunk($Components, 2);
			

			foreach($Components as $Component)
			{
				$Datas[$Component[0]] = $Component[1];	
			}
		}
		
		return $Datas;
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
		//Simplification des URLS.
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


		if($View != 'index')
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
		
		if(!empty($Data))
		{
			$DataString = '/';
			
			//Désimplifier l'URL, il y a des données
			if($View == '/')
			{
				$View = '/index';
			}

			if(!is_null($Data['data']))
			{
				$DataString .= $Data['data'];
				unset($Data['data']);
				if(!empty($Data))
				{
					$DataString .= '/';
				}
			}
			
			foreach($Data as $Key => $Val)
			{
				$DataString .= $Key . '/' . $Val;
			}
			
			$Data = $DataString;
		}
		else
		{
			$Data = '';
		}
			
		return '/' . $Module . $Controller . $View . $Data;
	}
}