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
 * @link      http://devoirminute.com
 */

/**
 * Contrôleur d'index du module élève.
 * 
 * @category Controller
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://devoirminute.com
 *
 */
class Eleve_IndexController extends IndexAbstractController
{
	/**
	 * Constructeur.
	 * 
	 * @param string $module nom de module
	 * @param string $controller nom de contrôleur
	 * @param string $view nom de vue
	 * @param string $data données
	 */
	public function __construct($module,$controller,$view,$data)
	{
		parent::__construct($module, $controller, $view, $data);
	}
	
	/**
	 * Page d'accueil du module ; connecter le membre si nécessaire, puis afficher les infos du compte et les liens utiles. 
	 * 
	 */
	public function indexAction()
	{
		$this->View->setTitle('Accueil élève');
		
		if(isset($_POST['connexion-eleve']))
		{
			$Eleve = $this->logMe($_POST['mail'], $_POST['password'], 'Eleve');
			if(!is_null($Eleve))
			{
				if($Eleve->Statut == 'EN_ATTENTE')
				{
					$this->View->setMessage("error", "Votre compte est actuellement en attente de validation. Consultez votre boite mail pour plus de détails.");
					$_SESSION['Eleve'] = NULL;
				}
				else
				{
					$this->redirect('/eleve/');
				}
			}
			else
			{
				$this->View->setMessage("error", "Identifiants incorrects.");
			}
		}
	}
	
	/**
	 * Page de connexion.
	 * Les données peuvent avoir été envoyées depuis la page d'accueil ou depuis cette page là.
	 * 
	 */
	public function connexionAction()
	{
		$this->View->setTitle('Connexion élève');
		
		if(isset($_POST['connexion-eleve']))
		{
			$Eleve = $this->logMe($_POST['email'], $_POST['password'], 'Eleve');
			if(!is_null($Eleve))
			{
				if($Eleve->Statut == 'EN_ATTENTE')
				{
					$this->View->setMessage("error", "Votre compte est actuellement en attente de validation. Consultez votre boite mail pour plus de détails.");
					$_SESSION['Eleve'];// = NULL;
				}
				else
				{
					$this->redirect('/eleve/');
				}
			}
			else
			{
				$this->View->setMessage("error", "Identifiants incorrects.");
			}
		}
	}
	
	/**
	 * Page d'inscription.
	 * 
	 */
	public function inscriptionAction()
	{
		$this->View->setTitle('Inscription élève');
								$Datas = array(
					'mail'=>'neamar@neamar.fr',
					'lien'=>sha1(SALT . 3 . 'neamar@neamar.fr') . '/mail/' . 'neamar@neamar.fr',
				);
				External::template_mail('neamar@neamar.fr', '/eleve/validation', $Datas);
		if(isset($_POST['inscription-eleve']))
		{
			if($ID = $this->create_account($_POST) !== FAIL)
			{
				$Datas = array(
					'mail'=>$_POST['email'],
					'lien'=>sha1(SALT . $ID . $_POST['email']) . '/mail/' . $_POST['email'],
				);
				External::template_mail($_POST['email'], '/eleve/validation', $Datas);
				$this->View->setMessage("info", "Vous êtes maintenant membre ! Veuillez cliquer sur le lien d'enregistrement qui vous a été envoyé par mail pour terminer votre inscription.");
				$this->redirect('/eleve/');
			}
		}

		//Charger la liste des matières :
		$this->View->Matieres = SQL::queryAssoc('SELECT ID, Nom FROM Classes ORDER BY ID DESC','ID','Nom');
	}
	
	/**
	 * Page d'inscription.
	 * 
	 */
	public function validationAction_wd()
	{
		if(!isset($this->Data['data'],$this->Data['mail']))
			exit();
		
		$Eleve = Eleve::load('(SELECT ID FROM Membres WHERE Mail = "' . SQL::escape($this->Data['mail']) . '" AND Type="ELEVE")',false);

		if(!is_null($Eleve) && sha1(SALT . $Eleve->ID . $this->Data['mail']) == $this->Data['data'])
		{
			$Eleve->setAndSave(array('Statut'=>'OK'));
			$this->View->setMessage("info","Votre compte est validé ! Vous pouvez maintenant vous connecter");
			$this->redirect("/eleve/");
		}
		else
		{
			$this->View->setMessage("error","Lien de validation invalide.");
			$this->redirect("/eleve/inscription");
		}
	}
	
	/**
	 * Gère l'enregistrement dans la table Eleves en particulier.
	 * 
	 * @see IndexAbstractController::create_account_special()
	 * 
	 * @param array $Datas les données envoyées
	 * 
	 * @return bool true sur un succès.
	 */
	protected function create_account_special(array $Datas)
	{
		$ToInsert = array(
			'ID'=>Sql::lastId(),
			'Classe'=>intval($Datas['classe']),
			'Section'=>$Datas['section']
		);
		
		return Sql::insert('Eleves',$ToInsert);
	}
}