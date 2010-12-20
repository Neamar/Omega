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
	$Retour[] = 'Visiteur non connecté';
	$Retour[] = '<a href="/eleve/connexion">Connexion</a>';
}
else
{
	$Retour[] = '<a href="/eleve/" class="eleve-home">' . $_SESSION['Eleve']->Mail . '</a>';
	$Retour[] = '<a href="/eleve/connexion" class="deconnexion">Déconnexion</a>';
	$Retour[] = $_SESSION['Eleve']->getPoints() . '&nbsp;pts. <a href="/eleve/points/ajout">Ajouter des points</a>';
}

return $Retour;