<?php
/**
 * DbObject.php - 2 déc. 2010
 * 
 * Un objet présent en base de données.
 * Classe de base, abstraite.
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


abstract class DbObject
{
	const TABLE_NAME = 'DbObjects';
	const SQL_QUERY = 'SELECT * FROM %TABLE% WHERE ID=%ID%';
	
	protected $Foreign = array(
		'Nom'=>'Objet');
	
	/**
	 * 
	 * Identifiant de l'élément
	 * @var int
	 */
	public $ID;
	
	/**
	 * Filtre les valeurs incorrectes de l'identifiant pour se prémunir de potentielles injections SQL.
	 * 
	 * @param string $ID l'identifiant à protéger
	 * 
	 * @return int l'id numérique.
	 */
	public static function filterID($ID)
	{
		return intval($ID);
	}
	
	/**
	 * Renvoie la requête de sélection de tuple type ActiveObject.
	 * Considère que tous les paramètres sont protégés.
	 * 
	 * @param string $Table
	 * @param int $ID
	 * 
	 * @return string Une requête SQL.
	 */
	public static function makeQuery($Query, $Table, $ID)
	{
		return str_replace(array('%TABLE%','%ID%'),array($Table,$ID),$Query);
	}
	
	/**
	 * Charge un élément à partir de son identifiant.
	 * 
	 * @param int $ID Ce paramètre peut ne pas être numérique. Attention aux injections dans ce cas !
	 *
	 * @return DbObject
	 */
	public static function load($ID)
	{	
		$Query = static::makeQuery(static::SQL_QUERY, static::TABLE_NAME, self::filterID($ID));
		return Sql::singleQuery($Query, get_called_class());
	}

	/**
	 * Crée un nouvel élément et renvoie le nouvel objet.
	 * 
	 * @param array $Values les données de l'objet. Les champs non renseignés sont 
	 *
	 * @return DbObject l'objet inséré.
	 */
	public static function create(array $Values)
	{	
		if(Sql::insert(static::TABLE_NAME, $Values))
		{
			return call_user_func(array(get_called_class(), 'load'), mysql_insert_id());
		}
		else
		{
			Debug::fail('Impossible de créer l\'objet demandé : ' . mysql_error());
		}
	}
	
	public function getFilteredId()
	{
		return self::filterId($this->ID);
	}
	/**
	 * Modifie l'objet et met à jour ses propriétés en base de données.
	 * 
	 * @param array $Changes
	 * 
	 * @return le résultat de la requête
	 */
	public function setAndSave(array $Changes)
	{
		Sql::update(static::TABLE_NAME, $this->ID, $Changes);
		
		$this->update();
	}
	
	/**
	 * Récupère l'objet associé à la colonne étrangère.
	 * 
	 * @param string Column la colonne à prendre en compte
	 * 
	 * @return DbObject l'objet étranger
	 */
	public function getForeignItem($Column)
	{
		if(isset($this->Foreign[$Column]))
		{
			return call_user_func(array($this->Foreign[$Column], 'load'), $this->$Column);
		}
	}
	
	/**
	 * Enregistre un message de log.
	 * 
	 * @param string Table la table d'enregistrement
	 * @param string Action l'action effectuée entraînant le log
	 * @param Membre Membre le membre ayant effectué l'action
	 * @param Exercice Exercice l'exercice sur lequel s'applique l'action
	 * @param array Values les autres valeurs à insérer (échappées)
	 */
	public function log($Table, $Action, Membre $Membre, Exercice $Exercice, array $Values)
	{
		$Values['Membre'] = $Membre->getFilteredId();
		$Values['Action'] = mysql_real_escape_string($Action);
		$Values['_Date'] = 'NOW()';
		
		if(!is_null($Exercice))
		{
			$Values['Exercice'] = $Exercice->getFilteredId();
		}
		
		if(!SQL::insert($Table, $Values))
		{
			Debug::fail('Log : ' . mysql_error());
		}
	}
	
	/**
	 * Met à jour l'objet en rechargant ses composants depuis la base de donnée.
	 */
	public function update()
	{
		$Query = static::makeQuery(static::SQL_QUERY, static::TABLE_NAME, self::filterID($ID));
		$Updated = Sql::singleQuery($Query, get_called_class());
		
		foreach($Updated as $Col=>$Val)
		{
			$this->$Col = $Val;
		}
	}
}