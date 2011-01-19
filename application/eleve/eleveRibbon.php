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

if(!isset($_SESSION['Eleve']))
{
	$Retour['left'] = 'Non connecté';
	$Retour['right'] = '<a href="/eleve/connexion">Connexion</a>';
	$Retour['links'] = array(
		'/' => 'Accueil',
		'/eleve/inscription' => 'Inscription correcteur',
		'/eleve/connexion' => 'Connexion correcteur'
	);
}
else
{
	$Retour['left'] = '<a href="/eleve/connexion" class="deconnexion">Déconnexion</a> <a href="/eleve/">' . $_SESSION['Eleve']->Mail . '</a>';
	$Retour['right'] = $_SESSION['Eleve']->getPoints() . ' pts. <a href="/eleve/points/ajout">Ajouter des points</a>';
	$Retour['links'] = array(
		'/eleve/' => 'Accueil élève',
		'/eleve/exercice/' => 'Mes exercices',
		'/eleve/exercice/creation' => 'Créer un exercice'
	);
}

return $Retour;