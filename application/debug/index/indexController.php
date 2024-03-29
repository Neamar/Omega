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
 * Contrôleur d'index du module debug.
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
	 * Affiche le dernier mail envoyé
	 */
	public function mailAction()
	{
		$Contenu = explode("\n", file_get_contents(DATA_PATH . '/logs/last_mail'), 3);
		
		
		$this->View->setTitle(
			'Dernier mail envoyé',
			'To: ' . $Contenu[0]
		);
		
		$this->View->Titre = $Contenu[1];
		$this->View->Mail = $Contenu[2];
	}
	
	/**
	 * Prévisualisation des templates
	 */
	public function seeMailAction()
	{
		echo '<meta charset=utf-8 />';
		echo str_replace(
			array(
				'__BONJOUR__',
				'__CONTENU__',
			),
			array(
				'Bonjour licoti5@hotmail.com',
				'<p>Vous avez demandé à être inscrit sur <strong>eDevoir</strong>.<br />
Merci de cliquer sur le lien suivant pour valider votre adresse mail et commencer à utiliser le site :<br />
<a href="http://edevoir.com/eleve/index/validation/xxxx">http://edevoir.com/eleve/index/validation/xxxx</a></p>
<p>Une fois cette formalité accomplie, vous pourrez créer un <a href="http://edevoir.com/eleve/exercice/creation">nouvel exercice</a>.
'
			),
			file_get_contents(DATA_PATH . '/layouts/mail.phtml')
		);
		exit();
	}
	
	public function sendMailAction()
	{
		$Mail = str_replace(
			array(
				'__BONJOUR__',
				'__CONTENU__',
			),
			array(
				'Bonjour licoti5@hotmail.com',
				'<p>Vous avez demandé à être inscrit sur <strong>eDevoir</strong>.<br />
Merci de cliquer sur le lien suivant pour valider votre adresse mail et commencer à utiliser le site :<br />
<a href="http://edevoir.com/eleve/index/validation/xxxx">http://edevoir.com/eleve/index/validation/xxxx</a></p>
<p>Une fois cette formalité accomplie, vous pourrez créer un <a href="http://edevoir.com/eleve/exercice/creation">nouvel exercice</a>.
'
			),
			file_get_contents(DATA_PATH . '/layouts/mail.phtml')
		);
		External::mail('licoti5@hotmail.com', 'Essai mail', $Mail);
		exit();
	}
	
	/**
	 * Page par défaut, pour les tests de CSS
	 * 
	 */
	public function pageAction()
	{
		$this->View->setTitle(
			'Exemple de contenu HTML',
			"Exemple de texte d'introduction présentant l'utilité de la page et son contenu."
		);
		
		$this->View->setSeelink('/', 'Exemple de lien à voir aussi');
		
		$this->View->setMessage('error', "Exemple de message d'erreur suivi d'un lien vers l'aide", "eleve/inscription");
		
		$this->View->SousPage = $this->concat('/eleve/inscription');
		
		$this->View->SousPage->setTitle(
			'Fonctionnalités de sous page',
			"Exemple de texte d'introduction présentant l'utilité de la sous page et son contenu. Ici, sous-page inscription élève"
		);		
	}
	
	/**
	 * Lance manuellement le cron.
	 * À utiliser avec précaution ! Peut déclencher en double certaines actions :\
	 */
	public function cronAction()
	{
		echo '<pre>';
		Event::dispatch(Event::CRON, array('Membre' => Membre::getBanque()));
		
		exit();
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
		
		
		if(isset($_POST['clean-all']))
		{
			//Fermer la connexion eDevoir
			Sql::disconnect();
			//Ouvrir la connexion root
			mysql_connect('localhost', 'root', $_POST['root-pw']);
			mysql_select_db(SQL_DB);
			mysql_set_charset('utf8');
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
				$Tables = mysql_query('SHOW TABLES');
				while($Table = mysql_fetch_row($Tables))
				{
					$TablesRestantes[] = $Table[0];
				}
				mysql_query('DROP TABLE ' . implode(', ', $TablesRestantes));
				
				if(count($TablesRestantes) == 0)
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
				'/data/CI',
				'/data/logs',
				'/data/ips',
				'/data/ips/ban',
				'/data/ips/try'
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
			$Actions[] = count($Requetes) . ' requêtes exécutées.';
			$Actions[] = 'Reprise des valeurs par défaut.';
			
			
			$Actions[] = 'Fin du nettoyage.';
			$this->View->Actions = $Actions;
		}
	}
}