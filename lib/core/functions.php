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
	include(PATH . '/404.php');
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
	file_put_contents(DATA_PATH . '/.lock',$Reason);
	exit();
}