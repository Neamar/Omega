<?php
/**
 * documentationRibbon.php - 2 jan. 2011
 * 
 * Le ruban s'affichant en haut de toutes les pages documentations.
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

$Retour = array(
	'left' => '',
	'center' => 'Documentation',
	'right' => '',
	'links' => array(
		'/documentation/' => 'Accueil',
		'/documentation/eleve/' => 'Documentation élève',
		'/documentation/correcteur/' => 'Documentation correcteur'
	)
);


return $Retour;