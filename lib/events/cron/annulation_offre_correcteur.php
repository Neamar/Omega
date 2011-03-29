<?php
//Event : déclenchement du cron
/**
 * Annuler automatiquement toutes les offres faites sur des exercices dont :
 * - le timeout correcteur est dépassé
 * - le statut est ATTENTE_ELEVE.
 */

$Exercices = Sql::query(
	'SELECT Exercices.ID, Exercices.Hash, Exercices.LongHash, Exercices.Statut, Exercices.Titre, Createur, Correcteur, Exercices.NbRefus
	FROM Exercices
	WHERE Exercices.TimeoutCorrecteur < NOW()
	AND Exercices.Statut = "ATTENTE_ELEVE"
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
	//Récupérer le correcteur actuel. À faire maintenant, puisqu'on le supprime après.
	$Correcteur = $Exercice->getCorrecteur();
	
	$Exercice->cancelOffer($Banque, "Refus automatique de l'offre");
	
	//Dispatch de l'évènement REFUS
	Event::dispatch(
		Event::ELEVE_EXERCICE_REFUS,
		array(
			'Exercice' => $Exercice,
			'Correcteur' => $Correcteur
		)
	);
}