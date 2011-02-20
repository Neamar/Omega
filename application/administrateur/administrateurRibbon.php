<?php
/**
 * administrateurRibbon.php - 12 fév. 2010
 * 
 * Le ruban s'affichant en haut de toutes les pages administrateurs.
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
$Retour['center'] = 'Administration';

if(!isset($_SESSION['Administrateur']))
{
	$Retour['left'] = '';
	$Retour['right'] = '<a href="/administrateur/connexion">Connexion</a>';
	$Retour['links'] = array(
		'/' => 'Accueil eDevoir',
	);
}
else
{
	$Retour['left'] = '<a href="/administrateur/connexion" class="deconnexion"><img src="/public/images/global/deconnexion.png" alt="Déconnexion" /></a> <a href="/correcteur/">' . $_SESSION['Administrateur']->Mail . '</a>';
	$Retour['right'] = '<a href="/administrateur/">Espace admin</a>';
	$Retour['links'] = array(
		'/administrateur/' => 'Admin',
		'/administrateur/log/' => 'Logs',
		'/administrateur/exercice/' => 'Exercices',
		'/administrateur/membres/' => 'Membres',
	);
}

return $Retour;