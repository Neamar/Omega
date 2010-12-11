<?php
/**
 * indexAbstractController.php - 26 oct. 2010
 * 
 * Actions de base communes aux contrôleurs d'index : connexion, identification...
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

/**
 * Couche d'abstraction pour l'index
 * 
 * @category Controller
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://devoirminute.com
 *
 */
abstract class IndexAbstractController extends AbstractController
{
	/**
	 * Crée un compte de base.
	 * Appelle la fonction create_account_special() qui gère les opérations spécifiques (tables héritées)
	 * 
	 * @param array $Data les données envoyées
	 * 
	 */
	protected function create_account(array $Datas)
	{
		if(!filter_var($Datas['email'], FILTER_VALIDATE_EMAIL))
		{
			$this->View->setMessage("error", "L'adresse email spécifiée est incorrecte.");
		}
		else if(External::isTrash($Datas['email']))
		{
			$this->View->setMessage("error", "Désolé, nous n'acceptons pas les adresses jetables.");
		}
		else if(empty($Datas['password']))
		{
			$this->View->setMessage("error", "Aucun mot de passe spécifié !");
		}
		else if($Datas['password'] != $Datas['password_confirm'])
		{
			$this->View->setMessage("error", "Les deux mots de passe ne concordent pas.");
		}
		else if(!isset($Datas['cgu']) || $Datas['cgu'] != 'on')
		{
			$this->View->setMessage("error", "Vous n'avez pas validé les conditions générales d'utilisation");
		}
		else
		{
			//Commencer une transaction pour garantir l'intégrité :
			SQL::start();
			$ToInsert = array(
				'Mail'=> $Datas['email'],
				'Pass'=>sha1(SALT . $Datas['password']),
				'_Creation'=>'NOW()',
				'_Connexion'=>'NOW()',
			);
			if(!Sql::insert('Membres', $ToInsert))
			{
				Sql::rollback();
				$this->View->setMessage("error", "Impossible de vous enregistrer. L'adresse email est peut-être déjà réservée ?");
			}
			else
			{
				$ID = SQL::lastId();
				if(!$this->create_account_special($Datas))
				{
					Sql::rollback();
					$this->View->setMessage("error", "Impossible de vous enregistrer. Veuillez réessayer plus tard.");
				}
				else 
				{
					Sql::commit();
					$this->View->setMessage("info", "Vous êtes enregistré !");
					return $ID ;
				}
			}
		}
		
		return FAIL;
	}
	
	/**
	 * Gère la partie spécifique de l'inscription.
	 * 
	 * @param array $Datas
	 * 
	 * @return bool true en succès, false en échec.
	 */
	protected abstract function create_account_special(array $Datas);
	
	/**
	 * Connecte la personne en tant que $Type (Eleve, Correcteur) si ses identifiants sont corrects.
	 * Renvoie null si les identifiants sont incorrects ou si le membre est désinscrit.
	 * 
	 * @param string $Mail le login à tester
	 * @param string $Pass le mdp à tester
	 * @param string $Type le type d'objet à renvoyer
	 * 
	 * @return Membre l'objet membre ou null.
	 */
	protected function logMe($Mail, $Pass, $Type)
	{
		$ID = '(SELECT ID FROM Membres WHERE Mail="' . SQL::escape($Mail) . '" AND Pass="' . sha1(SALT . $Pass) . '" AND Statut !="DESINSCRIT")';
		
		$Membre = $Type::load($ID, false); // Récupérer sans filtrer.
	
		$_SESSION[$Type] = $Membre;
		if(!is_null($Membre))
		{
			$Membre->setAndSave(array('Connexion'=>SQL::getDate()));
			$_SESSION['Membre']['Mail'] = $Membre->Mail;
		}
		
		return $Membre;
	}
}