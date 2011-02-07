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
 * @link      http://edevoir.com
 */

/**
 * Classe statique pour le lien système physique / virtuel.
 *
 * @category Lib
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
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
		return APPLICATION_PATH . '/' . $Module . '/' . $Module . 'Module.php';
	}

	/**
	 * Renvoie le chemin vers le ruban de haut de page du module.
	 *
	 * @param string $Module
	 *
	 * @return string /{$Module}/{$Module}Ribbon.php
	 */
	public static function ribbonPath($Module)
	{
		return APPLICATION_PATH . '/' . $Module . '/' . $Module . 'Ribbon.php';
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
	 * Renvoie le chemin vers une vue générique
	 *
	 * @param string $GenericView la vue générique
	 *
	 * @return  /lib/View/{$View}.phtml
	 */
	public static function genericViewPath($GenericView)
	{
		return LIB_PATH . '/views/' . $GenericView . '.phtml';
	}

	/**
	 * Renvoie le chemin vers un dossier d'évènements
	 *
	 * @param string $Event l'évènement
	 *
	 * @return  /lib/Event/{$Event}
	 */
	public static function eventPath($Event)
	{
		return LIB_PATH . '/events/' . $Event;
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
		return $View . 'Action' . (empty($Data)?'':'Wd');
	}

	/**
	* Récupérer le chemin vers une aide vue
	*
	* @param string $ViewHelper le module d'aide de vue désiré
	*
	* @return string /lib/ViewHelper/{$ViewHelper}.php
	*/
	public static function viewHelperPath($ViewHelper)
	{
		return LIB_PATH . '/viewhelpers/' . $ViewHelper . '.php';
	}

}