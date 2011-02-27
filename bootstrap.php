<?php
/**
 * bootstrap.php - 26 oct. 2010
 *
 * Fichier de base du site pour toutes les requêtes dynamiques.
 *
 * Fonctionnement d'une requête :
 * - On extrait toutes les composantes de l'URL
 * - On vérifie leurs validités respectives
 * - On charge le fichier de module pour vérifier que l'utilisateur a le droit d'afficher la page
 * - On appelle ensuite l'action qui sert à la fois de module et de contrôleur MVC
 * - On rend la vue :
 * 	- on appelle la méthode render() de la vue associée au contrôleur
 * 	- cette méthode charge template.phtml :
 * 		- template.phtml construit la page en appelant les méthodes appropriées : renderRibbon, renderHead() de la vue
 * 		- template.phtml inclut le fichier de vue
 *
 * PHP Version 5
 *
 * @category  Default
 * @package   Root
 * @author    Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @copyright 2010 Matthieu Bacconnier
 * @license   Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link      http://edevoir.com
 * @param string view la vue à appeler
 * @param string controller le contrôleur à appeler
 * @param string module le module actuel
 * @param string data les données additionnelles.
 */

/**
 * Strict minimum à définir pour un site fonctionnel
 *
 */

$File = str_replace('\\', '/', __FILE__);
define('PATH', substr($File, 0, strrpos($File, '/')));
include PATH . '/lib/core/constants.php';
include PATH . '/lib/core/functions.php';
include PATH . '/lib/core/OO2FS.php';
include PATH . '/lib/core/Sql.php';
session_start();


//$_GET = array_map('sanitize', $_GET) ne peut pas fonctionner car il échapperait aussi data.
$_GET['view'] = sanitize($_GET['view']);
$_GET['controller'] = sanitize($_GET['controller']);
$_GET['module'] = sanitize($_GET['module']);



//Vérifier que le site n'est pas verrouillé (en maintenance, ou erreur critique)
if(file_exists(DATA_PATH . '/.lock'))
{
	go404(file_get_contents(DATA_PATH . '/.lock'), 500);
}

//Démarrer le gestionnaire d'erreurs
set_error_handler('Debug::errHandler', -1);


/**
 * Lecture des données de la requête
 *
 */
$_GET['module'] = strtolower($_GET['module']);
$ModulePath = OO2FS::modulePath($_GET['module']);
$ControllerPath = OO2FS::controllerPath($_GET['controller'], $_GET['module']);
$ControllerName = OO2FS::controllerClass($_GET['controller'], $_GET['module']);
$ViewPath = OO2FS::viewPath($_GET['view'], $_GET['data'], $_GET['controller'], $_GET['module']);
$ViewName = OO2FS::viewFunction($_GET['view'], $_GET['data'], $_GET['controller'], $_GET['module']);

$_GET['data']= AbstractController::buildData($_GET['data']);

//Connecter le serveur SQL
Sql::connect();

//Vérifier que l'IP n'est pas bannie
if(file_exists(DATA_PATH . '/ips/bans/' . $_SERVER['REMOTE_ADDR']))
{
	$Banni = file_get_contents(DATA_PATH . '/ips/bans/' . $_SERVER['REMOTE_ADDR']);
	if($Banni < time())
	{
		//Débannir
		unlink(DATA_PATH . '/ips/bans/' . $_SERVER['REMOTE_ADDR']);
	}
	else
	{
		go404('Vous êtes banni !<br />Le serveur refuse de parler à ' . $_SERVER['REMOTE_ADDR'] . ' jusqu\'au ' . date('d/m/y à G\hi', $Banni) . '.', 500);
	}
}












/**
 * Test de base pour la validité de la page.
 */
//Paramètres fournis
if(!isset($_GET['view'], $_GET['controller'], $_GET['module'], $_GET['data']))
{
	go404('Cet appel est incorrect ; vous n\'utilisez probablement pas la méthode standard de consultation des pages.');
}

//Vérifier que le module existe et lui demander s'il accepte la demande.
if(!is_file($ModulePath))
{
	go404("Ce module n'existe pas !");
}
if(!include $ModulePath)
{
	go404('Ce module refuse de s\'exécuter pour vous. Inutile de lui faire du charme, il ne craquera pas.', 403);
}

//Vérifier l'existence du contrôleur.
if(!is_file($ControllerPath))
{
	go404("Ce contrôleur n'existe pas. Peut-être dans le futur... si vous avec une machine temporelle, n'hésitez pas à y aller (merci de me ramener le code source).");
}

//Charger le contrôleur :
include $ControllerPath;
if(!method_exists($ControllerName, $ViewName) && !method_exists($ControllerName, '__call'))
{
	//Tester si un contrôleur existe avec ce nom
	//Par exemple, /correcteur/points => /correcteur/points/
	$ControleurPotentiel = OO2FS::controllerPath($_GET['view'], $_GET['module']);
	if(is_file($ControleurPotentiel))
	{
		header('Location:' . URL . '/' . $_GET['module'] . '/' . $_GET['view'] . '/');
	}
	
	go404('Impossible de charger cette page. Le module existe, le contrôleur existe... mais pas la page.');
}









/**
 * Exploitation.
 */
//Chargement du contrôleur
$Controller = new $ControllerName($_GET['module'], $_GET['controller'], $_GET['view'], $_GET['data']);
//Exécution du modèle
$Controller->$ViewName();
//Rendu de la vue
$Controller->renderView();