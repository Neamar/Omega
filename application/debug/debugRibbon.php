<?php
/**
 * debugRibbon.php - 20 déc. 2010
 * 
 * Ruban du module débuggage.
 * 
 * PHP Version 5
 * 
 * @category  Default
 * @package   Root
 * @author    Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @copyright 2010 Matthieu Bacconnier
 * @license   Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link      http://edevoir.com
 */
 
return array(
	'left' => 'ADMIN',
	'center' => 'Espace Debug',
	'right' => '<a href="/administrateur/">Accès à l\'administration</a>',
	'links' => array(
		'/' => 'Accueil',
		'/debug/clean' => 'Nettoyage',
		'/debug/alldoc' => 'Toute la documentation'
	)
);