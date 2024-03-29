<?php
/**
 * Sql.php - 10 nov. 2010
 *
 * Offre une interface centralisée d'accès à la BDD, pour pouvoir changer facilement de méthode d'accès ou de système de BDD.
 *
 * PHP Version 5
 *
 * @category  Db
 * @package   Root
 * @author    Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @copyright 2010 Matthieu Bacconnier
 * @license   Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link      http://edevoir.com
 */

/**
 * Classe statique pour la connexion à la base de données.
 *
 * @category Db
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 *
 */
class Sql
{
	public static $isTransaction = false;
	
	/**
	 * Ouvrir une connexion à la base de données.
	 */
	public static function connect()
	{
		mysql_connect('localhost', 'eDevoir', 'sarl_omega');
		mysql_select_db(SQL_DB);
		mysql_set_charset('utf8');
	}

	/**
	 * Ferme la connexion. Normalement implicite et non appelé par les scripts.
	 */
	public static function disconnect()
	{
		mysql_close();
	}
	
	/**
	 * Échappe le (les) paramètres fournis.
	 * Non récursif.
	 * 
	 * @param mixed $Data
	 * 
	 * @return mixed les données échappées
	 */
	public static function escape($Data)
	{
		if(is_array($Data))
		{
			$Data = array_map('mysql_real_escape_string', $Data);
			$Data = array_map('Sql::escapeHtml', $Data);
		}
		else
		{
			$Data = mysql_real_escape_string($Data);
			$Data = Sql::escapeHtml($Data);
		}
		
		return $Data;
	}
	
	/**
	 * Échappe les entités HTML.
	 * N'utilise par htmlspecialchars qui échappe aussi le &, empêchant d'insérer un espace insécable.
	 * 
	 * @param string $Data
	 * 
	 * @return string $Data les données encodées
	 */
	public static function escapeHtml($Data)
	{
		return str_replace(array('<', '>'), array('&lt;', '&gt;'), $Data);
	}
	
	/**
	 * Renvoie le texte de la dernière erreur SQL.
	 * 
	 * @return string l'erreur
	 */
	public static function error()
	{
		return mysql_error();
	}
	
	/**
	 * Renvoie le numéro du dernier identifiant inséré
	 * 
	 * @returun int le dernier ID
	 */
	public static function lastId()
	{
		return mysql_insert_id();
	}
	
	/**
	 * Renvoie le nombre de tuples affectés par la dernière requête
	 * 
	 * @return int
	 */
	public static function affectedRows()
	{
		return mysql_affected_rows();
	}
	
	/**
	 * Vérifie l'intégrité des contraintes relationnelles du site.
	 * Automatiquement appelé au COMMIT pour simuler les CONSTRAINT CHECK non disponibles avec MySQL.
	 */
	public static function constraint()
	{
		//Vérifier que de l'argent n'est pas apparu de nulle part.
		//À noter : UNION fonctionne en pur SQL. Autrement dit, deux tuples identiques sont fusionnés. D'où l'utilisation d'un alias Type pour éviter les mauvais problèmes
		/**
		 * Premier jeu de contraintes.
		 * La somme des points dans les comptes membres doit correspondre à la somme des différences de transactions effectuées.
		 * 
		 * @var int
		 */
		$Points = Sql::singleColumn(
			'SELECT SUM(Points) AS Points
			FROM
			(
				SELECT SUM(Points) AS Points, "P" AS Type FROM Membres
				UNION
				SELECT -SUM(Delta) AS Points, "L" AS Type FROM Logs
			) V',
			'Points'
		);
		
		/**
		 * Second jeu de contraintes.
		 * La sommes des entrées doit correspondre à la somme des points en circulation moins la somme des points sortis.
		 * 
		 * @var int
		 */
		$Argent = Sql::singleColumn(
			'SELECT SUM(Montant) AS Montant
			FROM
			(
				SELECT SUM(Montant) AS Montant, "E" AS Type FROM Entrees
				UNION
				SELECT -SUM(Montant) AS Montant, "S" AS Type FROM Virements
				UNION
				SELECT -SUM(Points) AS Montant, "I" AS Type FROM Membres
			) V',
			'Montant'
		);
		
		return ($Points == 0 && $Argent == 0);
	}
	
	/**
	 * Débute une transaction.
	 * 
	 * @return SQLResource
	 */
	public static function start()
	{
		self::$isTransaction = true;
		return self::query('START TRANSACTION');
	}
	
	/**
	 * Enregistre les modifications de la transaction.
	 * 
	 * @return SQLResource
	 */
	public static function commit()
	{	
		if(self::constraint())
		{
			self::$isTransaction = false;
			return self::query('COMMIT');
		}
		else
		{
			self::rollback();
			lock('Problème de cohérence relationnelle.');
		}
	}
	
	/**
	 * Annule les modifications de la transaction.
	 * 
	 * @return SQLResource
	 */
	public static function rollback()
	{
		self::$isTransaction = false;
		return self::query('ROLLBACK');
	}

	/**
	 * Exécute une requête sur la base
	 *
	 * @param string $Query la requête à effectuer
	 *
	 * @return SQLResource le résultat de la requête.
	 */
	public static function query($Query)
	{
		$R = mysql_query($Query);
		if($R===false)
		{
			throw new Exception('Erreur SQL : ' . mysql_error());
		}
		return $R;
	}

	/**
	 * Exécute une requête sur la base. En cas d'erreur, le script n'est pas interrompu et la fonction appelante peut traiter l'exception.
	 * 
	 * @param string $Query la requête à effectuer
	 * 
	 * @return SQLResource le résultat de la requête.
	 */
	public static function queryNoFail($Query)
	{
		return mysql_query($Query);
	}

	/**
	 * Exécute une requête sur la base et ne renvoie que le premier résultat
	 * 
	 * @param string $Query la requête à effectuer
	 * @param string $Type le type de l'objet de retour. Si null (par défaut), on renvoie un tableau.
	 * 
	 * @return (Object|array) le premier résultat de la requête. Si aucun résultat, la fonction renvoie null.
	 */
	public static function singleQuery($Query, $Type=null)
	{
		$R = self::query($Query);
		if(mysql_num_rows($R)==0)
		{
			return null;
		}
		if(is_null($Type))
		{
			return mysql_fetch_assoc($R);
		}
		else
		{
			return mysql_fetch_object($R, $Type);
		}
	}
	
	/**
	 * Récupère une unique colonne du premier tuple résultat.
	 * 
	 * @param string $Query la requête à effectuer
	 * @param string $Column la colonne à renvoyer
	 */
	public static function singleColumn($Query, $Column)
	{
		$Resultat = self::singleQuery($Query);
		return $Resultat[$Column];
	}
	
	/**
	 * Exécute une requête sur la base et renvoie le résultat dans un tableau associatif clé => valeur
	 *
	 * @param string $Query la requête à effectuer
	 * @param string $KeyColumn la colonne qui doit servir de clé
	 * @param string $ValueColumn la colonne qui doit servir de valeur. Si non spécifiée, l'intégralité du tuple (moins la clé) sert de valeur.
	 *
	 * @return array le résultat de la requête.
	 */
	public static function queryAssoc($Query, $KeyColumn, $ValueColumn = null)
	{
		$RSql = self::query($Query);
		$R = array();
		while($RTuple = mysql_fetch_assoc($RSql))
		{
			if(is_null($ValueColumn))
			{
				$Key = $RTuple[$KeyColumn];
				unset($RTuple[$KeyColumn]);
				$R[$Key] = $RTuple;
			}
			else
			{
				$R[$RTuple[$KeyColumn]] = $RTuple[$ValueColumn];
			}
		}
		
		return $R;
	}

	/**
	 * Insère un tuple dans une table de la base de données.
	 * En cas d'erreurs (duplicate), l'erreur n'est pas traitée et est renvoyée à l'appelant pour gestion.
	 * NOTE: Les clés du tableau Datas commençant par un "_" indiquent que la valeur associée ne doit pas être échappée. Le "_" est ensuite supprimé lors de l'update sur la table. Voir le deuxième exemple.
	 * 
	 * @param string $Table la table dans laquelle insérer les données.
	 * @param array $Datas un tableau associatif sous la forme clé=>valeur dans la table. Les valeurs seront échappées ! Elle n'ont cependant pas à être quotées, des guillemets seront ajoutés sauf si la clé commence par un _ (cf. note).
	 * 
	 * @return SQLResource le résultat de la requête.
	 * 
	 * @example
	 *	$ToInsert = array('Reference'=>$ArticleID,'URL' => 'http://neamar.fr');
	 *	SQL::insert('More',$ToInsert);
	 */
	public static function insert($Table, array $Valeurs)
	{
		//On ne peut pas simplement utiliser array_keys, car on peut avoir à modifier les clés (règle de l'underscore)
		$Keys = array();
		foreach($Valeurs as $K => &$V)
		{
			if($K[0] == '_')
			{
				$Keys[] = substr($K, 1);
			}
			else
			{
				$Keys[] = $K;
				$V = '"' . self::escape($V) . '"';
			}
		}

		return self::queryNoFail(
			'INSERT INTO ' . $Table . '(' . implode(',', $Keys) . ')
			VALUES (' . implode(',', $Valeurs) . ')'
		);
	}

	/**
	 * Met à jour un tuple dans une table de la base de données selon l'identifiant spécifié.
	 * En cas d'erreurs, l'erreur n'est pas traitée et est renvoyée à l'appelant pour gestion.
	 * NOTE: Les clés du tableau Datas commençant par un "_" indiquent que la valeur associée ne doit pas être échappée. Le "_" est ensuite supprimé lors de l'update sur la table. Voir le deuxième exemple.
	 * 
	 * @param string Table la table dans laquelle insérer les données.
	 * @param int ID l'identifiant du tuple à mettre à jour. Forcément l'ID.
	 * @param array Datas un tableau associatif sous la forme clé=>valeur dans la table. Les valeurs doivent être échappées ! Elle n'ont cependant pas à être quotées, des guillemets seront ajoutés sauf si la clé commence par un _ (cf. note).
	 * @param string And des contraintes supplémentaires permettant de valider la mise à jour (exemple : "AND Auteur.ID=2" pour empêcher la modification de n'importe quoi)
	 * @param int Limit nombre maximal d'enregistrements à modifier
	 * 
	 * @return SQLResource le résultat de la requête.
	 * 
	 * @example
	 * //Notez la réalisation de la proposition si nécessaire :
	 *	if(is_numeric($_POST['proposition']))
	 *		SQL::update('Propositions',$_POST['proposition'],array('OmniID'=>mysql_insert_id()),'AND ReservePar=' . AUTHOR_ID);
	 *
	 * @example
	 * //Explicitation du _
	 *
	 * //Incorrect : le now() sera updaté sous la forme "NOW()" (guillemets compris, ce qui sera invalidé car la chaîne de caractères "NOW()" n'est pas de type DATE.
	 * SQL::update('Propositions',$_POST['proposition'],array('Date' => 'NOW()');
	 *
	 * //Correct : pour indiquer qu'il s'agit d'un appel à une fonction / expression, précédez votre clé d'un _ :
	 * SQL::update('Propositions',$_POST['proposition'],array('_Date' => 'NOW()');
	 * 
	 * @example
	 * //Explicitation du $And
	 * //Petit "hack" pour mettre à jour un tuple dont on ne connaît pas l'ID :
	 * SQL::update('Propositions',-1,array('Titre' => 'Lol'),'OR ID=(SELECT MAX(ID) FROM Propositions)'
	 */
	public static function update($Table, $ID, array $Valeurs, $And='', $Limit = 1)
	{
		$Set=array();
		foreach($Valeurs as $K=>$V)
		{
			if($K[0]=='_')
			{
				$Set[] = substr($K, 1) . '=' . $V . '';
			}
			else
			{
				$Set[] = $K . '="' . self::escape($V) . '"';
			}
		}

		return self::queryNoFail(
			'UPDATE ' . $Table . ' SET ' . implode(',', $Set) . '
			WHERE ID=' . intval($ID) . ' ' . $And . ' LIMIT ' . $Limit
		);
	}

	/**
	 * Supprime un tuple basé sur les contraintes spécifiées.
	 * Attention ! Dans notre cas, l'utilisateur SQL n'a pas tous les droits et ne peut supprimer que sur certaines tables
	 * 
	 * @param Table:String la table dans laquelle supprimer les données.
	 * @param ID:int l'identifiant du tuple à détruire. Forcément l'ID.
	 * @param $And:String des contraintes supplémentaires permettant de valider la mise à jour (exemple : "AND Auteur.ID=2" pour empêcher la modification de n'importe quoi)
	 * 
	 * @return :SQLResource le résultat de la requête.
	 */
	public static function delete($Table, $ID, $And)
	{
		SQL::delete('DELETE FROM ' . $Table . ' WHERE ID = ' . intval($ID) . ' ' . $And . ' LIMIT 1');
	}
	
	/**
	 * Retourne la date indiquée par le timestamp au format SQL.
	 * 
	 * @param int $time
	 * 
	 * @return string une chaîne formatée
	 */
	public static function getDate($time = -1)
	{
		if($time==-1)
		{
			$time = time();
		}
			
		return date("Y-m-d H:i:s", $time);
	}
}