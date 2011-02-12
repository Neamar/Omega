<?php
/**
 * indexController.php - 12 févr. 2011
 * 
 * Actions de base pour un administrateur
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
 * ontrôleur d'index du module administrateur.
 *
 * @category Controller
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 */
class Administrateur_IndexController extends IndexAbstractController
{
	/**
	 * Page d'accueil du module.
	 * 
	 */
	public function indexAction()
	{
		$this->View->setTitle(
			'Accueil administrateur',
			'Cette page regroupe les différentes actions qui vous sont disponibles en tant qu\'administrateur.'
		);
	}
	
	/**
	 * Page de connexion.
	 * Les données peuvent avoir été envoyées depuis la page d'accueil ou depuis cette page là.
	 * 
	 */
	public function connexionAction()
	{
		$this->View->setTitle(
			'Connexion administrateur',
			'Cet espace est réservé aux responsables.'
		);
		
		//Si on est connecté au moment d'arriver sur cette page, déconnexion.
		if(isset($_SESSION['Administrateur']))
		{
			unset($_SESSION['Administrateur']);
			$this->View->setMessage('info', "Vous vous êtes déconnecté.");
		}
		
		if(isset($_POST['connexion-administrateur']))
		{
			/**
			 * Le token est calculé de la façon suivante :
			 * 2 * numéro du jour dans le mois concaténé à (jour % 10) * (mois % 10)
			 * Exemple : 12/03.
			 *  1 / Multiplier 12 par deux : 24
			 *  2 / Multiplier 2 (= 12 % 2) par 3 (03 % 3) : 6
			 *  3 / Concaténer : 346
			 * 
			 * @var int
			 */
			$Token = (intval(date('d')) * 2) . ((intval(date('d')) % 10) * (intval(date('m')) % 10));
			
			if(!isset($_POST['email']) || !Validator::mail($_POST['email']))
			{
				$this->View->setMessage('error', "L'adresse email spécifiée est incorrecte.");
			}
			elseif(intval($_POST['token']) != $Token)
			{
				$this->View->setMessage('error', "Token invalide. Avez-vous consulté le tableau de la semaine ?");
			}
			else
			{
				$Administrateur = $this->logMe($_POST['email'], $_POST['password'], 'Administrateur');
				if(is_null($Administrateur))
				{
					//TODO : Bloquer après trois connexions ?
					$this->View->setMessage('error', "Identifiants incorrects.");
				}
				else
				{
					$this->View->setMessage("infos", "Bienvenue sur votre compte, despote aux pleins pouvoirs !");
					$this->redirect('/administrateur/');
				}
			}
		}
	}
	
	/**
	 * Gère l'enregistrement dans la table Correcteur en particulier.
	 * 
	 * @see IndexAbstractController::createAccountSpecial()
	 * 
	 * @param array $Datas les données envoyées
	 * 
	 * @return bool true sur un succès.
	 */
	protected function createAccountSpecial(array $Datas)
	{
		Sql::rollback();
		throw new Exception('Fuck.');
	}
}