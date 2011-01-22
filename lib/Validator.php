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
 */
 
/**
 * Classe statique pour la validation des données.
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
	 * Valide un compte paypal
	 * 
	 * @param $Compte le compte à tester
	 *
	 * @return true si ok
	 */
	public static function paypal($Compte)
	{
		return self::mail($Compte);
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
			if(!Validator::siren(implode('', array_slice($match, 1, 9)))) {
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
	
	/**
	 * Valide un numéro de RIB.
	 * Exemple : 20041 01005 0500013M026 06
	 * @see http://pear.php.net/package/Validate_FR/download
	 * 
	 * @param array $rib le numéro de RIB à valider
	 * 
	 * @return bool true si ok
	 */
    public static function rib(array $rib)
    {
        if (is_array($rib)) {
            $codebanque = $codeguichet = $nocompte = $key = '';
            extract($rib);
            echo  $codebanque, $codeguichet , $nocompte , $key;
        } else {
            return false;
        }
        $chars  = array('/[AJ]/','/[BKS]/','/[CLT]/','/[DMU]/','/[ENV]/',
                        '/[FOW]/','/[GPX]/','/[HQY]/','/[IRZ]/');
        $values = array('1','2','3','4','5','6','7','8','9');

        $codebank   = preg_replace('/[^0-9]/', '', $codebanque);
        $officecode = preg_replace('/[^0-9]/', '', $codeguichet);
        $account    = preg_replace($chars, $values, $nocompte);

        if (strlen($codebank) != 5) {
            return false;
        }

        if (strlen($officecode) != 5) {
            return false;
        }

        if (strlen($account) > 11) {
            return false;
        }

        if (strlen($key) != 2) {
            return false;
        }

        $padded = str_pad($account, 11, '0', STR_PAD_LEFT);
        $l      = $codebank . $officecode . $padded . $key . '0';
        $keyChk = 0;
        for ($i = 0; $i < 24; $i += 4) {
            $keyChk = ($keyChk*9 + substr($l, $i, 4)) % 97;
        }
        return !$keyChk;
    }
}