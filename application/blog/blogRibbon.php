<?php
/**
 * blogRibbon.php - 5 mar. 2011
 * 
 * Le ruban s'affichant en haut de toutes les pages blog.
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
$Retour['center'] = 'Blog <span class="edevoir"><span>e</span>Devoir</span>';

$Retour['left'] = '<a href="/blog/">Derniers articles</a>';
$Retour['right'] = '';
$Retour['links'] = array(
	'/' => 'Accueil eDevoir',
);

return $Retour;