<?php
/**
 * Debug.php - 26 oct. 2010
 * 
 * Offrir des fonctions faciles d'accès pour le débuggage.
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
 * Classe de debug.
 * 
 * @category Lib
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://devoirminute.com
 */
class Debug
{
	/**
	* Appelé quand une requête SQL produit une erreur.
	* Affiche l'erreur SQL et la pile d'appel.
	*/
	public static function sqlFail()
	{
		self::fail(SQL::error());
	}

	/**
	* Appelé quand une erreur se produit / est déclenchée par le code et nécessite l'affichage d'un message d'erreur.
	* Note : cette fonction est préférable à exit() car elle facilite le débuggage et le trace des erreurs en production.
	* @param Msg:String Le message à afficher
	* @return :void La fonction ne retourne jamais, le script est interrompu.
	*/
	public static function fail($Msg)
	{

		echo '<p style="border:1px dashed red;"><strong>Erreur : </strong>' . $Msg . '</p>';

		$trace = self::_getDebugLog();
		
		exit('<pre>' . $trace . '</pre>');
	}

	/**
	* Gestionnaire personnalisé d'erreurs défini avec set_error_handler.
	* Permet d'éviter d'afficher les erreurs PHP à l'utilisateur, et de récupérer proprement, par exemple en affichant une page d'erreur et en envoyant un mail à l'administrateur avec le contexte de l'erreur.
	* @param errno:int le numéro de l'erreur, inutile.
	* @param errstr:String la description de l'erreur, plus utile ;)
	* @param errfile:String le fichier dans lequel s'est produit l'erreur
	* @param errline:int la ligne du fichier
	* @param errcontext:array un gros tableau (très gros tableau même dans la plupart des cas) qui contient la liste des variables définies au moment du bug.
	* @return :void La fonction ne retourne jamais, le script est interrompu.
	*/
	public static function errHandler($errno, $errstr, $errfile, $errline, array $errcontext)
	{
		if(error_reporting()!=0)
		{
			Debug::fail('Erreur PHP : ' . $errstr);
		}
	}

	/**
	* Arrête le script sans message, par exemple suite à une redirection HTML.
	* @return :void La fonction ne retourne jamais, le script est interrompu.
	*/
	public static function stop()
	{
		exit();
	}

	/**
	 * Codes connus et autorisés.
	 * Cf. http://gif.phpnet.org/frederic/programs/http_status_codes/
	 * @var array
	 */
	private static $_Codes=array
	(
		200=>'OK',
		301=>'Moved Permanently',
		302=>'Found',
		404=>'Not Found',
		403=>'Forbidden',
	);

	/**
	* Arrête le script en faisant une redirection HTML avec le code d'erreur spécifié.
	* @param Location:String le chemin absolu du nouvel emplacement
	* @param Code:Int le code d'erreur à renvoyer ; 301 si non spécifié.
	* @return :void La fonction ne retourne jamais, le script est interrompu.
	*/
	public static function redirect($Location,$Code=301)
	{
		self::status($Code);
		header('Location: ' . URL . $Location);
		self::stop();
	}

	/**
	* Change le code HTTP associé à la page.
	* S'il s'agit d'un code d'erreur, l'exécution du script est déviée vers la page de gestion d'erreur.
	* @param Code:int Le nouveau code qui remplace l'ancien.
	* @example
	*	//Depuis un contrôleur
	*	//Renvoie un code 404, ce qui changera la page demandée pour erreur lors du chargement des modèles et vues.
	*	//Le return est important pour arrêter le premier contrôleur.
	* 	return Debug::status(404);
	*/
	public static function status($Code)
	{
		if(!isset(self::$Codes[$Code]))
		{
			self::fail('Code inconnu.');
		}

		header('Status: ' . $Code. ' ' . self::$Codes[$Code], true, $Code);

		//Gestion des 403,404,500
		if($Code>400)
		{
			$_GET['Erreur'] = $Code;
			$_GET['P']='erreur';

			Event::log('==Statut ' . $Code . ' sur ' . $_SERVER['REQUEST_URI'] . (isset($_SERVER['HTTP_REFERER'])?' (referer : '  . $_SERVER['HTTP_REFERER'] . ')':''));
		}
	}

	private static function _getDebugLog()
	{
		ob_start();
		echo '<strong>PAGE : </strong>' . '<a href="' . URL . $_SERVER['REQUEST_URI'] . '">' . $_SERVER['REQUEST_URI'] . '</a>';
		echo "\n\n\n";

		if(isset($_SERVER['HTTP_REFERER']))
		{
			echo '<strong>REFERRER : </strong>' . $_SERVER['HTTP_REFERER'];
			echo "\n\n\n";
		}

		echo '<strong>SESSION : </strong>';
		print_r($_SESSION);
		echo "\n\n\n";

		echo '<strong>TRACE : </strong>';
		debug_print_backtrace();
		echo "\n\n\n";

		echo '<strong>SERVEUR : </strong>';
		print_r($_SERVER);
		echo "\n\n\n";

		return preg_replace('`#0 .+ called at`', 'Erreur sur', ob_get_clean());
	}
}