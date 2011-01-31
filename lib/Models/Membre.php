<?php
/**
 * Membre.php - 2 déc. 2010
 * 
 * Un membre du site.
 * Classe théoriquement abstraite, mais certaines fonctions s'en servent "bas niveau" (rechercher Membre::get)
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
	
	/**
	 * Récupérer le banquier.
	 * 
	 * @return Membre
	 */
	public static function getBanque()
	{
		return Membre::load(BANQUE_ID);
	}
	
	protected $Foreign = array(
		'Createur'=>'Eleve',
		//'Matiere'=>'Matiere',
		'Classe'=>'Classe',
		'Type'=>'Type',
		//'Statut'=>'Statut',
		'Correcteur'=>'Correcteur'
	);
	
	public $Mail;
	protected $Pass;
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
	 * Attention ! Cette fonction doit être appellée depuis une transaction.
	 * 
	 * @param int $Value la valeur à débiter
	 * @param string $Log le message de log. Si non spécifié, l'enregistrement des logs est à la charge de l'appelant.
	 * @param Exercice $Exercice l'exercice (pour le log)
	 * 
	 * @return bool true si la transaction a réussie
	 */
	public function debit($Value, $Log, Exercice $Exercice = null)
	{
		return $this->_changePoints(-$Value, $Log, $Exercice);
	}
	
	/**
	 * Crédite l'utilisateur de la somme indiquée.
	 * Attention ! Cette fonction doit être appellée depuis une transaction.
	 * 
	 * @param int $Value la valeur à créditer
	 * @param string $Log le message de log.
	 * 
	 * @return bool true si la transaction a réussie
	 */
	public function credit($Value, $Log, Exercice $Exercice = null)
	{
		return $this->_changePoints($Value, $Log, $Exercice);
	}

	/**
	 * Met à jour le solde.
	 * 
	 * @param int $Value entier signé
	 * @param string $Log message à evoyer.
	 * @param Exercice $Exercice l'exercice sur lequel appliquer le log.
	 * @throws Exception la valeur spécifiée n'est pas numérique
	 * @throws Exception l'échange ne se déroule pas dans une transaction
	 * 
	 * @return bool true si succès, false sinon. En cas de false, un ROLLBACK peut avoir été effectué.
	 */
	private function _changePoints($Value, $Log, Exercice $Exercice = null)
	{
		if(!is_int($Value))
		{
			throw new Exception("La valeur doit être numérique", 1);
		}
		if(!Sql::$isTransaction)
		{
			throw new Exception("Débit et crédit dans une transaction !", 2);
		}
		if($Value == 0)
		{
			return false;
		}
		//Enregistrer et logger
		$Signe = ($Value > 0) ? '+':'';
		$this->setAndSave(array('_Points' => 'Points ' . $Signe . $Value));

		if(!$this->log('Logs', $Log, $this, $Exercice, array('Delta'=>$Value)))
		{
			Sql::rollback();
			return false;
		}

		//Les points sont devenus négatifs ? On annule.
		if($this->Points < 0)
		{
			Sql::rollback();
			return false;
		}
		
		return true;
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
	
	/**
	 * Compare le mot de passe fourni avec le mot de passe du membre et renvoie true en cas de succès.
	 * @see Util::hashPass()
	 * 
	 * @param string $Pass le mot de passe à essayer (encrypté !)
	 * 
	 * @return bool true si similaire au mdp du membre
	 */
	public function comparePass($Pass)
	{
		return ($this->Pass == $Pass);
	}
}

Membre::$Props = initProps('Membre');