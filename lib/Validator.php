<?php
/**
 * Validator.php - 17 janv. 2011
 * 
 * Fonctions permettant la validation fiable de données.
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
 * Documentation de la classe
 *
 * @category Default
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 */
class Validator
{
	const DATE_REGEXP = '#^([0-3]?[0-9])/([0-1]?[0-9])/(20[0-1][0-9])$#';
	/**
	 * Valide une adresse mail.
	 * 
	 * @param string $Mail l'adresse à tester
	 * 
	 * @return bool true si ok
	 */
	public static function mail($Mail)
	{
		return filter_var($Mail, FILTER_VALIDATE_EMAIL);
	}
	
	/**
	 * Valide un numéro de téléphone
	 * 
	 * @param string $Phone le numéro de téléphone à testers
	 * 
	 * @return bool true si ok
	 */
	public static function phone($Phone)
	{
		return preg_match('`^0[1-8]([-. ]?[0-9]{2}){4}$`', $Phone);
	}
	
	/**
	 * Valide un numéro de SIREN.
	 * @see http://pear.php.net/package/Validate_FR/download
	 * 
	 * @param string $siren le numéro de SIREN à valider
	 * 
	 * @return bool true si ok
	 */
	public static function siren($siren)
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
	 * @param string $siret le numéro de SIRET à valider
	 * 
	 * @return bool true si ok
	 */
	public static function siret($siret)
	{
		$siret = str_replace(array(' ', '.', '-'), '', $siret);
		$reg = "/^(\d)(\d)(\d)(\d)(\d)(\d)(\d)(\d)(\d)(\d)(\d)(\d)(\d)(\d)$/";
		if(!preg_match($reg, $siret, $match))
		{
			return false;
		}
		else
		{
			if(!$this->validateSiren(implode('', array_slice($match, 1, 9)))) {
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