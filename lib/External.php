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
				return true;
		}
		return false;
	}
}