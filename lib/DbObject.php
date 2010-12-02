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
	const OBJECT_NAME = 'DbObject';
	
	protected $Foreign = array(
		'Nom'=>'Objet');
	
	public $ID;
	/**
	 * Charge un élément à partir de son identifiant.
	 * 
	 * @param int $ID
	 *
	 * @return DbObject
	 */
	public static function load($ID)
	{	
		return SQL::singleQuery('SELECT * FROM ' . static::TABLE_NAME . ' WHERE ID=' . intval($ID), static::OBJECT_NAME);
	}

	/**
	 * Modifie l'objet et met à jour ses propriétés en base de données.
	 * 
	 * @param array $Changes
	 */
	public function setAndSave(array $Changes)
	{
		foreach($Changes as $Key=>$Value)
		{
			$this->$Key = $Value;
		}
		
		SQL::update(static::TABLE_NAME, $this->ID, $Changes);
	}
	
	public function getForeignItem($Column)
	{
		if(isset($this->Foreign[$Column]))
		{
			return call_user_func(array($this->Foreign[$Column], 'load'), $this->$Column);
		}
	}
}