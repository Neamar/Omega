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
 * Communication extérieures
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
	private static $Datas;
	
	/**
	 * Teste si l'adresse mail provient d'un fournisseur de jetable.
	 * 
	 * @param string $email
	 * 
	 * @return bool true si jetable
	 */
	public static function isTrash($email)
	{
		$Jetables = array('yopmail','ephemail','jetable','trash','brefmail','uggsrock','haltospam','kleemail','email-jetable','destroy-spam','justonemail','letmymail','onemoremail','cool.fr','nospam','nomail','mega.zik','speed.1s','courriel.fr','moncourrier','monemail','monmail','filzmail');
		foreach($Jetables as $Jetable)
		{
			if(strpos($_POST['email'],'@' . $Jetable)!==false)
			{
				return true;
			}
		}
		return false;
	}
	
	/**
	* Envoie un mail à $to.
	* L'envoi du mail se fait en fin de page, afin de ne pas ralentir l'affichage.
	* @param To:String le destinataire du message
	* @param Subject:String le sujet du mail
	* @param Message:String le message au format HTML.
	*/
	public static function mail($to,$subject,$message,$from='no-reply@edevoir.com')
	{
		// Pour envoyer un mail HTML, l'en-tête Content-type doit être défini
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

		// En-têtes additionnels
		$headers .= 'From: eDevoir <' . $from . '>' . "\r\n";

		//register_shutdown_function("mail",$to, $subject, $message, $headers);
		file_put_contents(DATA_PATH . '/logs/last_mail', $subject . "\n\n" . $message);

		Event::log('Envoi de mail à ' . $to . ' : ' . $subject);
	}
	
	/**
	 * Envoie un mail-template.
	 * 
	 * @param string $to le destinataire
	 * @param string $template le nom du template sans html, par exemple /eleve/validation
	 * @param array $Datas
	 */
	public static function template_mail($to, $template, array $Datas)
	{
		//Enregistrer les données
		External::$Datas = $Datas;
		
		//Lire le fichier
		$File = file_get_contents(DATA_PATH . '/mails' . str_replace('.', '',$template) . '.html');
		
		//Le parser
		$File = preg_replace_callback("`\%([a-zA-Z0-9_]+)\%`", 'External::template_replace', $File);
		
		//Réucpérer ses composantes
		$Items = explode("\n",$File,2);
		$subject = $Items[0];
		$message = $Items[1];
		
		//L'envoyer :
		self::mail($to, $subject, $message);
	}
	
	private static function template_replace($Matches)
	{
		if(isset(External::$Datas[$Matches[1]]))
		{
			return External::$Datas[$Matches[1]];
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