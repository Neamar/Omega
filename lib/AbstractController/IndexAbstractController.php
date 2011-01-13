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
 * @link      http://edevoir.com
 */

/**
 * Couche d'abstraction pour l'index
 * 
 * @category Controller
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 *
 */
abstract class IndexAbstractController extends AbstractController
{
	/**
	 * Crée un compte de base.
	 * Appelle la fonction create_account_special() qui gère les opérations spécifiques (tables héritées)
	 * @see IndexAbstractController::createAccountSpecial
	 * 
	 * @param array $Data les données envoyées
	 * @param string $Type (ELEVE|CORRECTEUR) le type de la Table Membre.
	 * 
	 * @return int l'identifiant nouvellement inséré, ou FAIL.
	 */
	protected function createAccount(array $Datas, $Type)
	{
		if(!$this->validateMail($Datas['email']))
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
				'Mail' => $Datas['email'],
				'Pass' => sha1(SALT . $Datas['password']),
				'_Creation' => 'NOW()',
				'_Connexion' => 'NOW()',
				'Type' => $Type,
			);
			if(!Sql::insert('Membres', $ToInsert))
			{
				Sql::rollback();
				$this->View->setMessage("error", "Impossible de vous enregistrer. L'adresse email est peut-être déjà réservée ?");
			}
			else
			{
				$ID = SQL::lastId();

				
				if(!$this->createAccountSpecial($Datas))
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
	protected abstract function createAccountSpecial(array $Datas);
	
	/**
	 * Modifie un compte de base.
	 * Appelle la fonction editAccountSpecial() qui gère les opérations spécifiques selon l'héritage (numéro de téléphone, classe...)
	 * Utilisé par options_CompteAction().
	 * 
	 * @param array $Data les données envoyées
	 * @param Membre $Base le membre actuel (pour ne pas mettre à jour ce qui ne change pas)
	 * 
	 * @return array le tableau des modifications à effecter, ou FAIL (avec un message dans ce cas)
	 */
	protected function editAccount(array $Datas, Membre $Base)
	{
		if(!$this->validateMail($Datas['email']))
		{
			$this->View->setMessage("error", "L'adresse email spécifiée est incorrecte.");
		}
		else if(External::isTrash($Datas['email']))
		{
			$this->View->setMessage("error", "Désolé, nous n'acceptons pas les adresses jetables.");
		}
		else if(!empty($Datas['password_confirm']) && $Datas['password'] != $Datas['password_confirm'])
		{
			$this->View->setMessage("error", "Les deux mots de passe ne concordent pas.");
		}
		else
		{
			$ToUpdate = array();

			if($Datas['email'] != $Base->Mail)
			{
				$ToUpdate['Mail'] = $Datas['email'];
			}
			
			if(!empty($Datas['password_confirm']))
			{
				$ToUpdate['Pass'] = sha1(SALT . $Datas['password']);
			}
			
			return $ToUpdate;
		}
		
		return FAIL;
	}
	
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
		$ID = '(SELECT ID FROM Membres WHERE Mail="' . SQL::escape($Mail) . '" AND Pass="' . sha1(SALT . $Pass) . '" AND Statut !="DESINSCRIT" AND Type="' . Sql::escape($Type) . '")';
		
		$Membre = $Type::load($ID, false); // Récupérer sans filtrer.
	
		$_SESSION[$Type] = $Membre;
		if(!is_null($Membre))
		{
			$Membre->setAndSave(array('Connexion'=>SQL::getDate()));
			$_SESSION['Membre']['Mail'] = $Membre->Mail;
		}
		
		return $Membre;
	}
	
	/**
	 * Valide une adresse mail.
	 * 
	 * @param string $Mail
	 * 
	 * @return bool
	 */
	protected function validateMail($Mail)
	{
		return filter_var($Mail, FILTER_VALIDATE_EMAIL);
	}
}