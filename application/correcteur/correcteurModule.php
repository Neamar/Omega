<?php
/**
 * correcteurModule.php - 30 déc. 2010
 * 
 * Fichier de test de module : correcteur.
 * Refuse l'exécution si le correcteur n'est pas connecté et qu'on demande une autre page que /correcteur/inscription
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

if(isset($_SESSION['Correcteur']))
{
	//Mettre à jour l'objet :
	$_SESSION['Correcteur'] = Correcteur::load($_SESSION['Correcteur']->ID);
	return true;
}
else 
{//Sinon on dégage sauf si on demande une page accessible hors ligne.
	$Allowed = array('inscription', 'connexion', 'recuperation', '_ressource');
	
	if(in_array($_GET['view'], $Allowed))
	{
		return true;
	}
	else
	{
		//Enregistre la page demandée pour rediriger dessus après.
		$_SESSION['CorrecteurComingFrom'] = $_SERVER['REQUEST_URI'];
		redirect('/correcteur/connexion', 302);
	}
}

return false;
?>