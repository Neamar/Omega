<?php
/**
 * indexController.php - 5 mar. 2011
 * 
 * Actions de base sur les articles du blog
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
 * Contrôleur d'article du module blog.
 *
 * @category Controller
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 */
class Blog_ArticleController extends AbstractController
{
	/**
	 * Page d'accueil du module.
	 * 
	 */
	public function indexAction()
	{
		$this->redirect('/blog/');
	}
	
	public function consulterActionWd()
	{
		//Vérifications de validité
		if(!isset($this->Data['data']))
		{
			go404('Erreur au chargement.');
		}
		if(!is_numeric($this->Data['data']))
		{
			go404('Impossible de récupérer l\'enregistrement spécifié');
		}
		
		//Récupération de l'article
		$Article = Sql::singleQuery('SELECT Auteur, Creation, Titre, Abstract, Article FROM Blog_Articles WHERE ID = ' . intval($this->Data['data']));
		
		if(is_null($Article))
		{
			go404('Cet article n\'existe pas.');
		}
		
		//Tout semble OK, construire un fil d'Arian cohérent
		$Ariane = array();
		
		$Ariane['/blog/'] = 'Blog';
		$Ariane['/blog/article/'] = 'Article';
		$Ariane['/blog/article/consulter/' . $this->Data['data']] = $Article['Titre'];
		
		
		$this->View->setBreadcrumbs($Ariane);
		
		//Charger l'aide de vue HTML dont on aura besoin pour la mise en forme d'Abstract
		include OO2FS::viewHelperPath('Html');
		
		//Metre en forme le Abstract pour un affichage correct en tête de page
		$Article['Abstract'] = str_replace(
			array('<p>', '</p>'),
			'',
			ViewHelper_Html_fromTex($Article['Abstract'])
		);
		
		$this->View->setTitle(
			$Article['Titre'],
			$Article['Abstract']
		);
		
		$this->View->Article = $Article;
	}
}