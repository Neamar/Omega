<?php
/**
 * functions.php - 25 fév. 2011
 *
 * Les fonctions globales disponibles dans tout le site ;
 * réduites au strict minimum pour éviter d'encombrer l'espace de nom
 *
 * PHP Version 5
 *
 * @category  Controller
 * @package   Root
 * @author    Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @copyright 2011 Matthieu Bacconnier
 * @license   Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link      http://edevoir.com
 */


/**
 * Définition de l'autoload
 *http://edevoir.com/correcteur/exercice/
 * @param string $ClassName la classe à charger dynamiquement.
 *
 * @return string le code retour de l'inclusion du fichier contenant la classe
 */
function __autoload($ClassName)
{
	$Models = array('DbObject', 'Exercice', 'Membre', 'Correcteur', 'Eleve', 'Administrateur');

	$FileName = $ClassName . '.php';
	if(substr($ClassName, -18) == 'AbstractController')
	{
		return include LIB_PATH . '/abstractcontrollers/' . $FileName;
	}
	if(in_array($ClassName, $Models))
	{
		return  include LIB_PATH . '/models/' . $FileName;
	}

	return include LIB_PATH . '/' . $FileName;
}

/**
 * Cette fonction permet de basculer sur une page d'erreur de type 404.
 * On la définit tôt afin de pouvoir s'en servir dans toutes les situations.
 * 
 * @param string $Message
 * @param int $Code le statut à renvoyer
 */
function go404($Message, $Code = 404)
{
	Event::log('Statut ' . $Code . '	' . $Message);
	if(!defined('NO_404'))
	{
		include PATH . '/404.php';
	}
	else
	{
		exit('<p class="crash">' . $Message . '</p>');
	}
}


/**
 * Sécuriser l'entrée des données
 * 
 * @param string $Valeur une valeur à protéger
 * 
 * @return string la valeur échappée de son contenu dangereux
 */
function sanitize($Valeur)
{
	return str_replace(array('.', '/'), '', $Valeur);
}

/**
 * Verrouille le site pour une inspection ultérieure.
 * Attention, fonction critique !
 * 
 * @param string $Reason la raison du lock
 * 
 * @return jamais
 */
function lock($Reason)
{
	file_put_contents(DATA_PATH . '/.lock', $Reason);
	External::report('Faille critique du site ! ' . $Reason, debug_backtrace());
	exit();
}

/**
 * Fonction appelée quand PHP déclenche une erreur.
 * @see set_error_handler()
 * @see Bootstrap.php
 * 
 * @param int $errno contains the level of the error raised, as an integer. 
 * @param string $errstr contains the error message, as a string.
 * @param string $errfile contains the filename that the error was raised in, as a string. 
 * @param int $errline contains the line number the error was raised at, as an integer. 
 */
function error($errno, $errstr, $errfile, $errline)
{
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

/**
 * Arrête le script en faisant une redirection HTML avec le code d'erreur spécifié.
 * 
 * @param string $Location le chemin absolu du nouvel emplacement
 * 
 * @return void La fonction ne retourne jamais, le script est interrompu.
 */
function redirect($Location, $Code = null)
{
	$Codes = array
	(
		200 => 'OK',
		301 => 'Moved Permanently',
		302 => 'Found',
	);
	if(!is_null($Code))
	{
		header('Status: ' . $Code. ' ' . $Codes[$Code], true, $Code);
	}
	
	header('Location: ' . URL . $Location);
	
	//Forcer l'écriture des données de la session maintenant.
	//En effet, l'envoi d'un mail via register_shutdown_function peut bloquer l'écriture de la session pendant une durée supérieure à la redirection.
	//Les sessions n'étant pas concurrentes, les potentiels messages peuvent être perdus.
	//@see http://fr2.php.net/manual/en/function.session-write-close.php
	session_write_close();
	
	//Terminer l'exécution du script pour éviter de fatiguer le serveur sur une page que personne ne consultera.
	exit();
}