<?php
/**
 * indexController.php - 30 déc. 2010
 * 
 * Actions de base pour un correcteur.
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
 * Contrôleur d'index du module correcteur.
 * 
 * @category Controller
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 *
 */
class Correcteur_IndexController extends IndexAbstractController
{
	/**
	 * Page d'accueil du module ; afficher les infos du compte et les liens utiles. 
	 * 
	 */
	public function indexAction()
	{
		$this->View->setTitle(
			'Accueil correcteur',
			'Cette page regroupe les différentes actions qui vous sont disponibles en tant que correcteur.'
		);
		
		$this->View->Exo = $this->concat('/correcteur/exercice/');
		$this->View->Points = $this->concat('/correcteur/points/');
	}
	
	/**
	 * Page de connexion.
	 * Les données peuvent avoir été envoyées depuis la page d'accueil ou depuis cette page là.
	 * 
	 */
	public function connexionAction()
	{	
		$this->View->setTitle(
			'Connexion correcteur',
			'Connectez-vous pour accéder au site.'
		);
		$this->View->setSeelink('/correcteur/inscription', 'Pas encore inscrit ?');
		
		//Si on est connecté au moment d'arriver sur cette page, déconnexion.
		if(isset($_SESSION['Correcteur']))
		{
			unset($_SESSION['Correcteur']);
			
			//Afficher un message avec de l'aide élève (les procédures de déconnexion sont les mêmes)
			$this->View->setMessage('info', "Vous vous êtes déconnecté.", 'eleve/deconnexion');
		}
		
		if(isset($_POST['connexion-correcteur']))
		{
			if(!isset($_POST['email']) || !Validator::mail($_POST['email']))
			{
				$this->View->setMessage('error', "L'adresse email spécifiée est incorrecte.");
			}
			else
			{
				$Correcteur = $this->logMe($_POST['email'], $_POST['password'], 'Correcteur');
				if(is_null($Correcteur))
				{
					//TODO : Bloquer après trois connexions ?
					$this->View->setMessage('error', "Identifiants incorrects.", 'correcteur/probleme_connexion');
				}
				else
				{
					if($Correcteur->Statut == 'EN_ATTENTE')
					{
						$this->View->setMessage('error', "Votre compte est actuellement en attente de validation de notre part. Vous serez informé par mail sous 48h ouvrées.", 'correcteur/validation');
						unset($_SESSION['Correcteur']); 
					}
					else
					{
						$this->View->setMessage('info', "Bienvenue sur votre compte ! Solde : " . $Correcteur->getPoints() . ' points.');
						
						//Rediriger vers la page d'accueil du module, ou vers la page demandée avant la connexion si ce n'est pas une page Ajax.
						if(isset($_SESSION['CorrecteurComingFrom']) && strpos($_SESSION['CorrecteurComingFrom'], '_') === false)
						{
							$URL = $_SESSION['CorrecteurComingFrom'];
							unset($_SESSION['CorrecteurComingFrom']);
						}
						else
						{
							$URL = '/correcteur/';	
						}
						
						$this->redirect($URL);
					}
				}
			}
		}
	}
	
	/**
	 * Affiche le "marché aux exercices" du correcteur.
	 */
	public function listeAction()
	{
		if($this->getMembre()->Statut == 'BLOQUE')
		{
			$this->View->setMessage('warning', 'Votre compte est bloqué. En conséquence, vous ne pouvez pas réserver de nouvel exercice.', 'correcteur/bloque');
			$this->redirect('/correcteur/');
		}
		if(!$this->getMembre()->isAbleToBook())
		{
			$this->View->setMessage('warning', 'Ne soyez pas trop gourmand... vous avez déjà beaucoup d\'exercices réservés.', 'correcteur/limite_reservation');
			$this->redirect('/correcteur/');
		}
		
		$this->View->setTitle(
			'Marché aux exercices',
			"Cette page liste les articles en attente de correcteur... pourquoi pas vous ?"
		);
		$this->View->setSeelink('/correcteur/options/matieres', 'Modifier mes compétences');
		$this->View->addScript();
		
		$this->View->NbReserves = $this->getMembre()->getBooked();
	}
	
	/**
	 * Affiche le "marché aux exercices" du correcteur (partie données).
	 * Doit faire des opérations relativement complexes sur les données, et ne passe donc pas par le helper ->json et sa vue associée.
	 * Utilise une vue indépendante (_liste.phtml).
	 */
	public function _listeAction()
	{
		$this->View->RawDatas = Sql::queryAssoc(
			'
SELECT 
	Exercices.Hash,
	Exercices.LongHash,
	Expiration,
	Exercices.Titre,
	Exercices.Matiere,
	Exercices.Section,
	Exercices.InfosEleve,
	Classes.DetailsClasse,
	Demandes.DetailsDemande,
	GROUP_CONCAT(Exercices_Fichiers.ThumbURL ORDER BY Exercices_Fichiers.ID SEPARATOR ",") AS SujetsThumbs,
	GROUP_CONCAT(Exercices_Fichiers.URL ORDER BY Exercices_Fichiers.ID SEPARATOR ",") AS Sujets
	
FROM Exercices
NATURAL JOIN Classes
NATURAL JOIN Demandes
LEFT JOIN Exercices_Fichiers ON (
	Exercices.ID = Exercices_Fichiers.Exercice
)
JOIN Correcteurs_Capacites ON (
	Correcteurs_Capacites.Correcteur = ' . $_SESSION['Correcteur']->getFilteredId() . '
	AND Correcteurs_Capacites.Matiere = Exercices.Matiere
	AND Exercices.Classe BETWEEN Correcteurs_Capacites.Finit AND Correcteurs_Capacites.Commence
)

WHERE Statut = "ATTENTE_CORRECTEUR"
AND Exercices.ID NOT IN (
	SELECT Exercice
	FROM Exercices_Correcteurs
	WHERE Correcteur = ' . $_SESSION['Correcteur']->getFilteredId() . '
)

GROUP BY Exercices.ID
ORDER BY Exercices.Expiration
			',
			'Hash'
		);
	}
	
	
	/**
	 * Page d'inscription.
	 * 
	 */
	public function inscriptionAction()
	{
		//On ne s'inscrit pas si on est déjà connecté.
		if(isset($_SESSION['Correcteur']))
		{
			$this->redirect('/correcteur/');
		}
		
		$this->View->setTitle(
			'Inscription correcteur',
			"L'inscription à <strong class=\"edevoir\"><span>e</span>Devoir</strong> en tant que correcteur demande de nombreuses informations, afin que nous puissions juger de vos compétences."
		);
		$this->View->setSeelink('/correcteur/connexion', 'Déjà membre ?');
		
		//Le membre vient de s'inscrire mais revient sur cette page.
		if(isset($_SESSION['Correcteur_JusteInscrit']) && !$this->View->issetMeta('message'))
		{
			$this->View->setMessage('warning', "Vous êtes déjà inscrit ! Votre demande est en cours de traitement.", 'correcteur/validation');
		}
		
		if(isset($_POST['inscription-correcteur']))
		{
			if(empty($_POST['nom']))
			{
				$this->View->setMessage('error', "Vous devez indiquer votre nom.");
			}
			elseif(empty($_POST['prenom']))
			{
				$this->View->setMessage('error', "Vous devez indiquer votre prénom.");
			}
			elseif(!Validator::phone($_POST['telephone']))
			{
				$this->View->setMessage('error', "Vous devez indiquer un numéro de téléphone valide (0X XX XX XX XX).");
			}
			elseif(!empty($_POST['siret']) && !Validator::siret($_POST['siret']))
			{
				$this->View->setMessage('error', "Numéro de SIRET invalide. Si vous n'avez pas encore de SIRET, laissez le champ vide.");
			}
			elseif($_FILES['cv']['name'] == '')
			{
				$this->View->setMessage('error', "Vous n'avez pas fourni votre CV.", 'correcteur/pourquoi_cv');
			}
			elseif($_FILES['cv']['error'] > 0)
			{
				$this->View->setMessage('error', 'Une erreur est survenue lors de l\'envoi du fichier ' .  $_FILES['cv']['name'] . ' (erreur ' . $_FILES['cv']['error'] . ')', '/eleve/erreurs_upload');
			}
			elseif(Util::extension($_FILES['cv']['name']) != 'pdf')
			{
				$this->View->setMessage('error', 'Votre CV doit-être au format PDF.', 'correcteur/pourquoi_cv');
			}
			elseif($_FILES['cv']['size'] > 3*1048576)
			{
				$this->View->setMessage('error', 'Votre CV ne doit pas dépasser 3Mo.', 'correcteur/pourquoi_cv');
			}
			elseif($_FILES['ci']['name'] == '')
			{
				$this->View->setMessage('error', "Vous n'avez pas fourni votre scan de carte d\'identité.", 'correcteur/pourquoi_ci');
			}
			elseif($_FILES['ci']['error'] > 0)
			{
				$this->View->setMessage('error', 'Une erreur est survenue lors de l\'envoi du fichier ' .  $_FILES['ci']['name'] . ' (erreur ' . $_FILES['ci']['error'] . ')', '/eleve/erreurs_upload');
			}
			elseif(!in_array(Util::extension($_FILES['ci']['name']), array('pdf', 'png', 'jpg', 'doc')))
			{
				$this->View->setMessage('error', 'Votre scan doit-être au format PDF, PNG ou JPG.', 'correcteur/pourquoi_carte');
			}
			elseif($_FILES['ci']['size'] > 3*1048576)
			{
				$this->View->setMessage('error', 'Le scan de votre carte d\'identité ne doit pas dépasser 3Mo.', 'correcteur/pourquoi_carte');
			}
			elseif(!Validator::captcha())
			{
				$this->View->setMessage('error', "Le captcha rentré est incorrect. Merci de réessayer.");
			}
			else
			{
				$ID = $this->createAccount($_POST, 'CORRECTEUR');
				if($ID != FAIL)
				{
					//Enregistrer le CV :	
					move_uploaded_file($_FILES['cv']['tmp_name'], PATH . '/data/CV/' . $ID . '.pdf');
					//Enregistrer la CI
					move_uploaded_file($_FILES['ci']['tmp_name'], PATH . '/data/CI/' . $ID . '.' . Util::extension($_FILES['ci']['name']));
					
					//Mission accomplie ! Dispatcher l'évènement :
					Event::dispatch(
						Event::CORRECTEUR_INSCRIPTION,
						array(
							'mail' => $_POST['email']
						)
					);
					
					//Rediriger le nouveau membre vers la page de connexion
					$_SESSION['Correcteur_JusteInscrit'] = $_POST['email'];
					$this->View->setMessage('info', "Nous avons bien reçu votre demande. Vous serez informé par mail de notre verdict.");
					$this->redirect('/correcteur/connexion');
				}
			}
		}
	}
	
	/**
	 * Vérifie que le correcteur peut se désinscrire
	 * @see IndexAbstractController::desinscriptionAction()
	 */
	public function desinscriptionAction()
	{
		$ExercicesActifs = Sql::singleColumn(
			'SELECT COUNT(*) AS S
			FROM Exercices
			WHERE Statut IN ("ATTENTE_ELEVE", "EN_COURS")
			AND Correcteur = ' . $this->getMEmbre()->getFilteredId(),
			'S'
		);
		
		if($ExercicesActifs > 0)
		{
			$this->View->setMessage('warning', 'Impossible de vous désinscrire pour l\'instant, vous devez d\'abord terminer vos exercices. Merci !');
			$this->redirect('/correcteur/');
		}
		
		parent::desinscriptionAction();
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
		$ToInsert = array(
			'ID'=>Sql::lastId(),
			'Prenom'=>$Datas['prenom'],
			'Nom'=>$Datas['nom'],
			'Telephone'=>preg_replace('`[^0-9]`', '', $Datas['telephone']),
		);
		
		if(isset($Datas['siret']) && $Datas['siret'] != '')
		{
			$ToInsert['Siret'] = $Datas['siret'];
		}
		
		return Sql::insert('Correcteurs', $ToInsert);
	}
}