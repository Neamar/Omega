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
		$this->View->setTitle('Accueil correcteur');

	}
	
	/**
	 * Page de connexion.
	 * Les données peuvent avoir été envoyées depuis la page d'accueil ou depuis cette page là.
	 * 
	 */
	public function connexionAction()
	{
		$this->View->setTitle('Connexion correcteur');
	}
	
	/**
	 * Page d'inscription.
	 * 
	 */
	public function inscriptionAction()
	{
		$this->View->setTitle('Inscription correcteur');
		
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
			elseif(!preg_match('`^0[1-8]([-. ]?[0-9]{2}){4}$`',$_POST['telephone']))
			{
				$this->View->setMessage("error", "Vous devez indiquer un numéro de téléphone valide (0X XX XX XX XX).");
			}
			elseif($_POST['siret'] != '' && !$this->validateSiret($_POST['siret']))
			{
				$this->View->setMessage("error", "Numéro de SIRET invalide. Si vous n'avez pas encore de SIRET, laissez le champ vide.");
			}
			elseif($_FILES['cv']['name'] == '')
			{
				$this->View->setMessage("error", "Vous n'avez pas fourni votre CV.",'correcteur/pourquoi_cv');
			}
			elseif($_FILES['cv']['error'] > 0)
			{
				$this->View->setMessage("error", 'Une erreur est survenue lors de l\'envoi du fichier ' .  $_FILES['cv']['name'] . ' (erreur ' . $_FILES['cv']['error'] . ')', '/eleve/erreurs_upload');
			}
			elseif(Util::extension($_FILES['cv']['name']) != 'pdf')
			{
				$this->View->setMessage("error", 'Votre CV doit-être au format PDF.','correcteur/pourquoi_cv');
			}
			elseif($_FILES['cv']['size'] > 3*1048576)
			{
				$this->View->setMessage("error", 'Votre CV ne doit pas dépasser 3Mo.','correcteur/pourquoi_cv');
			}
			else
			{
				$ID = $this->createAccount($_POST,'CORRECTEUR');
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
			'Telephone'=>preg_replace('`[^0-9]`','',$Datas['telephone']),
		);
		
		if(isset($Datas['siret']) && $Datas['siret'] != '')
		{
			$ToInsert['Siret'] = $Datas['siret'];
		}
		
		return Sql::insert('Correcteurs', $ToInsert);
	}
	
	/**
	 * Valide un numéro de SIREN.
	 * @see http://pear.php.net/package/Validate_FR/download
	 * 
	 * @param string $siren
	 */
	private function validateSiren($siren)
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
	private function validateSiret($siret)
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