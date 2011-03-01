<?php
/**
 * administrateurModule.php - 12 fév. 2011
 * 
 * Fichier de test de module : administrateur.
 * Refuse l'exécution si l'administrateur n'est pas connecté et qu'on demande une autre page que /administrateur/connexion
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

if(isset($_SESSION['Administrateur']))
{
	//Mettre à jour l'objet :
	$_SESSION['Administrateur'] = Administrateur::load($_SESSION['Administrateur']->ID);
	
	//Le module administrateur permet potentiellement de faire des actions extrêmement dangereuses.
	//Pour éviter tout problème, on vérifie à chaque chargement de pages l'intégrité du site.
	if(!Sql::constraint())
	{
		lock('Problème de cohérence relationnelle détectée chez les administrateurs ; site verrouillé pour vérifications.');
	}
	return true;
}
else 
{//Sinon on dégage sauf si on demande une page accessible hors ligne.
	$Allowed = array('connexion');
	
	if(in_array($_GET['view'], $Allowed))
	{
		return true;
	}
	else
	{
		redirect('/administrateur/connexion', 302);
	}
}

return false;
?>