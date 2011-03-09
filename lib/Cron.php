<?php
/**
 * Cron.php - 7 mars 2011
 * 
 * Initialise une requête pour accéder au cron.
 * Simule le chargement depuis une page distante vers bootstrap.php
 * 
 * PHP Version 5
 * 
 * @category  Internal
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

//Émulation du fonctionnement normal
$_SERVER['SERVER_NAME'] = 'edevoir.com';
$_SERVER['REQUEST_URI'] = '--';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_GET = array(
	'module' => 'debug',
	'controller' => 'index',
	'view' => 'realcron',
	'data' => array()
);

//Chargement du cœur de métier
define('PATH', str_replace('/lib', '', substr(__FILE__, 0, strrpos(__FILE__, '/'))));
include PATH . '/lib/core/constants.php';
include PATH . '/lib/core/functions.php';
include PATH . '/lib/core/OO2FS.php';
include PATH . '/lib/core/Sql.php';

//Connexion à SQL
Sql::connect();

//Envoi de l'évènement
Event::dispatch(Event::CRON, array('Membre' => Membre::getBanque()));
