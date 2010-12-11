<?php
/**
 * indexController.php - 26 oct. 2010
 * 
 * Fonctions de debuggage
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
class Debug_IndexController extends AbstractController
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
	 * Page d'accueil du module.
	 * 
	 */
	public function indexAction()
	{
		$this->View->setTitle('Module de debug');
	}
	
	/**
	 * Dumping de la session
	 * 
	 */
	public function sessionAction()
	{
		$this->View->setTitle('Dump de la session.');
	}
	
	/**
	 * Structure d'une session.
	 * 
	 */
	public function session_structAction()
	{
		$this->View->setTitle('Dump de la structure session.');
	}
	
	/**
	 * Dumping des données serveur
	 * 
	 */
	public function serverAction()
	{
		$this->View->setTitle('Dump des données serveur.');
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