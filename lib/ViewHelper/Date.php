<?php 
/**
 * Date.php - 23 déc. 2010
 *
 * Offrir des primitives de haut niveau pour l'affichage des dates.
 *
 * PHP Version 5
 *
 * @category  ViewHelper
 * @package   Root
 * @author    Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @copyright 2010 Matthieu Bacconnier
 * @license   Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link      http://edevoir.com
 */

/**
 * Renvoie un objet countdown mettant à jour dynamiquement le temps restant avant l'échéance indiquée.
 * 
 * @param mixed $Date le timestamp d'échéance. Si non numérique, la fonction tentera de "comprendre" la chaîne pour en extraire le timestamp.
 */
function ViewHelper_Date_countdown($Date)
{
	if(!is_numeric($Date))
	{
		$Date = strtotime($Date);
	}
	
	return '<time datetime="' . date('Y-m-d\TH:00+01:00', $Date) . '" class="date">' . date('d/m/y à H\h', $Date) . '</time>';
}

/**
 * Renvoie un objet countdown mettant à jour dynamiquement le temps restant avant l'échéance indiquée.
 * Cette fonction prend en compte les minutes.
 * @param mixed $Date le timestamp d'échéance. Si non numérique, la fonction tentera de "comprendre" la chaîne pour en extraire le timestamp.
 */
function ViewHelper_Date_countdownFull($Date)
{
	if(!is_numeric($Date))
	{
		$Date = strtotime($Date);
	}
	
	return '<time datetime="' . date('Y-m-d\TH:i+01:00', $Date) . '" class="date">' . date('d/m/y à H\hi\m', $Date) . '</time>';
}