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
$Retour['center'] = 'Correcteur';

if(!isset($_SESSION['Correcteur']))
{
	$Retour['left'] = 'Non connecté';
	$Retour['right'] = '<a href="/correcteur/connexion">Connexion</a>';
	$Retour['links'] = array(
		'/' => 'Accueil',
		'/correcteur/inscription' => 'Inscription correcteur',
		'/correcteur/connexion' => 'Connexion correcteur'
	);
}
else
{
	$Retour['left'] = '<a href="/correcteur/connexion" class="deconnexion"><img src="/public/images/global/deconnexion.png" alt="Déconnexion" /></a> <a href="/correcteur/">' . $_SESSION['Correcteur']->Mail . '</a>';
	$Retour['right'] = '<strong class="pts">' . $_SESSION['Correcteur']->getPoints() . ' pts</strong>. <a href="/correcteur/points/retrait">Retirer des points</a>';
	$Retour['links'] = array(
		'/correcteur/' => 'Accueil',
		'/correcteur/liste' => 'Foire aux exercices',
		'/correcteur/exercice/' => 'Mes exercices',
		'/correcteur/options/' => 'Options',
	);
}

return $Retour;