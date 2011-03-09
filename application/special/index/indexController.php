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
	
	/**
	 * Callback-URL pour monelib
	 */
	public function from_monelib()
	{
		External::report('Monelib ok');
	}
}