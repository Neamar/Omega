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
 * @link      http://edevoir.com
 */

date_default_timezone_set("Europe/Paris");

//____________________________________________________________________________
//DONNÉES TECHNIQUES
//____________________________________________________________________________

/**
 * Chemin vers les librairies
 * @var string
 */
define('LIB_PATH', PATH . '/lib');

/**
 * Chemin vers les fichiers de l'application
 * @var string
 */
define('APPLICATION_PATH', PATH . '/application');

/**
 * Chemin vers les données
 * @var string
 */
define('DATA_PATH', PATH . '/data');

/**
 * L'adresse sur laquelle le site est hébergé. Utile pour les redirections ou les liens qu'on doit placer en absolu.
 * @var string
 */
define('URL', 'http://' . $_SERVER['SERVER_NAME']);

/**
 * Les différents hôtes disponibles
 */
define('LOCAL', 'http://omega.localhost');
define('TEST', 'http://test.edevoir.com');
define('PROD', 'http://edevoir.com');

/**
 * Le sel utilisé sur le site pour tous les hashages
 * @var string
 */
define('SALT', 'Omega_cy4:D#4|sa|P)\|BUjdxS~');

/**
 * Code d'erreur
 * @var int
 */
define('FAIL', -1);

/**
 * Clé publique pour l'utilisation de recaptcha
 * @var string
 */
define('CAPTCHA_PUBLIC', '6LdI48ESAAAAAPyHPYB19hXs7mWxFUtnW4uTEHBp');

/**
 * Clé privée pour l'utilisation de recaptcha
 * @var string
 */
define('CAPTCHA_PRIVATE', '6LdI48ESAAAAAPr31W2_tpx7J3ZTCm5rVwL-pRU3');

/**
 * Clé utilisée pour la compilation LaTeX
 * 
 * @var string
 */
define('CLSI_TOKEN', 'e3075c67779a0796f550e988e400469b');

//____________________________________________________________________________
//DONNÉES DITES MÉTIER
//____________________________________________________________________________
/**
 * Nombre de points correspondant à 1€
 * @var int
 */
define('EQUIVALENCE_POINT', 25);

/**
 * Le prix maximal payable sur un exercice.
 * 
 * @var int
 */
define('MAX_SOMME', 20000);

/**
 * La taille d'un hash "court".
 * 
 * @var int
 */
define('HASH_LENGTH', 6);

/**
 * Pourcentage de multiplication de la somme correcteur (dont TVA)
 * @var float
 */
define('MARGE', 1.25);

/**
 * Borne supérieure maximale (en pourcentage) pour un remboursement
 * 
 * @var int
 */
define('POURCENTAGE_MAX_REMBOURSEMENT', 200);

/**
 * Pourcentage de remboursement automatique en cas de retard
 * 
 * @var int
 */
define('POURCENTAGE_RETARD', 200);

/**
 * Valeur max (euros) remboursée en plus de la somme initiale (retard ou remboursement)
 * 
 * @var int
 */
define('MAX_REMBOURSEMENT', 200);

/**
 * Nombre maximal de refus avant d'annuler un exercice
 * 
 * @var int
 */
define('MAX_REFUS', 3);

/**
 * Surcharge ajoutée pour le cumul de la correction d'exercice
 * 
 * @var int
 */
define('POURCENTAGE_SURACTIVITE', 10);

/**
 * Surcharge maximum ajoutée pour le cumul de la correction d'exercice
 * 
 * @var int
 */
define('POURCENTAGE_MAX_SURACTIVITE', 150);

/**
 * Nombre de jours pris en compte pour le calcul du cumul
 * 
 * @var int
 */
define('CALCUL_CUMUL', 7);

/**
 * Délai minimum (h) avant le timeout correcteur
 * 
 * @var int
 */
define('MIN_CORRECTEUR_TIMEOUT', 10);

/**
 * Nombre de jours après l'expiration avant fermeture de la FAQ de l'exercice
 * 
 * @var int
 */
define('DELAI_FAQ', 7);

/**
 * Liste des extensions autorisées à l'upload.
 * 
 * @var string
 */
define('EXTENSIONS', 'gif|jpg|jpeg|png|txt|pdf|rtf|doc|docx|odt');
/**
 *  Nombre de jours après l'expiration avant impossibilité de faire une réclamation remboursée
 * 
 * @var int
 */
define('DELAI_REMBOURSEMENT', 7);

/**
 * Nombre max d'exercices réservés simultanément par un correcteur
 * 
 * @var int
 */
define('MAX_EXERCICE_RESERVES', 5);

/**
 * Nombre max d'exercices créés non acceptés par un élève
 * 
 * @var int
 */
define('MAX_EXERCICE_CREES', 10);

/**
 * Nombre max de fichiers par exercice
 * 
 * @var int
 */
define('MAX_FICHIERS_EXERCICE', 25);

/**
 * Délai minimum (jours) entre deux virements
 * 
 * @var int
 */
define('DELAI_VIREMENT', 7);

/**
 * Identifiant du membre-banque
 *
 * @var int
 */
define('BANQUE_ID', 1);

/**
 * Le nombre d'enregistrements de base contenus sur un tableau AJAX.
 * 
 * @var int
 */
define('AJAX_LIMITE', 10);


/**
 * Compte paypal d'eDevoir
 * En test : 'vendeu_1299600229_biz@edevoir.com'
 * En test (acheteur) : client_1299600199_per@edevoir.com, mdp : test_facile
 * En production : 'contact@edevoir.com'
 * 
 * @var string
 */
define('PAYPAL_ACCOUNT', 'contact@edevoir.com');

/**
 * URL de paypal
 * En test : 'www.sandbox.paypal.com'
 * En production : 'www.paypal.com'
 * @var string
 */
define('PAYPAL_URL', 'www.paypal.com');

//____________________________________________________________________________
// CAS PARTICULIERS
//____________________________________________________________________________
if(URL == LOCAL)
{
	/**
	 * Indique de ne pas véritablement envoyer les mails, mais de les enregistrer dans un fichier pour consultation ultérieure
	 * @see External::mail
	 * 
	 * @var bool
	 */
	define('FAKE_MAIL', true);
}

if(URL == PROD)
{
	/**
	 * Indique que la connexion est interdite sur le site jusqu'au timestamp indiqué.
	 * @see IndexAbstractController::logMe()
	 * @var int
	 */
	define('NO_CONNECT', 1300273200);
}

if(URL == TEST)
{
	define('SQL_DB', 'dev-work');
}
else
{
	define('SQL_DB', 'work');
}