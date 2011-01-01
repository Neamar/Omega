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
 * @param string $URL l'url à intégrer
 * @param View $ViewObject la vue contenant les données
 * 
 * @return string le contenu de la vue
 */
function ViewHelper_View_render(View $ViewObject)
{
	ob_start();
	
	$ViewObject->renderContent();
	
	return ob_get_clean();
}
 