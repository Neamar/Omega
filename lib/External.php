<?php
/**
 * External.php - 8 déc. 2010
 * 
 * Gère toutes les communications avec l'extérieur : mails et autres.
 * 
 * PHP Version 5
 * 
 * @category  Default
 * @package   Root
 * @author    Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @copyright 2010 Matthieu Bacconnier
 * @license   Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link      http://edevoir.com
 * @param string
 * @param string
 */
 
/**
 * Communications extérieures
 *
 * @category Default
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 */
class External
{
	/**
	 * Données utiles pour le template du mail avec replace_callback.
	 * 
	 * @see External::template_mail
	 * 
	 * @var array
	 */
	private static $_Datas;
	
	/**
	 * Teste si l'adresse mail provient d'un fournisseur de jetable.
	 * 
	 * @param string $email
	 * 
	 * @return bool true si jetable
	 */
	public static function isTrash($email)
	{
		$Jetables = array('yopmail', 'ephemail', 'jetable', 'trash', 'brefmail', 'uggsrock', 'haltospam', 'kleemail', 'email-jetable', 'destroy-spam', 'justonemail', 'letmymail', 'onemoremail', 'cool.fr', 'nospam', 'nomail', 'mega.zik', 'speed.1s', 'courriel.fr', 'moncourrier', 'monemail', 'monmail', 'filzmail');
		foreach($Jetables as $Jetable)
		{
			if(strpos($_POST['email'], '@' . $Jetable)!==false)
			{
				return true;
			}
		}
		return false;
	}
	
	/**
	* Envoie un mail à $to.
	* L'envoi du mail se fait en fin de page, afin de ne pas ralentir l'affichage.
	* @param string $To le destinataire du message
	* @param string $subject le sujet du mail
	* @param string $message le message au format HTML.
	* @param string $from l'expéditeur (no-reply par défaut)
	*/
	public static function mail($to, $subject, $message, $from = 'no-reply@edevoir.com')
	{
		// Pour envoyer un mail HTML, l'en-tête Content-type doit être défini
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

		// En-têtes additionnels
		$headers .= 'From: eDevoir <' . $from . '>' . "\r\n";

		if(URL == 'http://omega.localhost')
		{
			file_put_contents(DATA_PATH . '/logs/last_mail', $to . PHP_EOL . $subject . PHP_EOL . $message);
		}
		else
		{
			register_shutdown_function("mail", $to, $subject, $message, $headers);
		}
		
		Event::log('Envoi de mail à ' . $to . ' : ' . $subject);
	}
	
	/**
	 * Envoie un mail-template.
	 * 
	 * @param string $to le destinataire
	 * @param string $template le nom du template sans html, par exemple /eleve/validation
	 * @param array $Datas
	 */
	public static function templateMail($to, $template, array $Datas)
	{
		//Enregistrer les données
		External::$_Datas = $Datas;
		
		//Lire le fichier
		$File = file_get_contents(DATA_PATH . '/mails' . str_replace('.', '', $template) . '.html');
		
		//Le parser
		$File = preg_replace_callback("`__([a-zA-Z0-9_]+)__`", 'External::_templateReplace', $File);
		
		//Récupérer ses composantes
		$Items = explode(PHP_EOL, $File, 3);
		$subject = $Items[0];
		$bonjour = $Items[1];
		$message = $Items[2];
		
		//Injecter le tout dans le template
		$Mail = str_replace(
			array(
				'__BONJOUR__',
				'__CONTENU__',
			),
			array(
				$bonjour,
				$message,
			),
			file_get_contents(DATA_PATH . '/layouts/mail.phtml')
		);
		
		//L'envoyer :
		self::mail($to, $subject, $Mail);
	}
	
	/**
	 * Envoie un mail en prenant un membre en paramètre.
	 * Les données usuelles du membre sont intégrées dans le tableau (par exemple, son mail, son nom s'il est correcteur, etc.)
	 * 
	 * @param Membre $to le destinataire du mail, ainsi que les infos de base
	 * @param string $template le template à utiliser
	 * @param array $Datas un tableau de données qui sera complémenté par les données du membre et de l'exercice. En cas de conflit, les clés de ce tableau ont la priorité.
	 * @param Exercice $Exercice un exercice dont les clés principales seront injectées dans le tableau
	 */
	public static function templateMailFast(Membre $to, $template, array $Datas = array(), Exercice $Exercice = null)
	{
		//Incorporer les données du membre
		$_Datas = array();
		$_Datas['mail'] = $to->Mail;
		if(get_class($to) == 'Correcteur')
		{
			$_Datas['nom'] = $to->identite();
		}
		
		//Incorporer les données de l'exercice
		if(!is_null($Exercice))
		{
			$_Datas['titre'] = $Exercice->Titre;
			$_Datas['hash'] = $Exercice->Hash;
			$_Datas['prix_correcteur'] = $Exercice->priceAsked();
			$_Datas['prix_eleve'] = $Exercice->pricePaid();
			$_Datas['expiration'] = date('d/m/Y à H\h', strtotime($Exercice->Expiration)); 
		}
		
		$Datas = array_merge($_Datas, $Datas);
		
		self::templateMail($to->Mail, $template, $Datas);
	}
	
	private static function _templateReplace($Matches)
	{
		if(isset(External::$_Datas[$Matches[1]]))
		{
			return External::$_Datas[$Matches[1]];
		}
		else
		{
			if(defined($Matches[1]))
			{
				return constant($Matches[1]);
			}
			else 
			{
				throw new Exception("Utilisation d'une clé inconnue dans un template : " . $Matches[1]);
				return 'INCONNU';
			}
		}
	}
}