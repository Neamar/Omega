<?php
/**
 * OptionsAbstractController.php - 17 janv. 2011
 * 
 * Contrôleur abstrait pour la gestion des options compte.
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
 * Gestion des options
 *
 * @category Default
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 */
abstract class OptionsAbstractController extends AbstractController
{
	/**
	 * Modifie un compte de base.
	 * Appelle la fonction editAccountSpecial() qui gère les opérations spécifiques selon l'héritage (numéro de téléphone, classe...)
	 * @see OptionsAbstractController::compteAction
	 * 
	 * @param array $Data les données envoyées
	 * @param Membre $Base le membre actuel (pour ne pas mettre à jour ce qui ne change pas)
	 * 
	 * @return array le tableau des modifications à effecter, ou FAIL (avec un message dans ce cas)
	 */
	protected function editAccount(array $Datas, Membre $Base)
	{
		if(!$this->getMembre()->comparePass(Util::hashPass($_POST['password'])))
		{
			$this->View->setMessage('error', "Mot de passe invalide ; impossible de modifier votre compte.");
		}
		else if(!Validator::mail($Datas['email']))
		{
			$this->View->setMessage('error', "L'adresse email spécifiée est incorrecte.");
		}
		else if(External::isTrash($Datas['email']))
		{
			$this->View->setMessage('error', "Désolé, nous n'acceptons pas les adresses jetables.");
		}
		else if(!empty($Datas['new-password-confirm']) && $Datas['new-password'] != $Datas['new-password-confirm'])
		{
			$this->View->setMessage('error', "Les deux mots de passe ne concordent pas.");
		}
		else
		{
			$ToUpdate = array();

			if($Datas['email'] != $Base->Mail)
			{
				$ToUpdate['Mail'] = $Datas['email'];
			}
			
			if(!empty($Datas['new-password-confirm']))
			{
				$ToUpdate['Pass'] = sha1(SALT . $Datas['new-password']);
			}
			
			return $ToUpdate;
		}
		
		return FAIL;
	}
	
	/**
	 * Options pour la modification du compte (adresse mail, ...)
	 */
	public abstract function compteAction();
}