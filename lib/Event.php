<?php
/**
 * Event.php - 11 déc. 2010
 * 
 * Simuler une gestion d'évenements (basique) en PHP.
 * 
 * PHP Version 5
 * 
 * @category  Default
 * @package   Root
 * @author    Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @copyright 2010 Matthieu Bacconnier
 * @license   Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link      http://edevoir.com
 * @param string
 * @param string
 */
 
/**
 * Permet d'enregistrer des actions, et de déclencher des fonctions sur ces actions.
 *
 * @category Default
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 */
class Event
{
	const NOUVEAU = 'Création de l\'omnilogisme';
	const EDITION = 'Correction des erreurs';
	const TAGGAGE = 'Modification des mots clés de l\'omnilogisme';
	const LOADING = 'Chargement des données distantes';
	const ACCROCHAGE = 'Ajout d\'une accroche';
	const PARUTION = 'Parution officielle de l\'omnilogisme';
	const ACCEPTE = 'Statut changé vers ACCEPTE';
	const BANNIERE = 'Ajout d\'une bannière';
	const A_CORRIGER = 'Statut changé vers A_CORRIGER';
	const REF = 'Modification des références';
	const CHANGEMENT_GENERIQUE = 'Modification d\'une des données de l\'article';

	//Constantes non associées à un article en particulier
	const NOUVELLE_PROPOSITION = 'Nouvelle proposition';
	const NOUVELLE_CATEGORIE = 'Nouvelle catégorie';

	private static $Events = array();

	/**
	* Transmet un événement aux écouteurs associés.
	* @param Event:String l'évenement à dispatcher. Théoriquement une constante statique de Event ;)
	* @param Article:Omni l'article concerné par la modification. Dans certains cas particuliers, il s'agit d'une info oncohérente (par exemple à l'enregistrement d'une proposition) auquel cas $Article est considéré comme null.
	*/
	public static function dispatch($Event, Omni $Article = null)
	{
		if(count(self::$Events)==0)
			self::buildEvents();

		$EventType = array_search($Event,self::$Events);

		//S'il y a des listeners associés :
		if($EventType!=false && is_dir(PATH . '/E/' . strtolower($EventType)))
		{

			//Les lister et les exécuter.
			$handle = opendir(PATH . '/E/' .strtolower($EventType));
			while (false !== ($file = readdir($handle)))
			{
				if ($file != "." && $file != "..")
					include(PATH . '/E/' .strtolower($EventType) . '/' . $file);

			}
			closedir($handle);
        }

        self::log('Dispatch : ' . ($EventType==false?$Event:$EventType) . (!is_null($Article)?' sur ' . $Article->Titre:''));
	}

	/**
	* Appelle un évenement générique.
	* Les événements génériques sont des évènements qui peuvent être déclenchés de plusieurs façons ; pour éviter de dupliquer le code on les met dans le dossier _generic.
	* @param $File le nom du fichier
	* @example
	* //Cette ligne redispatche l'évenement sur un fichier générique de même nom.
	* Event::callGeneric(basename(__FILE__));
	*/
	public static function callGeneric($File)
	{
		include(PATH . '/E/generique/' . $File);
	}

	/**
	* Logge un événement quelconque : crash d'une page, dispatche d'un événement, action externe, bref toute action potentiellement intéressante (et crashable).
	* Format : timestamp	IP	Auteur	Action
	*/
	public static function log($Event)
	{
		if(isset($_SESSION['Membre']['Pseudo']))
			$Auteur = $_SESSION['Membre']['Pseudo'];
		else
			$Auteur = '-----';

		$Ligne = date('H\hi\ms') . '	' . str_pad($_SERVER['REMOTE_ADDR'],15) . '	' . str_pad($Auteur,12) . '	' . $Event . "\n";
		
		$f = fopen(DATA_PATH . '/logs/' . date('Y-m-d') . '.log','a');
		fputs($f,$Ligne);
		fclose($f);
	}

	/**
	* Transforme les constantes en tableau pour les manipuler facilement.
	*/
	private static function buildEvents()
	{
		$oClass = new ReflectionClass ('Event');
		self::$Events = $oClass->getConstants ();
	}
}