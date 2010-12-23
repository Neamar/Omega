<?php
/**
 * Membre.php - 2 déc. 2010
 * 
 * Un membre du site.
 * Classe abstraite.
 * 
 * PHP Version 5
 * 
 * @category  Db
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
 * @category Db
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 */
class Membre extends DbObject
{
	const TABLE_NAME = 'Membres';
	public static $Props;
	
		protected $Foreign = array(
		'Createur'=>'Eleve',
		//'Matiere'=>'Matiere',
		'Classe'=>'Classe',
		'Type'=>'Type',
		//'Statut'=>'Statut',
		'Correcteur'=>'Correcteur'
	);
	
	public $Mail;
	//private $Pass;
	protected $Points;
	public $Creation;
	public $Connexion;
	public $Statut;
	public $Type;
	public $RIB;
	public $Paypal;

	
	/**
	 * Renvoie true si le membre peut se permettre de débourser $Value.
	 * Remet à jour l'objet.
	 * 
	 * @param int $Value
	 * 
	 * @return bool true si le compte est supérieur à la valeur demandée
	 */
	public function isAbleToDebit($Value)
	{
		return ($this->getDebitAbility()>=$Value);
	}
	
	/**
	 * Renvoie la somme possédée par l'élève.
	 * ATTENTION. Cette somme peut-être désynchronisée avec la base de données.
	 * Utiliser getDebitAbility() pour obtenir une valeur fiable.
	 * 
	 * @see Membre::getDebitAbility
	 * @return int la somme maximale dépensable.
	 */
	public function getPoints()
	{
		return $this->Points;
	}
	
	/**
	 * Renvoie la somme maximale que le membre peut se permettre de débiter.
	 * Force une mise à jour de l'objet.
	 * 
	 * @return int la somme maximale dépensable.
	 */
	public function getDebitAbility()
	{
		$this->update();
		return $this->Points;
	}
	
	/**
	 * Débite l'utilisateur de la somme indiquée.
	 * Si l'utilisateur ne peut pas payer cette somme, la fonction renvoie false.
	 * 
	 * @param int $Value la valeur à débiter
	 * @param string $Log le message de log. Si non spécifié, l'enregistrement des logs est à la charge de l'appelant.
	 * 
	 * @return bool true si la transaction a réussie
	 */
	public function debit($Value,$Log=null)
	{
		if(!is_int($Value))
		{
			return false;
		}
			
		if($this->isAbleToDebit($Value))
		{
			$this->setAndSave(array('_Points'=>'Points - ' . $Value));
			if(!is_null($Log))
			{
				$this->log('Logs', $Log, $this, null, array('Delta'=>-$Value));
			}
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Crédite l'utilisateur de la somme indiquée.
	 * 
	 * @param int $Value la valeur à créditer
	 * @param string $Log le message de log. Si non spécifié, l'enregistrement des logs est à la charge de l'appelant.
	 * 
	 * @return bool true si la transaction a réussie
	 */
	public function credit($Value,$Log=null)
	{
		if(!is_int($Value))
		{
			return false;
		}
		
		$this->setAndSave(array('_Points'=>'Points + ' . $Value));
		if(!is_null($Log))
		{
			$this->log('Logs', $Log, $this, null, array('Delta'=>$Value));
		}
	}
	
	/**
	 * L'utilisateur peut-il effectuer un virement ?
	 * (dépend de la date du dernier virement)
	 * 
	 * @return bool true si la personne peut effectuer un transfert
	 */
	public function isAbleToTransfer()
	{
		return ($this->getTransferAbility()<=time());
	}
	
	/**
	 * À quelle date l'utilisateur pourra-t-il demander un virement ?
	 * 
	 * @return int Le timestamp
	 */
	public function getTransferAbility()
	{
		//TODO Implémenter la date de virement
		return 0;
	}
	
	/**
	 * Renvoie true si le membre est bloqué.
	 * 
	 * @return bool true si le membre est bloqué
	 */
	public function isBlocked()
	{
		return ($this->Statut=='BLOQUE');
	}
}

Membre::$Props = initProps('Membre');