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
	const ELEVE_INSCRIPTION = 'eleve/index/inscription';
	const ELEVE_EXERCICE_CREATION = 'eleve/exercice/creation';
	const ELEVE_EXERCICE_ENVOI = 'eleve/exercice/envoi';
	const ELEVE_EXERCICE_ACCEPTATION = 'eleve/exercice/acceptation';
	const ELEVE_EXERCICE_REFUS = 'eleve/exercice/refus';
	const ELEVE_EXERCICE_TERMINE = 'eleve/exercice/termine';
	
	const CORRECTEUR_INSCRIPTION = 'correcteur/inscription';
	const CORRECTEUR_EXERCICE_PROPOSITION = 'correcteur/exercice/proposition';
	const CORRECTEUR_EXERCICE_ENVOI = 'correcteur/exercice/envoi';
	
	const MEMBRE_POINTS_RETRAIT = 'membre/points/retrait';
	
	/**
	* Transmet un événement aux écouteurs associés.
	* @param string $Event l'évenement à dispatcher.
	* @param array $Params les paramètres à passer aux évènements.
	*/
	public static function dispatch($Event, array $Params = array())
	{
		//Sécuriser :
		$Event = str_replace('.', '', $Event);
		
		//Récupérer le chemin complet.
		$EventPath = OO2FS::eventPath($Event);
		//S'il y a des listeners associés :
		if(is_dir($EventPath))
		{
			//Les lister et les exécuter.
			$handle = opendir($EventPath);
			while (false !== ($file = readdir($handle)))
			{
				if ($file != "." && $file != "..")
				{
					include $EventPath . '/' . $file;
				}

			}
			closedir($handle);
        }

        //Logger le dispatch de l'évènement.
        self::log('Dispatch : ' . $Event);
	}

	/**
	* Logge un événement quelconque : crash d'une page, dispatch d'un événement, action externe, bref toute action potentiellement intéressante (et crashable).
	* Format : timestamp	IP	Auteur	Action
	* 
	* @param string $Event l'évènement
	* @param Membre $Membre le membre ayant enclenché l'action (non spécifié si inconnu ou non pertinent)
	*/
	public static function log($Event, Membre $Membre = null)
	{
		if(!is_null($Membre))
		{
			$Auteur = $Membre->Mail;
		}
		else
		{
			$Auteur = '-----';
		}

		$Ligne = date('H\hi\ms') . '	' . str_pad($_SERVER['REMOTE_ADDR'], 15) . '	' . str_pad($Auteur, 20) . '	' . $Event . "\n";
		
		$f = fopen(DATA_PATH . '/logs/' . date('Y-m-d') . '.log', 'a');
		fputs($f, $Ligne);
		fclose($f);
	}
}