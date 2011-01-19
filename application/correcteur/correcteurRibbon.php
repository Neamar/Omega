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
	$Retour['left'] = '<a href="/correcteur/connexion" class="deconnexion">Déconnexion</a> <a href="/correcteur/">' . $_SESSION['Correcteur']->Mail . '</a>';
	$Retour['right'] = $_SESSION['Correcteur']->getPoints() . ' pts. <a href="/correcteur/points/retrait">Retirer des points</a>';
	$Retour['links'] = array(
		'/correcteur/' => 'Accueil correcteur',
		'/correcteur/liste' => 'Foire aux exercices',
	);
}

return $Retour;