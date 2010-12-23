<?php
/**
 * bootstrap.php - 26 oct. 2010
 * 
 * Fichier de base du site pour toutes les requêtes dynamiques.
 * 
 * PHP Version 5
 * 
 * @category  Default
 * @package   Root
 * @author    Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @copyright 2010 Matthieu Bacconnier
 * @license   Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link      http://devoirminute.com
 * @param string view la vue à appeler
 * @param string controller le contrôleur à appeler
 * @param string module le module actuel
 * @param string data les données additionnelles.
 */

//TODO : modifier SQL : Classes.Id en Classes.Classe pour une meilleure cohérence. 
/**
 * Strict minimum à définir pour un site fonctionnel
 * 
 */

$File = str_replace('\\', '/', __FILE__);
define('PATH', substr($File, 0, strrpos($File, '/')));
include PATH . '/lib/core/constants.php';
include PATH . '/lib/core/OO2FS.php';
include PATH . '/lib/core/Sql.php';

session_start();



$ModulePath = OO2FS::modulePath($_GET['module']);
$ControllerPath = OO2FS::controllerPath($_GET['controller'], $_GET['module']);
$ControllerName = OO2FS::controllerClass($_GET['controller'], $_GET['module']);
$ViewPath = OO2FS::viewPath($_GET['view'], $_GET['data'], $_GET['controller'], $_GET['module']);
$ViewName = OO2FS::viewFunction($_GET['view'], $_GET['data'], $_GET['controller'], $_GET['module']);

//Traiter les données si existantes
if(!empty($_GET['data']))
{
	$Components = explode('/', $_GET['data']);
	if(count($Components) % 2 == 1)
	{
		array_unshift($Components, 'data');
	}
	$Components = array_chunk($Components, 2);
	
	$_GET['data']=array();
	foreach($Components as $Component)
	{
		$_GET['data'][$Component[0]] = $Component[1];	
	}
	unset($Components, $Component);
}
/**
 * Définition de l'autoload
 * 
 * @param string $ClassName la classe à charger dynamiquement.
 * 
 * @return string le code retour de l'inclusion du fichier contenant la classe
 */

function __autoload($ClassName)
{
	$Models = array('DbObject','Exercice','Membre','Correcteur','Eleve',);
	
	$FileName = $ClassName . '.php';
	if(substr($ClassName, -18)=='AbstractController')
	{
		return include LIB_PATH . '/AbstractController/' . $FileName;
	}
	if(in_array($ClassName, $Models))
	{
		return  include LIB_PATH . '/Models/' . $FileName;
	}
	
	return include LIB_PATH . '/' . $FileName;
}
//Démarrer le gestionnaire d'erreurs
set_error_handler('Debug::errHandler', -1);

//Connecter le serveur SQL
Sql::connect();













/**
 * Test de base pour la validité de la page.
 */
//Paramètres fournis
if(!isset($_GET['view'], $_GET['controller'], $_GET['module'], $_GET['data']))
{
	exit('Appel incorrect.');
}
	
//Vérifier que le module existe et lui demander s'il accepte la demande.
if(!is_file($ModulePath))
{
	exit("Le module n'existe pas !");
}
if(!include $ModulePath)
{
	exit('Le module refuse de s\'exécuter.');
}

//Vérifier l'existence du contrôleur.
if(!is_file($ControllerPath))
{
	exit('Ce contrôleur n\'existe pas.');
}

//Charger le contrôleur :
include $ControllerPath;
if(!method_exists($ControllerName, $ViewName))
{
	exit('Vue incorrecte : ' . $ViewName);
}	
	

	
	
	
	
	
	

/**
 * Exploitation.
 */
//Chargement du contrôleur
$Controller = new $ControllerName($_GET['module'], $_GET['controller'], $_GET['view'], $_GET['data']);
//Exécution du contrôleur
$Controller->$ViewName();
//Rendu de la vue
$Controller->renderView();