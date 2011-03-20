<?php
/**
 * indexController.php - 26 oct. 2010
 * 
 * Actions de base pour un élève.
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
 * Contrôleur d'index du module élève.
 * 
 * @category Controller
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 *
 */
class Eleve_IndexController extends IndexAbstractController
{
	/**
	 * Page d'accueil du module ; connecter le membre si nécessaire, puis afficher les infos du compte et les liens utiles. 
	 * 
	 */
	public function indexAction()
	{
		$this->View->setTitle(
			'Accueil élève',
			'Cette page offre un récapitulatif de votre compte : vos derniers exercices, votre solde... ainsi, bien sûr, que la possibilité de créer un nouvel exercice.</p>'
		);
		
		$this->View->Exo = $this->concat('/eleve/exercice/');
		$this->View->Exo->NoCreate = true; //Ne pas afficher le lien de création que l'on va déjà mettre nous-mêmes.
		$this->View->Points = $this->concat('/eleve/points/');

	}
	
	/**
	 * Page de connexion.
	 * Les données peuvent avoir été envoyées depuis la page d'accueil ou depuis cette page là.
	 * 
	 */
	public function connexionAction()
	{
		$this->View->setTitle(
			'Connexion élève',
			'Connectez-vous pour accéder au site.'
		);
		$this->View->setSeelink('/eleve/inscription', 'Pas encore inscrit ?');
		
		//Si on est connecté au moment d'arriver sur cette page, déconnexion.
		if(isset($_SESSION['Eleve']))
		{
			unset($_SESSION['Eleve']);
			$this->View->setMessage('info', "Vous vous êtes déconnecté.", 'eleve/deconnexion');
		}
		
		if(isset($_POST['connexion-eleve']))
		{
			if(!isset($_POST['email']) || !Validator::mail($_POST['email']))
			{
				$this->View->setMessage('error', "L'adresse email spécifiée est incorrecte.");
			}
			else
			{
				$Eleve = $this->logMe($_POST['email'], $_POST['password'], 'Eleve');
				if(!is_null($Eleve))
				{
					if($Eleve->Statut == 'EN_ATTENTE')
					{
						$this->View->setMessage('error', "Votre compte est actuellement en attente de validation. Consultez votre boite mail pour plus de détails.", 'eleve/probleme_connexion');
						unset($_SESSION['Eleve']); 
					}
					else
					{
						$this->View->setMessage('info', "Bienvenue sur votre compte ! Solde : " . $Eleve->getPoints() . ' points.');
						
						//Rediriger vers la page d'accueil du module, ou vers la page demandée avant la connexion si ce n'est pas une page Ajax.
						if(isset($_SESSION['EleveComingFrom']) && strpos($_SESSION['EleveComingFrom'], '_') === false)
						{
							$URL = $_SESSION['EleveComingFrom'];
							unset($_SESSION['EleveComingFrom']);
						}
						else
						{
							$URL = '/eleve/';	
						}

						$this->redirect($URL);
					}
				}
				else
				{
					//TODO : Bloquer après trois connexions ?
					$this->View->setMessage('error', "Identifiants incorrects.", 'eleve/probleme_connexion');
				}
			}
		}
	}
	
	/**
	 * Page d'inscription.
	 * 
	 */
	public function inscriptionAction()
	{
		//On ne s'inscrit pas si on est déjà connecté.
		if(isset($_SESSION['Eleve']))
		{
			$this->redirect('/eleve/');
		}
		
		$this->View->setTitle(
			'Inscription élève',
			"L'inscription à <strong class=\"edevoir\"><span>e</span>Devoir</strong> est simple et rapide. Nous ne demandons qu'un minimum d'informations pour vous permettre de profiter rapidement des services offerts par le site."
		);
		$this->View->setSeelink('/eleve/connexion', 'Déjà membre ?');
		
		//Charger la liste des classes pour le combobox :
		$this->View->Classes = SQL::queryAssoc('SELECT Classe, DetailsClasse FROM Classes ORDER BY Classe DESC', 'Classe', 'DetailsClasse');
		
		
		//Le membre vient de s'inscrire mais revient sur cette page.
		if(isset($_SESSION['Eleve_JusteInscrit']) && !$this->View->issetMeta('message'))
		{
			$this->View->setMessage('warning', "Vous êtes déjà inscrit ! Veuillez cliquer sur le lien d'enregistrement qui vous a été envoyé par mail  à " . $_SESSION['Eleve_JusteInscrit'] . " pour terminer votre inscription.", 'eleve/validation');
		}
		
		if(isset($_POST['inscription-eleve']))
		{
			if(!isset($this->View->Classes[$_POST['classe']]))
			{
				$this->View->setMessage('error', "Sélectionnez une classe dans la liste déroulante.");
			}
			elseif(!Validator::captcha())
			{
				$this->View->setMessage('error', "Le captcha rentré est incorrect. Merci de réessayer.");
			}
			else
			{
				$ID = $this->createAccount($_POST, 'ELEVE');
				if($ID != FAIL)
				{
					//Enregistrer le nouveau membre et le rediriger vers la page de connexion
					Event::dispatch(
						Event::ELEVE_INSCRIPTION, 
						array(
							'mail' => $_POST['email'],
							'lien' => sha1(SALT . $ID . $_POST['email']) . '/mail/' . urlencode($_POST['email']),
						)
					);
					
					$_SESSION['Eleve_JusteInscrit'] = $_POST['email'];
					$this->View->setMessage('info', "Vous êtes maintenant membre ! Veuillez cliquer sur le lien d'enregistrement qui vous a été envoyé par mail pour terminer votre inscription.");
					$this->redirect('/eleve/connexion');
				}
			}
		}
	}
	
	/**
	 * Page de validation du compte.
	 * Si le compte est en attente, vérifie que l'identifiant fourni est correct puis marque le compte comme accessible.
	 * Redirige vers /eleve/connexion
	 * 
	 */
	public function validationActionWd()
	{
		if(!isset($this->Data['data'], $this->Data['mail']))
		{
			$this->View->setMessage('info', 'Appel incorrect');
			$this->redirect('/eleve/connexion');
		}
		
		$Eleve = Eleve::load('(SELECT ID FROM Membres WHERE Mail = "' . SQL::escape($this->Data['mail']) . '" AND Type="ELEVE")', false);
		
		if(!is_null($Eleve) && sha1(SALT . $Eleve->ID . $this->Data['mail']) == $this->Data['data'])
		{
			if($Eleve->Statut == 'EN_ATTENTE')
			{
				$Eleve->setAndSave(array('Statut' => 'OK'));
			}
			
			$this->View->setMessage('info', "Votre compte est validé ! Vous pouvez maintenant vous connecter.");
			$this->redirect("/eleve/connexion");
		}
		else
		{
			$this->View->setMessage('error', 'Lien de validation invalide.');
			$this->redirect("/eleve/inscription");
		}
	}
	
	/**
	 * Gère l'enregistrement dans la table Eleves en particulier.
	 * 
	 * @see IndexAbstractController::createAccountSpecial()
	 * 
	 * @param array $Datas les données envoyées
	 * 
	 * @return bool true sur un succès.
	 */
	protected function createAccountSpecial(array $Datas)
	{
		$ToInsert = array(
			'ID' => Sql::lastId(),
			'Classe' => intval($Datas['classe']),
			'Section' => $Datas['section']
		);
		
		if(isset($Datas['parrain']))
		{
			$IDParrain = Sql::singleColumn('SELECT ID FROM Membres WHERE Mail="' . Sql::escape($Datas['parrain']) . '"', 'ID');
			if(!is_null($IDParrain))
			{
				$ToInsert['Parrain'] = $IDParrain;
			}
		}
		
		return Sql::insert('Eleves', $ToInsert);
	}
}