<?php
/**
 * indexController.php - 8 mar. 2011
 * 
 * Fonctions spéciales ne rentrant dans aucun module.
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

/**
 * Contrôleur d'index du module special.
 * 
 * @category Controller
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 *
 */
class Special_IndexController extends AbstractController
{	
	/**
	 * Page de retour après une transaction Paypal.
	 * 
	 */
	public function from_paypalAction()
	{
		$_POST = unserialize('a:38:{s:8:"mc_gross";s:5:"20.00";s:22:"protection_eligibility";s:10:"Ineligible";s:14:"address_status";s:11:"unconfirmed";s:8:"payer_id";s:13:"UTAJ35PGEV7R8";s:3:"tax";s:4:"0.00";s:14:"address_street";s:33:"Av. de la Pelouse, 87648672 Mayet";s:12:"payment_date";s:25:"08:43:52 Mar 08, 2011 PST";s:14:"payment_status";s:9:"Completed";s:7:"charset";s:12:"windows-1252";s:11:"address_zip";s:5:"75002";s:10:"first_name";s:4:"Test";s:20:"address_country_code";s:2:"FR";s:12:"address_name";s:9:"Test User";s:14:"notify_version";s:3:"3.0";s:6:"custom";s:15:"eleve@neamar.fr";s:12:"payer_status";s:8:"verified";s:8:"business";s:33:"vendeu_1299600229_biz@edevoir.com";s:15:"address_country";s:6:"France";s:12:"address_city";s:5:"Paris";s:8:"quantity";s:1:"1";s:11:"verify_sign";s:56:"AFcWxV21C7fd0v3bYYYRCpSSRl31AAsB0qbNT9L0V7zr.lODS33vcg.f";s:11:"payer_email";s:33:"client_1299600199_per@edevoir.com";s:6:"txn_id";s:17:"0NE50138B13798640";s:12:"payment_type";s:7:"instant";s:9:"last_name";s:4:"User";s:13:"address_state";s:6:"Alsace";s:14:"receiver_email";s:33:"vendeu_1299600229_biz@edevoir.com";s:11:"receiver_id";s:13:"8D9RBG92RFEFS";s:8:"txn_type";s:10:"web_accept";s:9:"item_name";s:15:"Achat de points";s:11:"mc_currency";s:3:"EUR";s:11:"item_number";s:0:"";s:17:"residence_country";s:2:"FR";s:8:"test_ipn";s:1:"1";s:15:"handling_amount";s:4:"0.00";s:19:"transaction_subject";s:15:"eleve@neamar.fr";s:13:"payment_gross";s:0:"";s:8:"shipping";s:4:"0.00";}');
		// lire le formulaire provenant du système PayPal et ajouter 'cmd'
		$req = 'cmd=_notify-validate';

		foreach($_POST as $key => $value)
		{
			$value = urlencode(stripslashes($value));
			$req .= "&$key=$value";
		}
		// renvoyer au système PayPal pour validation
		$header = '';
		$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
		$fp = fsockopen(PAYPAL_URL, 80, $errno, $errstr, 30);
		
		if(!$fp)
		{
			External::report('Erreur HTTP sur une transaction paypal');
			go404('Impossible d\'établir une connexion HTTP');
		}
		else
		{
			fputs($fp, $header . $req);
			while(!feof($fp))
			{
				$res = fgets($fp, 1024);
				if(strcmp($res, 'VERIFIED') == 0)
				{
					$Membre = Membre::load('-1 OR Mail="' . Sql::escape($_POST['custom']) . '"', false);
						
											
					//Transaction valide... mais est-elle correcte ?
					
					if($_POST['payment_status'] == 'Completed'
					&& $_POST['receiver_email'] == PAYPAL_ACCOUNT
					&& $_POST['mc_currency'] == 'EUR'
					&& is_numeric($_POST['mc_gross'])
					&& !is_null($Membre))
					{
						$Points = floor($_POST['mc_gross'] * EQUIVALENCE_POINT);
						
						//Tout semble en ordre, procéder au paiement
						Sql::start();
						
						//Donner l'argent au membre
						$Membre->credit((int) $Points, 'Ajout via paypal.');
						
						//Et logger l'entrée
						$ToInsert = array(
							'Membre' => $Membre->ID,
							'Montant' => $Points,
							'_Date' => 'NOW()',
							'Hash' => $_POST['txn_id'],
							'Data' => serialize($_POST)
						);
						if(Sql::insert('Entrees', $ToInsert))
						{
							Sql::commit();
						}
						else
						{
							Sql::rollback();
							throw new Exception("Transaction paypal abandonnée ; une répétition ?");
						}
					}
					else
					{
						throw new Exception("Transaction paypal valide mais incorrecte");
					}
				}
				else if(strcmp($res, "INVALID") == 0)
				{
					// Transaction invalide
					throw new Exception("Transaction invalide réalisée via paypal");
				}
			}
			fclose($fp);
		}
		
		exit();
	}
}