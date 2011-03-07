<?php
/**
 * Cron.php - 7 mars 2011
 * 
 * Initialise une requête pour accéder au cron.
 * Simule le chargement depuis une page distante vers bootstrap.php
 * 
 * PHP Version 5
 * 
 * @category  Default
 * @package   Root
 * @author    Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @copyright 2010 Matthieu Bacconnier
 * @license   Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link      http://edevoir.com
 * @param string
 * @param string
 */

if(!defined('STDIN'))
{
	exit('CLI uniquement');
}

define('PATH', substr($File, 0, strrpos($File, '/')));
include PATH . '/lib/core/constants.php';
include PATH . '/lib/core/functions.php';
include PATH . '/lib/core/OO2FS.php';
include PATH . '/lib/core/Sql.php';

Event::dispatch(Event::CRON, array('Membre' => Membre::getBanque()));