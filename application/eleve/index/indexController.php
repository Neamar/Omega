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
class Eleve_IndexController extends AbstractController
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
	 * Page d'accueil du module ; afficher les infos du compte et les liens utiles. 
	 * 
	 */
	public function indexAction()
	{
		$this->View->setTitle('Accueil élève');
	}
	
	/**
	 * Page d'inscription.
	 * 
	 */
	public function inscriptionAction()
	{
		$this->View->setTitle('Inscription élève');
		
		if(isset($_POST['inscription-eleve']))
		{
			if(!filter_var($_POST['email'],FILTER_VALIDATE_EMAIL))
				$this->View->setMessage("error", "L'adresse email spécifiée est incorrecte.");
			else if(External::isTrash($_POST['email']))
				$this->View->setMessage("error", "Désolé, nous n'acceptons pas les adresses jetables.");
			else if(empty($_POST['password']))
				$this->View->setMessage("error", "Aucun mot de passe spécifié !");
			else if($_POST['password'] != $_POST['password_confirm'])
				$this->View->setMessage("error", "Les deux mots de passe ne concordent pas.");
			else if(!isset($_POST['cgu']) || $_POST['cgu'] != 'on')
				$this->View->setMessage("error", "Vous n'avez pas validé les conditions générales d'utilisation");
			else
			{
				SQL::start();
				$ToInsert = array(
					'Mail'=> $_POST['email'],
					'Pass'=>sha1(SALT . $_POST['password']),
					'_Creation'=>'NOW()',
					'_Connexion'=>'NOW()',
				);
				if(!Sql::insert('Membres', $ToInsert))
				{
					Sql::rollback();
					$this->View->setMessage("error", "Impossible de vous enregistrer. L'adresse email est peut-être déjà réservée ?");
				}
				else
				{
					$ToInsert = array(
						'ID'=>Sql::lastId(),
						'Classe'=>intval($_POST['classe']),
						'Section'=>$_POST['section']
					);
					
					if(!Sql::insert('Eleves',$ToInsert))
					{
						Sql::rollback();
						$this->View->setMessage("error", "Impossible de vous enregistrer. Veuillez réessayer plus tard.");
					}
					else 
					{
						Sql::commit();
						$this->View->setMessage("info", "Vous êtes enregistré !");
					}
				}
			}
		}
		//Charger la liste des matières :
		$this->View->Matieres = SQL::queryAssoc('SELECT ID, Nom FROM Classes ORDER BY ID DESC','ID','Nom');
	}
}