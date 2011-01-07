<?php
/**
 * indexController.php - 26 oct. 2010
 * 
 * Fonctions de debuggage
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
 * Contrôleur d'index du module élève.
 * 
 * @category Controller
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 *
 */
class Debug_IndexController extends AbstractController
{
	/**
	 * Constructeur.
	 * 
	 * @param string $module nom de module
	 * @param string $controller nom de contrôleur
	 * @param string $view nom de vue
	 * @param string $data données
	 */
	public function __construct($module,$controller,$view,$data)
	{
		parent::__construct($module, $controller, $view, $data);
	}
	
	/**
	 * Page d'accueil du module.
	 * 
	 */
	public function indexAction()
	{
		$this->View->setTitle('Module de debug');
	}
	
	/**
	 * Dumping de la session
	 * 
	 */
	public function sessionAction()
	{
		$this->View->setTitle('Dump de la session.');
	}
	
	/**
	 * Structure d'une session.
	 * 
	 */
	public function sessionStructAction()
	{
		$this->View->setTitle('Dump de la structure session.');
	}
	
	/**
	 * Dumping des données serveur
	 * 
	 */
	public function serverAction()
	{
		$this->View->setTitle('Dump des données serveur.');
	}
	
	/**
	 * Exemples de dates
	 * 
	 */
	public function dateAction()
	{
		$this->View->setTitle('Exemple de dates');
	}
	
	/**
	 * Page par défaut.
	 * 
	 */
	public function pageAction()
	{
		$this->View->setTitle('Exemple de contenu HTML');
		
		$this->View->setMessage("error", "Texte du message d'erreur", "foo/bar");
	}
	
	/**
	 * Affiche toute la documentation.
	 */
	public function alldocAction()
	{
		$this->View->setTitle('Documentation eDevoir');
		include OO2FS::controllerPath('index', 'documentation');
		
		$Files = array();
		foreach(Documentation_IndexController::$Pages as $Section => $Pages)
		{
			$Files[$Section] = array();
			
			foreach($Pages as $URL => $Titre)
			{
				if(is_file(APPLICATION_PATH . '/documentation/' . $Section . '/views/' . $URL . '.tex'))
				{
					$Files[$Section][$URL] = array(
						'Titre' => $Titre,
						'Fichier' => APPLICATION_PATH . '/documentation/' . $Section . '/views/' . $URL . '.tex'
					);
				}
			}
		}
		
		$this->View->Files = $Files;
		$this->View->addStyle('/public/css/documentation/Typo.css');
	}
	
	/**
	 * Supprime toutes les tables de la base de données et les recrée à neuf via le fichier /lib/default.sql.
	 */
	public function cleanAction()
	{
		$this->View->setTitle('Reset des données');
		
		
		if(isset($_POST['delete']))
		{
			$Actions = array();
			
			/*
			 * Première partie : suppression de toutes les données de la base
			 */
			$Actions[] = 'DROP DATABASE.';
			$MaxIterations = 10; 
			while($MaxIterations > 0)
			{
				$MaxIterations--;
				$TablesRestantes = array();
				$Tables = SQL::query('SHOW TABLES');
				while($Table = mysql_fetch_row($Tables))
				{
					$TablesRestantes[] = $Table[0];
				}
				SQL::queryNoFail('DROP TABLE ' . implode(', ', $TablesRestantes));
				
				if(count($TablesRestantes)==0)
				{
					break;
				}
				else
				{
					$Actions[] = 'Suppression de ' . count($TablesRestantes) . ' tables.<br />';
				}
			}
			$Actions[] = 'Base de données nettoyée.';
			
			/*
			 * Seconde partie : suppression de tous les dossiers d'exercices
			 */
			$Actions[] = 'DROP Exercices';
			function rrmdir($dir)
			{
				$objects = scandir($dir);
				foreach ($objects as $object)
				{
					if ($object != "." && $object != "..")
					{
						if (filetype($dir . "/" . $object) == "dir")
						{
							rrmdir($dir . "/" . $object);
						}
						else
						{
							unlink($dir . "/" . $object);
						}
					}
				}
				reset($objects);
				rmdir($dir);
			}
			
			$Dirs = array
			(
				'/public/exercices',
				'/data/CV',
				'/data/logs'
			);
			
			foreach($Dirs as $Dir)
			{
				if(is_dir(PATH . $Dir))
				{
					rrmdir(PATH . $Dir);
					$Actions[] = $Dir . ' nettoyé.';
				}
				mkdir(PATH . $Dir);
			}
			
			/*
			* Troisième partie : reconstruction de la DB.
			*/
			$Actions[] = 'REBUILD DATABASE';
			
			$Requetes = explode(';', file_get_contents(DATA_PATH . '/default.sql'));
			foreach($Requetes as $Requete)
			{
				if(trim($Requete) != '')
				{
					Sql::query($Requete);
				}
			}
			$Actions[] = count($Requetes) . ' requeêtes exécutées.';
			$Actions[] = 'Reprise des valeurs par défaut.';
			
			
			$Actions[] = 'Fin du nettoyage.';
			$this->View->Actions = $Actions;
		}
	}
}