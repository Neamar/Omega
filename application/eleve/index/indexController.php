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
		
		//Si on est connecté au moment d'arriver sur cette page, déconnexion.
		if(isset($_SESSION['Eleve']))
		{
			unset($_SESSION['Eleve']);
			$this->View->setMessage("info", "Vous vous êtes déconnecté.", 'eleve/deconnexion');
		}
		
		if(isset($_POST['connexion-eleve']))
		{
			if(!isset($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
			{
				$this->View->setMessage("error", "L'adresse email spécifiée est incorrecte.");
			}
			else
			{
				$Eleve = $this->logMe($_POST['email'], $_POST['password'], 'Eleve');
				if(!is_null($Eleve))
				{
					if($Eleve->Statut == 'EN_ATTENTE')
					{
						$this->View->setMessage("error", "Votre compte est actuellement en attente de validation. Consultez votre boite mail pour plus de détails.", 'eleve/probleme_connexion');
						unset($_SESSION['Eleve']); 
					}
					else
					{
						$this->View->setMessage("infos", "Bienvenue sur votre compte ! Solde : " . $Eleve->getPoints());
						$this->redirect('/eleve/');
					}
				}
				else
				{
					//TODO : Bloquer après trois connexions ?
					$this->View->setMessage("error", "Identifiants incorrects.", 'eleve/probleme_connexion');
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
		$this->View->setTitle(
			'Inscription élève',
			"L'inscription à <strong class=\"edevoir\"><span>e</span>Devoir</strong> est simple et rapide. Nous ne demandons qu'un minimum d'informations pour vous permettre de profiter rapidement des services offerts par le site."
		);
		
		//Charger la liste des classes pour le combobox :
		$this->View->Classes = SQL::queryAssoc('SELECT Classe, DetailsClasse FROM Classes ORDER BY Classe DESC', 'Classe', 'DetailsClasse');
		
		
		//Le membre vient de s'inscrire mais revient sur cette page.
		if(isset($_SESSION['Eleve_JusteInscrit']) && !$this->View->issetMeta('message'))
		{
			$this->View->setMessage("info", "Vous êtes déjà inscrit ! Veuillez cliquer sur le lien d'enregistrement qui vous a été envoyé par mail  à" . $_SESSION['Eleve_JusteInscrit'] . "pour terminer votre inscription.", 'eleve/validation');
		}
		
		if(isset($_POST['inscription-eleve']))
		{
			if(!isset($this->View->Classes[$_POST['classe']]))
			{
				$this->View->setMessage("error", "Sélectionnez une classe dans la liste déroulante.");
			}
			else
			{
				$ID = $this->createAccount($_POST, 'ELEVE');
				if($ID != FAIL)
				{
					//Enregistrer le nouveau membre et le rediriger vers la page de connexion
					$Datas = array(
						'mail'=>$_POST['email'],
						'lien'=>sha1(SALT . $ID . $_POST['email']) . '/mail/' . $_POST['email'],
					);
					External::templateMail($_POST['email'], '/eleve/validation', $Datas);
					$_SESSION['Eleve_JusteInscrit'] = $_POST['email'];
					$this->View->setMessage("info", "Vous êtes maintenant membre ! Veuillez cliquer sur le lien d'enregistrement qui vous a été envoyé par mail pour terminer votre inscription.");
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
		if(!isset($this->Data['data'],$this->Data['mail']))
		{
			exit();
		}
		

		$Eleve = Eleve::load('(SELECT ID FROM Membres WHERE Mail = "' . SQL::escape($this->Data['mail']) . '" AND Type="ELEVE")', false);
		
		if(!is_null($Eleve) && sha1(SALT . $Eleve->ID . $this->Data['mail']) == $this->Data['data'])
		{
			if($Eleve->Statut == 'EN_ATTENTE')
			{
				$Eleve->setAndSave(array('Statut'=>'OK'));
			}
			
			$this->View->setMessage("info", "Votre compte est validé ! Vous pouvez maintenant vous connecter");
			$this->redirect("/eleve/connexion");
		}
		else
		{
			$this->View->setMessage("error", "Lien de validation invalide.");
			$this->redirect("/eleve/inscription");
		}
	}
	
	/**
	 * Page d'options globales.
	 */
	public function optionsAction()
	{
		$this->View->setTitle(
			'Options élève',
			'Modifiez ici vos informations de compte.'
		);
		$this->View->Compte = $this->concat('/eleve/options_compte');
	}
	
	public function options_CompteAction()
	{
		$this->View->setTitle(
			'Modifications du compte',
			'Cette page vous permet de modifier les informations de votre compte.'
		);

		//Charger la liste des classes pour le combobox :
		$this->View->Classes = SQL::queryAssoc('SELECT Classe, DetailsClasse FROM Classes ORDER BY Classe DESC', 'Classe', 'DetailsClasse');
		
		
		if(isset($_POST['edition-compte']))
		{
			$ToUpdate = $this->editAccount($_POST, $_SESSION['Eleve']);
			if($ToUpdate == FAIL)
			{
				//La mise à jour ne doit pas être effectuée.
				//Le message a été défini par editAccount.
			}
			elseif(!isset($this->View->Classes[$_POST['classe']]))
			{
				$this->View->setMessage("error", "Sélectionnez une classe dans la liste déroulante.");
			}
			else
			{
				if($_POST['classe'] != $_SESSION['Eleve']->Classe)
				{
					$ToUpdate['Classe'] = $_POST['classe'];
				}
				
				if($_POST['section'] != $_SESSION['Eleve']->Section)
				{
					$ToUpdate['Section'] = $_POST['section'];
				}
				
				//Ne commiter que s'il y a des modifications.
				if(empty($ToUpdate))
				{
					$this->View->setMessage("warning", "Aucune modification.");
				}
				else
				{
					$_SESSION['Eleve']->setAndSave($ToUpdate);
					$this->View->setMessage("info", "Modifications du compte enregistrées.");
				}
			}
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
			'ID'=>Sql::lastId(),
			'Classe'=>intval($Datas['classe']),
			'Section'=>$Datas['section']
		);
		
		return Sql::insert('Eleves', $ToInsert);
	}
}