<?php
/**
 * indexController.php - 26 oct. 2010
 * 
 * Contrôleur pour la documentation.
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
 * Contrôleur de base pour toute la documentation.
 * 
 * @category Controller
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 *
 */
class Documentation_IndexController extends AbstractController
{
	private static $_Pages = array
	(
		'foo' => array
		(
			'bar' => "Titre de la page d'aide"
		),
		'index' => array
		(
			'index' => "Accueil de la documentation",
			'fonctionnement' => "Fonctionnement du site",
			'faq' => "Questions fréquemment posées",
			'exemples' => "Exemples de corrigés rendus",
			'parents' => "À l’intention des parents",
			'eleves' => "À l’intention des élèves",
			'points' => "Le système de points",
			'cgu' => "Conditions générales d’utilisation",
			'cgv' => "Conditions générales de vente",
			'confidentialite' => "Protection de la vie privée",
			'contact' => "Nous contacter",
			'legal' => "Mentions légales",
			'matieres' => "Liste des matières",
			'niveaux' => "Liste des classes",
			'tex' => "Comment insérer des formules mathématiques dans un élément de la FAQ ?",
		),
		'eleve' => array
		(
			'index' => "Aide des élèves",
			'fonctionnement' => "Comment faire corriger mon devoir ?",
			'inscription' => "Comment m'inscrire en tant qu'élève ?",
			'validation' => "Comment valider mon compte ?",
			'probleme_connexion' => "Pourquoi je ne ne peux pas me connecter ?",
			'deconnexion' => "Pourquoi ai-je été déconnecté ?",
			'depot' => "Comment déposer de l’argent sur mon compte ?",
			'creation' => "Comment ajouter un devoir à faire corriger ?",
			'auto_accept' => "Comment fonctionne l’acceptation automatique ?",
			'champ_rendu' => "À quoi correspond la date d’expiration ?",
			'champ_annulation' => "À quoi correspond la date d’annulation ?",
			'champ_info' => "À quoi sert ce cadre ?",
			'ajout' => "Comment ajouter des fichiers à mon exercice ?",
			'fichiers' => "Quels types de fichiers sont autorisés ?",
			'erreurs_upload' => "J’ai obtenu un message erreur à l’envoi du fichier ?", /* Attention pour les guillemets*/
			'supplement' => "Pourquoi dois-je payer un supplément “suractivité” ?",
			'acces_impossible' => "Pourquoi ne puis-je pas accéder à un exercice ?",
			'annulation' => "Comment annuler mon devoir ?",
			'refus' => "Comment puis-je refuser une offre ?",
			'acceptation' => "Que se passe-t-il une fois l’offre du correcteur acceptée ?",
			'vie_privee' => "J’ai eu une alerte vie privée, à quoi cela correspond-il ?",
			'faq' => "Comment utiliser la FAQ pour poser des questions sur mon exercice ?",
			'envoi_gratuit' => "Pourquoi ai-je été remboursé alors que je n’ai reçu qu’une partie du travail ?",
			'retard' => "Que se passe-t-il en cas de retard ?",
			'envoye' => "J’ai reçu un mail m’indiquant que le corrigé est disponible, que faire ?",
			'contestation' => "Le travail a été mal réalisé, comment puis-je me faire rembourser ?",
			'remboursement' => "Que se passe-t-il une fois que la procédure de remboursement est lancée ?",
			'mecontentement_remboursement' => "J’ai été partiellement remboursé mais je ne suis toujours pas satisfait, que faire ?",
			'contestation_tardive' => "J’ai dit que l’exercice me convenait ou j’ai laissé passer la limite, mais je souhaite contester. Comment faire ?",
			'retrait' => "Comment faire pour retirer des points ?",
			'bloque' => "Mon compte est bloqué, pourquoi ? Que faire ?",
			'desinscription' => "Comment faire pour me désinscrire ?",
		),
	);
	
	/**
	 * Récupère le titre d'une page.
	 * 
	 * @param string $section
	 * @param string $page
	 * 
	 * @return le titre de la page $section/$page.
	 */
	public static function getTitle($section, $page)
	{
		if(isset(self::$_Pages[$section][$page]))
		{
			return self::$_Pages[$section][$page];
		}
		else
		{
			return 'Page inconnue.';
		}
	}
}