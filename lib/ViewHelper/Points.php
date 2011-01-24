<?php 
/**
 * Points.php - 22 janv. 2011
 *
 * Offrir des primitives de haut niveau pour le contrôleur de points.
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
 * Convertit une valeur en points en euros
 * 
 * @param int $Points les points à transformer
 */
function ViewHelper_Points_euros($Points)
{
	return number_format($Points / EQUIVALENCE_POINT, 2, ',', ' ') . '&nbsp;€';
}