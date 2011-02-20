<?php 
/**
 * Points.php - 22 janv. 2011
 *
 * Offrir des primitives de haut niveau pour le contrôleur de points.
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
 * Affiche un nombre de points sur le site
 * 
 * @param int $Points
 * @param string $Unite l'unité à afficher après. Un "s" est automatiquement ajouté si nécessaire
 * 
 * @return une structure HTML
 */
function ViewHelper_Points($Points, $Unite = 'point')
{
	return '<span class="pts">' . $Points . '&nbsp;' . $Unite . ($Points>1?'s':'') . '</span>';
}

/**
 * Convertit une valeur en points en euros
 * 
 * @param int $Points les points à transformer
 * 
 * @return float le nombre d'euros correspondants arrondis à la deuxième décimale
 */
function ViewHelper_Points_euros($Points)
{
	return number_format($Points / EQUIVALENCE_POINT, 2, ',', ' ') . '&nbsp;€';
}

/**
 * Affiche un message important si la création d'un exercice entraîne un surcout
 * 
 * @param Eleve $Eleve l'élève à tester
 * 
 * @return string
 */
function ViewHelper_Points_raiseWarning(Eleve $Eleve)
{
	$Raise = $Eleve->getRaise();
	$Nombres = array('', 'un', 'deux', 'trois', 'quatre', 'plus de quatre');
	$Nombre = ($Raise - 100) / 10;
	
	if($Raise > 100)
	{
		if(!function_exists('ViewHelper_Doc_link'))
		{
			include OO2FS::viewHelperPath('Doc');
		}
		
		return '<p class="important">Attention ! Vous avez déjà soumis ' . $Nombres[$Nombre] . ' exercice' . ($Nombre > 1?'s':'') . ' dans les sept derniers jours.<br />
N\'oubliez pas que nous sommes là pour aider ponctuellement, pas pour valider la ' . strtolower($Eleve->DetailsClasse) . ' à votre place ! En conséquence, une majoration de ' . ($Raise - 100) . '% s\'applique.<br />
	' . ViewHelper_Doc_anchor('eleve', 'supplement') . '
		</p>';	
	}
	else
	{
		return '';
	}
}