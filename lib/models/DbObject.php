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
 * Objet en base de données
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
	
	public static $Props;
	
	protected $Foreign = array(
		'Nom' => 'Objet');
	
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
	public static function filterID($ID=null)
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
		return str_replace(array('%TABLE%', '%ID%'), array($Table, $ID), $Query);
	}
	
	/**
	 * Charge un élément à partir de son identifiant.
	 * 
	 * @param int $ID Ce paramètre peut ne pas être numérique. Attention aux injections dans ce cas !
	 * @param bool $FilterID l'id fourni doit-il être filtré ?
	 * 
	 * @return DbObject
	 */
	public static function load($ID,$FilterID=true)
	{
		if($FilterID)
		{
			$ID = static::filterID($ID);
		}
		$Query = static::makeQuery(static::SQL_QUERY, static::TABLE_NAME, $ID);
		return Sql::singleQuery($Query, get_called_class());
	}

	/**
	 * Crée un nouvel élément et renvoie le nouvel objet.
	 * @deprecated cette fonction manque de puissance, puisqu'elle ne permet pas de sélectionner l'identifiant sur lequel effectuer le load (ID par défaut, mais ce n'est pas forcément ce qui est voulu). De plus, elle ne permet pas facilement de gérer des transactions ou des héritages d'objets.
	 * @see Sql::insert() pour les créations d'objet
	 * @see Sql::start() pour les transactions 
	 * @see DbObject::load() pour la récupération après la création
	 * 
	 * @param array $Values les données de l'objet. Les champs non renseignés prendront la valeur par défaut de la table
	 *
	 * @return DbObject l'objet inséré ou null si échec.
	 */
	public static function create(array $Values)
	{	
		if(Sql::insert(static::TABLE_NAME, $Values))
		{
			return call_user_func(array(get_called_class(), 'load'), SQL::lastId());
		}
		else
		{
			return null;
		}
	}
	
	public function getFilteredId()
	{
		return self::filterId($this->ID);
	}
	
	/**
	 * Modifie l'objet et met à jour ses propriétés en base de données.
	 * Remonte méthodiquement l'héritage de l'objet pour trouver sur quelles tables se trouvent quelles propriétés.
	 * 
	 * @param array $Changes
	 * 
	 * @return le résultat de la requête
	 */
	public function setAndSave(array $Changes)
	{
		$Class = get_class($this);
		while($Class!=='DbObject')
		{
			//Récupérer les éléments à updater sur cette table
			$CurrentChanges = array_intersect_key($Changes, $Class::$Props);

			if(count($CurrentChanges)!=0)
			{
				//Il y a des éléments à mettre à jour sur cette table !
				if(!SQL::update($Class::TABLE_NAME, $this->ID, $CurrentChanges))
				{
					Debug::fail('Erreur au setAndSave : ' . Sql::error());
				}
			}
			
			//Remonter d'un cran :
			$Class = get_parent_class($Class);
		}
		
		//Mettre à jour l'objet (clé liées, calculs effectués par SQL...)
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
		else
		{
			return null;
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
	public function log($Table, $Action, Membre $Membre, Exercice $Exercice = null, array $Values = array())
	{
		$Values['Membre'] = $Membre->getFilteredId();
		$Values['Action'] = $Action;
		$Values['_Date'] = 'NOW()';
		
		if(!is_null($Exercice))
		{
			$Values['Exercice'] = self::filterID($Exercice->ID);
		}
		
		return SQL::insert($Table, $Values);
	}
	
	/**
	 * Met à jour l'objet en rechargant ses composants depuis la base de donnée.
	 */
	public function update()
	{
		$Query = static::makeQuery(static::SQL_QUERY, static::TABLE_NAME, $this->getFilteredId());
		$Updated = Sql::singleQuery($Query, get_called_class());
		
		foreach($Updated as $Col=>$Val)
		{
			$this->$Col = $Val;
		}
	}
}

/**
 * Récupère les propriétés définies par une classe pour faciliter l'héritage d'ActiveRecord
 * 
 * @param string $ClassName
 * 
 * @return array les paramètres explicitement déclarés par la classe (sans les paramètres hérités)
 */
function initProps($ClassName)
{
	$Props = getProps($ClassName);
	
	$ParentClass = get_parent_class($ClassName);
	if($ParentClass !==false)
	{
		$PropsParent = getProps($ParentClass);
		$InnerProps = array_diff_assoc($Props, $PropsParent);
	}
	else
	{
		$InnerProps = $Props;
	}
		
	return $InnerProps;
}


function getProps($ClassName)
{
	$R = new ReflectionClass($ClassName);
	$RProps = $R->getProperties();
	$Props = array();
	
	foreach ($RProps as $Prop)
	{
	    $Props[$Prop->getName()]=true;
	    $Props['_' . $Prop->getName()]=true;//pour les requêtes SQL à underscore.
	}
	
	return $Props;
}

DbObject::$Props = initProps('DbObject');