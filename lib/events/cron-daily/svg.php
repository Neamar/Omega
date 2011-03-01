<?php
if(0 && isset($_SERVER['REQUEST_URI']))
{
	exit('Appel depuis le cron uniquement.');
}

ignore_user_abort(true);
function svgMail($URI)
{
	//Envoyer par mail le résultat
	$boundary = "_".md5(uniqid(rand()));

	$headers = "From: webmaster@edevoir.com \r\n";
	$headers .= "MIME-Version: 1.0\r\nContent-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

	mail(
		'matthieu@bacconnier.fr',
		'Sauvegarde SQL ' . date('d-M-Y'),
		"--". $boundary ."\nContent-Type: text/plain; charset=ISO-8859-1\r\n\nSauvegarde de la base de données.\n\n". "--" .$boundary . "\nContent-Type: application/x-gzip; name=\"dump_work.sql.gz\"\r\nContent-Transfer-Encoding: base64\r\nContent-Disposition: attachment; filename=\"dump_work.sql.gz\"\r\n\n".chunk_split(base64_encode(file_get_contents($URI))) . "--" . $boundary . "--",
		$headers
	);
}


$URI = DATA_PATH . '/logs/' . date('Y-m-d') . '_work.gz';
$Fichier = gzopen($URI, 'w');

//Désactiver les clés le temps de l'importation
gzwrite($Fichier, 'SET foreign_key_checks = 0;' . PHP_EOL);

gzwrite(
	$Fichier,
	"-- ----------------------
-- dump de la base work au " . date("d-M-Y") . "
-- ----------------------\n\n\n"
);

$listeTables = Sql::query("show tables");
$partie = 0;

while($table = mysql_fetch_array($listeTables))
{
	gzwrite(
		$Fichier,
		"-- -----------------------------
-- creation de la table " . $table[0] . "
-- -----------------------------\n"
	);

	$listeCreationsTables = Sql::query("show create table " . $table[0]);
	while($creationTable = mysql_fetch_array($listeCreationsTables))
	{
		gzwrite($Fichier, $creationTable[1] . ";\n\n");
	}

	//Champs à quoter
	$quotes = array("string", "blob", "date", "time", "datetime");
	
	$donnees = Sql::query("SELECT * FROM ".$table[0]);

	gzwrite(
		$Fichier,
		"-- -----------------------------
-- insertions dans la table ".$table[0]."
-- -----------------------------\n"
	);

	while($nuplet = mysql_fetch_array($donnees))
	{
		$tuple = "INSERT INTO " . $table[0] . " VALUES(";
		for($i = 0; $i < mysql_num_fields($donnees); $i++)
		{
			if($i != 0)
			{
				$tuple .=  ", ";
			}
			
			if(in_array(mysql_field_type($donnees, $i), $quotes))
			{
				$tuple .=  "'";
			}
			
			$tuple .= str_replace(array('\'', '\\'), array('\'\'', '\\\\'), $nuplet[$i]);
			
			if(in_array(mysql_field_type($donnees, $i), $quotes))
			{
				$tuple .=  "'";
			}
		}
		$tuple .=  ");\n";

		gzwrite($Fichier, $tuple);
	}
}
gzwrite($Fichier, 'SET foreign_key_checks = 1;' . PHP_EOL);

unset($donnees, $nuplet, $listeCreationsTables, $forbidden, $listeTables);
gzclose($Fichier);

svgMail($URI);
unlink($URI);