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
	 * Page d'accueil du module ; connecter le membre si nécessaire, puis afficher les infos du compte et les liens utiles. 
	 * 
	 */
	public function indexAction()
	{
		$this->View->setTitle(
			'Accueil correcteur',
			'Cette page regroupe les différentes actions qui vous sont disponibles en tant que correcteur.'
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
			'Connexion correcteur',
			'Connectez-vous pour accéder au site.'
		);
		
		//Si on est connecté au moment d'arriver sur cette page, déconnexion.
		if(isset($_SESSION['Correcteur']))
		{
			unset($_SESSION['Correcteur']);
			$this->View->setMessage("info", "Vous vous êtes déconnecté.", 'eleve/deconnexion');
		}
		
		if(isset($_POST['connexion-correcteur']))
		{
			if(!isset($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
			{
				$this->View->setMessage("error", "L'adresse email spécifiée est incorrecte.");
			}
			else
			{
				$Correcteur = $this->logMe($_POST['email'], $_POST['password'], 'Correcteur');
				if(!is_null($Correcteur))
				{
					if($Correcteur->Statut == 'EN_ATTENTE')
					{
						$this->View->setMessage("error", "Votre compte est actuellement en attente de validation de notre part. Vous serez informé par mail sous 48h ouvrées.", 'correcteur/validation');
						unset($_SESSION['Correcteur']); 
					}
					else
					{
						$this->View->setMessage("infos", "Bienvenue sur votre compte ! Solde : " . $Correcteur->getPoints());
						$this->redirect('/correcteur/');
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
	 * Page d'options globales.
	 */
	public function optionsAction()
	{
		$this->View->setTitle(
			'Options correcteur',
			'Modifiez ici vos informations de compte, ou vos capacités pour chaque matière.'
		);
		
		$this->View->Compte = $this->concat('/correcteur/options_compte');
		$this->View->Matieres = $this->concat('/correcteur/options_matieres');
	}
	
	/**
	 * Page d'options pour la mise à jour du compte
	 */
	public function options_CompteAction()
	{
		$this->View->setTitle(
			'Modifications du compte',
			"Cette page vous permet de modifier les informations de votre compte.<br />
Si vous ne l'avez pas encore fait, vous pourrez aussi spécifier votre numéro de SIRET."
		);

		if(isset($_POST['edition-compte']))
		{
			$ToUpdate = $this->editAccount($_POST, $_SESSION['Correcteur']);
			if($ToUpdate == FAIL)
			{
				//La mise à jour ne doit pas être effectuée.
				//Le message a été défini par editAccount.
			}
			elseif(!$this->validatePhone($_POST['telephone']))
			{
				$this->View->setMessage("error", "Vous devez indiquer un numéro de téléphone valide (0X XX XX XX XX).");
			}
			elseif(!empty($_POST['siret']) && !$this->validateSiret($_POST['siret']))
			{
				$this->View->setMessage("error", "Numéro de SIRET invalide. Si vous n'avez pas encore de SIRET, laissez le champ vide.");
			}
			else
			{
				if($_POST['telephone'] != $_SESSION['Correcteur']->Telephone)
				{
					$ToUpdate['Telephone'] = preg_replace('`[^0-9]`', '', $_POST['telephone']);
				}
				if(!empty($_POST['siret']))
				{
					$ToUpdate['Siret'] = $_POST['siret'];
				}
				
				//Ne commiter que s'il y a des modifications.
				if(empty($ToUpdate))
				{
					$this->View->setMessage("warning", "Aucune modification.");
				}
				else
				{
					$_SESSION['Correcteur']->setAndSave($ToUpdate);
					$this->View->setMessage("info", "Modifications du compte enregistrées.");
				}
			}
		}
	}
	
	/**
	 * Page d'options pour la mise à jour du compte
	 */
	public function options_MatieresAction()
	{
		$this->View->setTitle(
			'Modifications des compétences',
			"Cette page vous permet de modifier vos compétences ; et ainsi de filtrer les exercices pour n'afficher que ceux qui vous correspondent."
		);
		
		$this->View->addScript();
		//Charger la liste des matières :
		$Matieres = SQL::queryAssoc('SELECT Matiere FROM Matieres', 'Matiere', 'Matiere');
		
		//Charger la liste des matières :
		$this->View->Classes = SQL::queryAssoc('SELECT Classe, DetailsClasse FROM Classes ORDER BY Classe DESC', 'Classe', 'DetailsClasse');
		
		if(isset($_POST['edition-compte']))
		{
			Sql::query('DELETE FROM Correcteurs_Capacites WHERE Correcteur = ' . $_SESSION['Correcteur']->getFilteredId());
			foreach($Matieres as $Matiere)
			{
				$ID = preg_replace('`[^-a-zA-Z]`', '', $Matiere);
				if(!empty($_POST['check_' . $ID]))
				{
					$Debut = intval($_POST['start_' . $ID]);
					$Fin = intval($_POST['end_' . $ID]);
					
					if($Debut < $Fin)
					{
						$this->View->setMessage("error", "WTF ? Le slider... a buggé ? Nooooon !");
						break;
					}
					else
					{
						$ToInsert = array
						(
							'Correcteur' => $_SESSION['Correcteur']->getFilteredId(),
							'Matiere' => $Matiere,
							'Commence' => $Debut,
							'Finit' => $Fin,
						);
						
						Sql::insert('Correcteurs_Capacites', $ToInsert);
					}
				}
			}
			
			if(!$this->View->issetMeta('message'))
			{
				$this->View->setMessage("info", "Compétences enregistrées.");
			}
		}
		
		$this->View->Matieres = $Matieres;
		$this->View->Defaults = SQL::queryAssoc('SELECT Matiere, Commence, Finit FROM Correcteurs_Capacites WHERE Correcteur = ' . $_SESSION['Correcteur']->getFilteredId(), 'Matiere');
	}
	
	/**
	 * Affiche la "foire aux exercices" du correcteur.
	 */
	public function listeAction()
	{
		$this->View->setTitle(
			'Marché aux exercices',
			"Cette page liste les articles en attente de correcteur... pourquoi pas vous ?"
		);
	}
	
	/**
	 * Affiche la "foire aux exercices" du correcteur (partie données)
	 */
	public function _listeAction()
	{
		//On a besoin du gestionnaire de date.
		include OO2FS::viewHelperPath('Date');
		
		$RawDatas = Sql::queryAssoc(
			'SELECT Hash, UNIX_TIMESTAMP(TimeoutEleve) AS TimeoutEleve, Titre, Matiere, Classes.DetailsClasse, Demandes.DetailsDemande, InfosEleve 
			FROM Exercices
			NATURAL JOIN Classes
			NATURAL JOIN Demandes
			',
			'Hash'
		);
		
		//TODO: Déporter ça dans une vue, rogntondidju !
		$Datas = array();
		
		foreach($RawDatas as $Hash => $SubDatas)
		{
			
			$Expiration = '<br /><a href="/correcteur/exercice/reservation/' . $Hash . '">Je prends !</a><br />';
			$Expiration .= ViewHelper_Date_countdown($SubDatas['TimeoutEleve']);
			
			$Infos = 'Exercice « <strong>' . $SubDatas['Titre'] . '</strong> »';
			$Datas[] = array(
				$Expiration,
				$Infos
			);
		}
		
		$this->json($Datas);
	}
	
	
	/**
	 * Page d'inscription.
	 * 
	 */
	public function inscriptionAction()
	{
		$this->View->setTitle(
			'Inscription correcteur',
			"L'inscription à <strong>eDevoir</strong> en tant que correcteur demande de nombreuses informations, afin que nous puissions juger de vos compétences."
		);
		
		//Le membre vient de s'inscrire mais revient sur cette page.
		if(isset($_SESSION['Correcteur_JusteInscrit']) && !$this->View->issetMeta('message'))
		{
			$this->View->setMessage("info", "Vous êtes déjà inscrit ! Votre demande est en cours de traitement.", 'correcteur/validation');
		}
		
		if(isset($_POST['inscription-correcteur']))
		{
			if(empty($_POST['nom']))
			{
				$this->View->setMessage("error", "Vous devez indiquer votre nom.");
			}
			elseif(empty($_POST['prenom']))
			{
				$this->View->setMessage("error", "Vous devez indiquer votre prénom.");
			}
			elseif(!$this->validatePhone($_POST['telephone']))
			{
				$this->View->setMessage("error", "Vous devez indiquer un numéro de téléphone valide (0X XX XX XX XX).");
			}
			elseif(!empty($_POST['siret']) && !$this->validateSiret($_POST['siret']))
			{
				$this->View->setMessage("error", "Numéro de SIRET invalide. Si vous n'avez pas encore de SIRET, laissez le champ vide.");
			}
			elseif($_FILES['cv']['name'] == '')
			{
				$this->View->setMessage("error", "Vous n'avez pas fourni votre CV.", 'correcteur/pourquoi_cv');
			}
			elseif($_FILES['cv']['error'] > 0)
			{
				$this->View->setMessage("error", 'Une erreur est survenue lors de l\'envoi du fichier ' .  $_FILES['cv']['name'] . ' (erreur ' . $_FILES['cv']['error'] . ')', '/eleve/erreurs_upload');
			}
			elseif(Util::extension($_FILES['cv']['name']) != 'pdf')
			{
				$this->View->setMessage("error", 'Votre CV doit-être au format PDF.', 'correcteur/pourquoi_cv');
			}
			elseif($_FILES['cv']['size'] > 3*1048576)
			{
				$this->View->setMessage("error", 'Votre CV ne doit pas dépasser 3Mo.', 'correcteur/pourquoi_cv');
			}
			else
			{
				$ID = $this->createAccount($_POST, 'CORRECTEUR');
				if($ID != FAIL)
				{
					//Enregistrer le CV :	
					move_uploaded_file($_FILES['cv']['tmp_name'], PATH . '/data/CV/' . $ID . '.pdf');
					
					//Enregistrer le nouveau membre et le rediriger vers la page de connexion
					$Datas = array(
						'mail'=>$_POST['email'],
					);
					External::templateMail($_POST['email'], '/correcteur/inscription', $Datas);
					$_SESSION['Correcteur_JusteInscrit'] = $_POST['email'];
					$this->View->setMessage("info", "Nous avons bien reçu votre demande. Vous serez informé par mail de notre verdict.");
					$this->redirect('/correcteur/connexion');
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
	
	/**
	 * Valide un numéro de téléphone
	 * 
	 * @param string $Phone
	 * 
	 * @return bool
	 */
	protected function validatePhone($Phone)
	{
		return preg_match('`^0[1-8]([-. ]?[0-9]{2}){4}$`', $Phone);
	}
	
	/**
	 * Valide un numéro de SIREN.
	 * @see http://pear.php.net/package/Validate_FR/download
	 * 
	 * @param string $siren
	 */
	protected function validateSiren($siren)
	{
        $siren = str_replace(array(' ', '.', '-'), '', $siren);
        $reg = "/^(\d)(\d)(\d)(\d)(\d)(\d)(\d)(\d)(\d)$/";
        if(!preg_match($reg, $siren, $match))
        {
            return false;
        }
        $match[2] *= 2;
        $match[4] *= 2;
        $match[6] *= 2;
        $match[8] *= 2;
        $sum = 0;

        for($i = 1; $i < count($match); $i++)
        {
            if($match[$i] > 9)
            {
                $a = (int) substr($match[$i], 0, 1);
                $b = (int) substr($match[$i], 1, 1);
                $match[$i] = $a + $b;
            }
            $sum += $match[$i];
        }
        return (($sum % 10) == 0);
	}
	
	/**
	 * Valide un numéro de SIRET.
	 * @see http://pear.php.net/package/Validate_FR/download
	 * 
	 * @param string $siret
	 * 
	 * @return bool true si valide.
	 */
	protected function validateSiret($siret)
	{
		$siret = str_replace(array(' ', '.', '-'), '', $siret);
		$reg = "/^(\d)(\d)(\d)(\d)(\d)(\d)(\d)(\d)(\d)(\d)(\d)(\d)(\d)(\d)$/";
		if(!preg_match($reg, $siret, $match))
		{
			return false;
		}
		else
		{
			if(!Validate_FR::siren(implode('', array_slice($match, 1, 9)))) {
				return false;
			}
		}
		$match[1] *= 2;
		$match[3] *= 2;
		$match[5] *= 2;
		$match[7] *= 2;
		$match[9] *= 2;
		$match[11] *= 2;
		$match[13] *= 2;
		$sum = 0;
	
		for($i = 1; $i < count($match); $i++)
		{
			if($match[$i] > 9)
			{
				$a = (int) substr($match[$i], 0, 1);
				$b = (int) substr($match[$i], 1, 1);
				$match[$i] = $a + $b;
			}
			$sum += $match[$i];
		}
		return (($sum % 10) == 0);
	}
}