<?php
/**
 * eleveRibbon.php - 20 déc. 2010
 * 
 * Le ruban s'affichant en haut de toutes les pages élèves.
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

$Retour['center'] = 'Espace Élève';

if(!isset($_SESSION['Eleve']))
{
	$Retour['left'] = 'Non connecté';
	$Retour['right'] = '<a href="/eleve/connexion">Connexion</a>';
	$Retour['links'] = array(
		'/' => 'Accueil',
		'/eleve/inscription' => 'Inscription élève',
		'/eleve/connexion' => 'Connexion élève'
	);
}
else
{
	$Retour['left'] = '<strong class="pts">' . $_SESSION['Eleve']->getPoints() . ' pts</strong>. <a href="/eleve/points/ajout">Ajouter des points</a>';
	$Retour['right'] = '<a href="/eleve/">' . $_SESSION['Eleve']->Mail . '</a><a href="/eleve/connexion" class="deconnexion"><img src="/public/images/global/deconnexion.png" alt="Déconnexion" title="Déconnexion" /></a>';
	
	$Retour['links'] = array(
		'/eleve/' => 'Accueil',
		'/eleve/exercice/' => 'Mes exercices',
		'/eleve/exercice/creation' => 'Créer un exercice',
		'/eleve/options/' => 'Options'
	);
}

return $Retour;