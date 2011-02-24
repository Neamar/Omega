<?php
/**
 * eleveModule.php - 26 oct. 2010
 * 
 * Fichier de test de module : eleve.
 * Refuse l'exécution si l'élève n'est pas connecté et qu'on demande une autre page que /eleve/inscription
 * 
 * PHP Version 5
 * 
 * @category  ModuleCheck
 * @package   Root
 * @author    Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @copyright 2010 Matthieu Bacconnier
 * @license   Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link      http://edevoir.com
 */

//Soit on est déjà connecté et tout va bien

if(isset($_SESSION['Eleve']))
{
	//Mettre à jour l'objet :
	$_SESSION['Eleve'] = Eleve::load($_SESSION['Eleve']->ID);
	return true;
}
else 
{//Sinon on dégage sauf si on demande une page accessible hors ligne.
	$Allowed = array('inscription', 'connexion', 'validation', 'recuperation');
	
	if(in_array($_GET['view'], $Allowed))
	{
		return true;
	}
	else
	{
		//Enregistre la page demandée pour rediriger dessus après.
		$_SESSION['EleveComingFrom'] = $_SERVER['REQUEST_URI'];
		Debug::redirect('/eleve/connexion', 302);
	}
}

return false;
?>