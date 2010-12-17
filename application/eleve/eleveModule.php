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
 * @link      http://devoirminute.com
 */

//Soit on est déjà connecté et tout va bien

if(isset($_SESSION['Eleve']))
{
	return true;
}
else 
{//Sinon on dégage sauf si on demande une page accessible hors ligne.
	$Allowed = array('inscription','connexion');
	
	if(in_array($_GET['view'],$Allowed))
	{
		return true;
	}
	else
	{
		Debug::redirect('/eleve/connexion',302);
	}
}

return false;
?>