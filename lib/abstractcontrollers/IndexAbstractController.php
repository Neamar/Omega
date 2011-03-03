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
	 * Désinscrit le membre.
	 */
	public function desinscriptionAction()
	{
		$this->View->setTitle(
			'Désinscription eDevoir',
			'Cette page vous permet de vous désinscrire définitivement.'
		);
		
		/**
		 * Le membre qui demande la désinscription.
		 * @var Membre
		 */
		$Membre = $this->getMembre();
		$this->View->Membre = $Membre;
		
		if(isset($_POST['desinscription-membre']))
		{
			//Au revoir :(
			Sql::update(
				'Membres',
				$Membre->getFilteredId(),
				array('Statut' =>  'DESINSCRIT'),
				'AND Pass="' . Util::hashPass($_POST['password']) . '"
				 AND Type = "' . strtoupper($this->getModule()) . '"'
			);
			
			if(Sql::affectedRows() < 1)
			{
				$this->View->setMessage('error', "Mot de passe invalide.");
			}
			else
			{
				//Envoyer un mail de confirmation de désinscription (et le faire avant d'unset la $_SESSION ^^)
				External::templateMailFast($this->getMembre(), '/membre/compte/desinscription');
				
				unset($_SESSION[$this->getMembreIndex()]);
				
				//Récupérer sur la banque les points du compte
				if($Membre->getPoints() > 0)
				{
					Sql::start();
					$Points = $Membre->getPoints();
					$Membre->debit($Points, 'Virement de désinscription');
					Membre::getBanque()->credit($Points, 'Réception du virement de désinscription');
					Sql::commit();
				}
				
				//Puis rediriger vers la page de connexion
				$this->View->setMessage(
					'ok',
					"Vous avez été désinscrit. Que les vents vous soient favorables !<br />
					Vous avez été déconnecté."
				);
				$this->redirect('/' . $this->getModule() . '/connexion');
			}
		}
		
		//Utiliser une vue générique.
		$this->deflectView(OO2FS::genericViewPath('membre/desinscription'));
	}
	
	/**
	 * Page pour récupérer un mot de passe perdu
	 */
	public function recuperationAction()
	{
		$this->View->setTitle(
			'Récupération mot de passe',
			'Cette page vous permet de récupérer un mot de passe perdu.'
		);
		$this->View->setSeelink('/' . $this->getModule() . '/connexion', 'Connexion');
		
		//Récupérer un mot de passe alors qu'on est connecté ?
		//WTF ?
		if(isset($_SESSION[$this->getMembreIndex()]))
		{
			$this->redirect('/' . $this->getModule());
		}
		
		if(isset($_POST['recuperation-membre']))
		{
			if(!Validator::mail($_POST['email']))
			{
				$this->View->setMessage('error', "Entrez une adresse mail correcte !");
			}
			else
			{
				//Générer un nouveau mot de passe :
				$length = 8;
				$chars = '_ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
				$chars_length = (strlen($chars) - 1);
		
				// Start our string
				$string = $chars{rand(0, $chars_length)};
		
				// Generate random string
				for ($i = 1; $i < $length; $i = strlen($string))
				{
					// Grab a random character from our list
					$r = $chars{rand(0, $chars_length)};
		
					// Make sure the same two characters don't appear next to each other
					if ($r != $string{$i - 1})
					{
						$string .=  $r;
					}
				}
				
				$Password = $string;
				
				Sql::update(
					'Membres',
					'-1',
					array(
						'Pass' => Util::hashPass($Password)
					),
					'OR (Mail = "' . Sql::escape($_POST['email']) . '" AND Type = "' . strtoupper($this->getModule()) . '")'
				);
				
				if(Sql::affectedRows() < 1)
				{
					$this->View->setMessage('error', "Cette adresse n'existe pas.");
				}
				else
				{
					$Datas = array(
						'mail' => $_POST['email'],
						'password' => $Password,
						'ip' => $_SERVER['REMOTE_ADDR']
					);
					
					External::templateMail($_POST['email'], '/membre/compte/recuperation_mdp', $Datas);
					
					$this->View->setMessage('ok', "Un mail a bien été envoyé avec vos nouveaux identifiants.");
					$this->redirect('/' . $this->getModule() . '/connexion');
				}
			}
		}
		
		//Utiliser une vue générique.
		$this->deflectView(LIB_PATH . '/views/membre/recuperation.phtml');
	}
	
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
		if(!Validator::mail($Datas['email']))
		{
			$this->View->setMessage('error', "L'adresse email spécifiée est incorrecte.");
		}
		else if(External::isTrash($Datas['email']))
		{
			$this->View->setMessage('error', "Désolé, nous n'acceptons pas les adresses jetables.");
		}
		else if(empty($Datas['password']))
		{
			$this->View->setMessage('error', "Aucun mot de passe spécifié !");
		}
		else if($Datas['password'] != $Datas['password_confirm'])
		{
			$this->View->setMessage('error', "Les deux mots de passe ne concordent pas.");
		}
		else if(!isset($Datas['cgu']) || $Datas['cgu'] != 'on')
		{
			$this->View->setMessage('error', "Vous n'avez pas validé les Conditions Générales d'Utilisation");
		}
		else
		{
			//Commencer une transaction pour garantir l'intégrité :
			SQL::start();
			$ToInsert = array(
				'Mail' => $Datas['email'],
				'Pass' => Util::hashPass($Datas['password']),
				'_Creation' => 'NOW()',
				'_Connexion' => 'NOW()',
				'Type' => $Type,
			);
			if(!Sql::insert('Membres', $ToInsert))
			{
				Sql::rollback();
				$this->View->setMessage('error', "Impossible de vous enregistrer. L'adresse email est peut-être déjà réservée ?");
			}
			else
			{
				$ID = SQL::lastId();

				
				if(!$this->createAccountSpecial($Datas))
				{
					Sql::rollback();
					$this->View->setMessage('error', "Impossible de vous enregistrer. Veuillez réessayer plus tard.");
				}
				else 
				{
					Sql::commit();
					$this->View->setMessage('info', "Vous êtes enregistré !");
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
		//On ne gère pas désinscrit par un message à part pour ne pas révéler si un membre était déjà inscrit.
		$ID = '(
		SELECT ID
		FROM Membres
		WHERE
			Mail="' . SQL::escape($Mail) . '"
			AND Pass="' . Util::hashPass($Pass) . '"
			AND Statut !="DESINSCRIT"
			AND Type="' . Sql::escape($Type) . '"
		)';
		
		//Fichier gardant en mémoire le nombre d'essais de connexion
		$IPTryURL = DATA_PATH . '/ips/try/' . $_SERVER['REMOTE_ADDR'];
		
		$Membre = $Type::load($ID, false); // Récupérer sans filtrer.
	
		$_SESSION[$Type] = $Membre;
		if(!is_null($Membre))
		{
			$Membre->setAndSave(array('Connexion' => SQL::getDate()));
			$_SESSION['Membre']['Mail'] = $Membre->Mail;
		}
		else
		{
			//Gestion du bannissement si trop d'essais
			if(file_exists($IPTryURL))
			{
				$NbTentativesConnexion = unserialize(file_get_contents($IPTryURL));
			}
			
			
			if(!isset($NbTentativesConnexion) || $NbTentativesConnexion['T'] < time())
			{
				$NbTentativesConnexion = array(
					'E' => 0, //Nombre d'essais
					'T' => time() + 3600, //Expiration
				);
			}
			
			$NbTentativesConnexion['E']++;
			
			if($NbTentativesConnexion['E'] >= 5)
			{
				//Bannir et remettre à 0 le compteur
				Util::ban($_SERVER['REMOTE_ADDR'], 3600);
				unlink($IPTryURL);
			}
			else
			{
				file_put_contents($IPTryURL, serialize($NbTentativesConnexion));
			}
		}
		
		return $Membre;
	}
}