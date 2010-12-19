<?php
/**
 * OO2FS.php - 10 nov. 2010
 *
 * Offre des primitives accessibles pour la conversion representation objet / chemin système
 * OO2FS : objet oriented to filesystem.
 * Attention ; cette classe ne teste pas l'existence des fichiers, elle renvoie juste leur position potentielle dans la hiérarchie.
 *
 * PHP Version 5
 *
 * @category  Lib
 * @package   Root
 * @author    Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @copyright 2010 Matthieu Bacconnier
 * @license   Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link      http://devoirminute.com
 */

/**
 * Classe statique pour le lien système physique / virtuel.
 *
 * @category Lib
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://devoirminute.com
 *
 */
class OO2FS
{

	/**
	* Récupérer le chemin vers un validateur de module.
	*
	* @param string $Module le module recherché
	*
	* @return string /{$Module}/{$Module}Module.php
	*/
	public static function modulePath($Module)
	{
		return APPLICATION_PATH . '/' . $Module . '/' . $_GET['module'] . 'Module.php';
	}

	/**
	* Récupérer le chemin vers un contrôleur.
	*
	* @param string $Controller le contrôleur cherché
	* @param string $Module le module demandé
	*
	* @return string /{$Module}/{$Controller}/{$Controller}Controller.php
	*/
	public static function controllerPath($Controller, $Module)
	{
		return APPLICATION_PATH . '/' . $Module . '/' . $Controller . '/' . $Controller . 'Controller.php';
	}

	/**
	* Récupérer le nom de classe associé à un contrôleur
	*
	* @param string $Controller le contrôleur cherché
	* @param string $Module le module demandé
	*
	* @return string {$Module}_{$Controller}Controller
	*/
	public static function controllerClass($Controller, $Module)
	{
		return ucfirst($Module) . '_' . ucfirst($Controller) . 'Controller';
	}

	/**
	* Récupérer le chemin vers une vue
	*
	* @param string $View la vue désirée
	* @param string $Data les données (ou non)
	* @param string $Controller le contrôleur cherché
	* @param string $Module le module demandé
	*
	* @return string /{$Module}/{$Controller}/views/{$View}.phtml
	*/
	public static function viewPath($View, $Data, $Controller, $Module)
	{
		return APPLICATION_PATH . '/' . $Module . '/' . $Controller . '/views/' . $View . (empty($Data)?'':'_wd') . '.phtml';
	}

	/**
	* Récupérer le nom d'une fonction de vue
	*
	* @param string $View la vue désirée
	* @param string $Data les données (ou non)
	* @param string $Controller le contrôleur cherché
	* @param string $Module le module demandé
	*
	* @return string {$View}Action ou {$View}Action_wd
	*/
	public static function viewFunction($View, $Data, $Controller, $Module)
	{
		return $View . 'Action' . (empty($Data)?'':'_wd');
	}

}