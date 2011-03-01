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
 * Contrôleur d'index du module administrateur.
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
		
		$this->View->Virements = $this->virementsCount();
		$this->View->Alertes = $this->alertesCount();
		$this->View->Reclamations = $this->reclamationsCount();
		$this->View->Validations = $this->validationsCount();
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
					$this->View->setMessage('info', "Bienvenue sur votre compte, despote aux pleins pouvoirs !");
					$this->redirect('/administrateur/');
				}
			}
		}
	}
	
	/**
	 * Liste des virements à réaliser
	 */
	public function virementsAction()
	{
		$this->View->setTitle(
			'Liste des virements',
			'Cette page permet d\'effectuer les différents virements demandés.'
		);
		
		$this->View->NbVirements = $this->virementsCount();
		
		$Query = 'SELECT Virements.ID, Membres.Mail, Virements.Date, Virements.Montant, Virements.Beneficiaire
		FROM Virements
		JOIN Membres ON (Membres.ID = Virements.Membre)
		WHERE Virements.Type = "__TYPE__"
		AND Virements.Statut = "INDETERMINE"';
		
		$this->View->VirementsRib = Sql::queryAssoc(
			str_replace('__TYPE__', 'RIB', $Query),
			'ID'
		);
		
		$this->View->VirementsPaypal = Sql::queryAssoc(
			str_replace('__TYPE__', 'PAYPAL', $Query),
			'ID'
		);
			
	}
	
	/**
	 * Liste des alertes à traiter
	 */
	public function alertesAction()
	{
		
	}
	
	/**
	 * Liste des réclamations à traiters
	 */
	public function reclamationsAction()
	{
		$this->View->setTitle(
			'Liste des réclamations',
			'Cette page permet de traiter les différentes réclamations.'
		);
		
		$this->View->NbReclamations = $this->reclamationsCount();
		
		$this->View->Reclamations = Sql::queryAssoc(
			'SELECT Exercices.Hash, Exercices_Logs.Date, Exercices.Titre, Eleves.Mail AS EMail, Correcteurs.Mail AS CMail
			FROM Exercices_Logs
			JOIN Exercices ON (Exercices_Logs.Exercice = Exercices.ID)
			JOIN Membres Eleves ON(Eleves.ID = Exercices.Createur)
			JOIN Membres Correcteurs ON(Correcteurs.ID = Exercices.Correcteur)
			WHERE Exercices.Statut = "REFUSE"
			AND Exercices_Logs.NouveauStatut = "REFUSE"',
			'Hash'
		);
	}
	
	/**
	 * Liste des validations à réaliser
	 */
	public function validationsAction()
	{
		$this->View->setTitle(
			'Liste des validations',
			'Cette page permet d\'effectuer les différentes valdiations demandées.'
		);
		
		$this->View->NbValidations = $this->validationsCount();
		
		$this->View->Validations = Sql::queryAssoc(
			'SELECT Membres.Mail, Membres.Creation, CONCAT(Prenom, " ", UPPER(Nom)) AS Identite, Membres.Mail AS CV
			FROM Membres
			JOIN Correcteurs ON(Correcteurs.ID = Membres.ID)
			WHERE Membres.Statut = "EN_ATTENTE"',
			'Mail'
		);
	}
	
	/**
	 * Liste des utilisateurs bannis
	 */
	public function bannisAction()
	{
		$this->View->setTitle(
			'Liste des IPs bannies',
			'Cette page permet de bloquer une IP, et de consulter la liste des bannis.'
		);
		Util::ban('158.125.45.18', 3618);
		Util::ban('159.125.45.18', 3571);
		Util::ban('247.125.45.18', 100024);
		
		//Débannir
		if(isset($_POST['unban']))
		{
			if(unlink(DATA_PATH . '/ips/ban/' . preg_replace('`[^0-9.]`', '', $_POST['unban'])))
			{
				$this->View->setMessage('ok', 'Cette ip a été débannie');
				$this->redirect('/administrateur/bannis');
			}
		}
		
		//Bannir
		if(isset($_POST['ajout-ban']) && !empty($_POST['ip']) && !empty($_POST['duree']) && is_numeric($_POST['duree']))
		{
			Util::ban(preg_replace('`[^0-9.]`', '', $_POST['ip']), intval($_POST['duree']));
		}
		
		$this->View->Bannis = glob(DATA_PATH . '/ips/ban/*');
	}
	
	/**
	 * Renvoie le nombre de virements en attente d'être effectués.
	 * 
	 * @return int
	 */
	protected function virementsCount()
	{
		return Sql::singleColumn(
			'SELECT COUNT(*) AS S
			FROM Virements
			WHERE Statut = "INDETERMINE"',
			'S'
		);
	}
	
	/**
	 * Renvoie le nombre d'alertes en attente d'être traitées
	 * 
	 * @return int
	 */
	protected function alertesCount()
	{
		return Sql::singleColumn(
			'SELECT COUNT(*) AS S
			FROM Alertes
			WHERE Statut = "ATTENTE"',
			'S'
		);
	}
	
	
	/**
	 * Renvoie le nombre d'exercices pour lesquels une réclamation a été déposée
	 * 
	 * @return int
	 */
	protected function reclamationsCount()
	{
		return Sql::singleColumn(
			'SELECT COUNT(*) AS S
			FROM Exercices
			WHERE Statut = "REFUSE"',
			'S'
		);
	}

	/**
	 * Renvoie le nombre de correcteurs en attente de validation
	 * 
	 * @return int
	 */
	protected function validationsCount()
	{
		return Sql::singleColumn(
			'SELECT COUNT(*) AS S
			FROM Membres
			WHERE Statut="EN_ATTENTE"',
			'S'
		);
	}
	
	/**
	 * Bloque l'inscription d'un administrateur.
	 * 
	 * @see IndexAbstractController::createAccountSpecial()
	 * 
	 * @param array $Datas les données envoyées lors de la tentative d'inscription.
	 */
	protected function createAccountSpecial(array $Datas)
	{
		Sql::rollback();
		throw new Exception('Fuck.');
	}
}