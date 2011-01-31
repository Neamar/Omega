<?php
/**
 * Annuler automatiquement tous les exercices dont :
 * - la date d'annulation est dépassée
 * - le statut est VIERGE, ATTENTE_CORRECTEUR ou ATTENTE_ELEVE.
 */

$Exercices = Sql::query('SELECT Exercices.ID, Exercices.Hash, Exercices.LongHash, Exercices.Statut, Exercices.Titre, Membres.Mail AS Createur, Exercices.NbRefus
	FROM Exercices
	LEFT JOIN Membres ON (Membres.ID = Exercices.Createur)
	WHERE Exercices.TimeoutEleve < NOW()
	AND Exercices.Statut IN ("VIERGE", "ATTENTE_CORRECTEUR", "ATTENTE_ELEVE")
	',
	'ID'
);

$Banque = Membre::getBanque();

//Afin de ne pas alourdir le script, on se contente d'un mysql_fetch_object.
//Notons cependant que l'objet Exercice retourné ne correspond pas forcément aux normes définies dans le reste du code
//La plupart de ses propriétés ne sont pas définies, et Createur ne correspond pas à l'id du membre, mais à son mail.
//En conséquence, la plupart des fonctions sur Exercice ne sont pas correctes ET NE DOIVENT PAS ÊTRE APPELÉES
while($Exercice = mysql_fetch_object($Exercices, 'Exercice'))
{
	$Changes = array(
		'_Correcteur' => 'NULL',
		'_TimeoutCorrecteur' => 'NULL',
		'_InfosCorrecteur' => 'NULL',
		'Enchere' => '0',
		'NbRefus' => min(MAX_REFUS, $Exercice->NbRefus + 1),
	);
			
	$Exercice->setStatus('ANNULE', $Banque, 'Annulation automatique de l\'exercice.', $Changes);
	
	$Datas = array(
		'titre' => $Exercice->Titre,
		'hash' => $Exercice->Hash
	);
	External::templateMail($Exercice->Createur, '/eleve/annulation_automatique', $Datas);
}