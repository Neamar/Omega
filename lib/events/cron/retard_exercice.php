<?php
//Event : déclenchement du cron
/**
 * Rembourser automatiquement tous les exercices dont :
 * - la date d'expiration est dépassée
 * - le statut est VIERGE, ATTENTE_CORRECTEUR ou ATTENTE_ELEVE.
 */

$Exercices = Sql::query(
	'SELECT Exercices.ID, Exercices.Hash, Exercices.LongHash, Exercices.Statut, Exercices.Titre, Exercices.Createur, Exercices.Correcteur, Exercices.NbRefus, Exercices.Enchere, Modificateur
	FROM Exercices
	WHERE Exercices.Expiration < NOW()
	AND Exercices.Statut = "EN_COURS"
	',
	'ID'
);

$Banque = $Params['Membre'];

//Afin de ne pas alourdir le script, on se contente d'un mysql_fetch_object.
//Notons cependant que l'objet Exercice retourné ne correspond pas forcément aux normes définies dans le reste du code
//La plupart de ses propriétés ne sont pas définies, et Createur ne correspond pas à l'id du membre, mais à son mail.
//En conséquence, la plupart des fonctions sur Exercice ne sont pas correctes ET NE DOIVENT PAS ÊTRE APPELÉES
while($Exercice = mysql_fetch_object($Exercices, 'Exercice'))
{
	
	$Eleve = $Exercice->getEleve();
	$Correcteur = $Exercice->getCorrecteur();
	
	$Remboursement = min(POURCENTAGE_RETARD/100 * $Exercice->pricePaid(), MAX_REMBOURSEMENT * EQUIVALENCE_POINT);

	//Rembourser l'élève
	Sql::start();

	$Exercice->setStatus('REMBOURSE', $Banque, 'Remboursement à ' . POURCENTAGE_RETARD . '% pour retard', array('Remboursement' => POURCENTAGE_RETARD));
	
	$Banque->debit($Remboursement, 'Remboursement pour dépassement de délai', $Exercice);
	$Eleve->credit($Remboursement, 'Délai dépassé', $Exercice);

	Sql::commit();
	
	$Datas = array(
		'exercice' => $Exercice,
		'eleve' => $Eleve,
		'correcteur' => $Exercice->getCorrecteur()
	);
	
	Event::dispatch(Event::CORRECTEUR_EXERCICE_RETARD, $Datas);
	
	//Bloquer le correcteur
	$Correcteur->setAndSave(array('Statut' => 'BLOQUE'));
	
	Event::dispatch(Event::CORRECTEUR_BLOQUE, $Datas);
}