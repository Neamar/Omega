<?php
/**
 * constants.php - 26 oct. 2010
 *
 * Charger toutes les constantes nécessaires
 * La constante PATH doit-être définie depuis le bootstrap.
 *
 * PHP Version 5
 *
 * @category  Controller
 * @package   Root
 * @author    Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @copyright 2010 Matthieu Bacconnier
 * @license   Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link      http://devoirminute.com
 */

//Chemin vers les librairies
define('LIB_PATH', PATH . '/lib');
define('APPLICATION_PATH', PATH . '/application');

//Chemin vers les données
define('DATA_PATH', '');

//L'adresse sur laquelle le site est hébergé. Utile pour les redirections ou les liens qu'on doit placer en absolu.
define('URL', 'http://localhost/Omega');

//Le sel utilisé sur le site pour tous les hashages
define('SALT', 'Omega_cy4:D#4|sa|P)\|BUjdxS~');

date_default_timezone_set("Europe/Paris");