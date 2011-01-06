<?php
/**
 * View.php - 1 janv. 2011
 * 
 * Permettre de manipuler des vues pour les rendre.
 * 
 * PHP Version 5
 * 
 * @category  ViewHelper
 * @package   Root
 * @author    Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @copyright 2010 Matthieu Bacconnier
 * @license   Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link      http://edevoir.com
 */

/**
 * Renvoie un objet countdown mettant à jour dynamiquement le temps restant avant l'échéance indiquée.
 * Contrat, ne traite pas les erreurs en cas de paramètres incorrects.
 * Contrat, ne traite pas la récursion.
 * 
 * @param View $ViewObject la vue contenant les données
 * @param int $DeltaH le décalage à imposer aux éléments de titre (DeltaH = 2 : tous les h1 deviennent des h3)
 * @param bool $Message s'il faut afficher le message de la vue
 * 
 * @return string le contenu de la vue
 */
function ViewHelper_View_render(View $ViewObject, $DeltaH = 0, $Message = true)
{
	ob_start();
	
	if($Message)
	{
		echo $ViewObject->renderMessage();
	}
	
	$ViewObject->renderContent();
	
	$R = ob_get_clean();
	
	if($DeltaH != 0)
	{
		$R = preg_replace_callback(
			'`<(/)?h([1-6])>`', 
			create_function
			(
				'$H',
				'return "<" . $H[1] . "h" . ($H[2] + ' . $DeltaH . ') . ">";'
			),
			$R);
	}
	return $R;
}