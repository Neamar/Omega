<?php
/**
 * indexController.php - 5 mar. 2011
 * 
 * Actions de base du blog
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
 * Contrôleur d'index du module blog.
 *
 * @category Controller
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 */
class Blog_IndexController extends AbstractController
{
	/**
	 * Page d'accueil du module.
	 * 
	 */
	public function indexAction()
	{
		$this->View->setTitle(
			'Blog eDevoir',
			'<strong>Un devoir à réaliser ?</strong> Une équipe de gens compétents se tient à votre
disposition pour vous fournir – dans les délais que <span>vous</span> fixez – un
rendu de qualité et un suivi personnalisé adapté à <span>vos besoins</span>'
		);
		
		
		$this->View->Articles = Sql::queryAssoc(
			'SELECT ID, Titre, Creation, Abstract
			FROM Blog_Articles
			ORDER BY Creation DESC, ID DESC',
			'ID'
		);
	}
}