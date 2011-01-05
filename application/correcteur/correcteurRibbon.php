<?php
/**
 * correcteurRibbon.php - 30 déc. 2010
 * 
 * Le ruban s'affichant en haut de toutes les pages correcteurs.
 * 
 * PHP Version 5
 * 
 * @category  View
 * @package   Root
 * @author    Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @copyright 2010 Matthieu Bacconnier
 * @license   Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link      http://edevoir.com
 */

$Retour = array();

if(!isset($_SESSION['Correcteur']))
{
	$Retour[] = 'Visiteur non connecté';
	$Retour[] = '<a href="/correcteur/connexion">Connexion</a>';
}
else
{
	$Retour[] = '<a href="/correcteur/" class="correcteur-home">' . $_SESSION['Correcteur']->Mail . '</a>';
	$Retour[] = '<a href="/correcteur/connexion" class="deconnexion">Déconnexion</a>';
}

return $Retour;