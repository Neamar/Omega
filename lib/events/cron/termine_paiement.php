<?php
//Event : déclenchement du cron
/**
 * Clore automatiquement l'exercice si :
 * - Expiration + DELAI_REMBOURSEMENT jours sont passés 
 * - le statut est ENVOYÉ
 */

$Exercices = Sql::query(
	'SELECT Exercices.ID, Exercices.Hash, Exercices.LongHash, Exercices.Statut, Exercices.Titre, Exercices.Createur, Exercices.Enchere, Exercices.Correcteur, Exercices.NbRefus
	FROM Exercices
	WHERE Exercices.Expiration < DATE_SUB(NOW(), INTERVAL ' . DELAI_REMBOURSEMENT . ' DAY)
	AND Exercices.Statut = "ENVOYE"
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
	//Récupérer le correcteur actuel.
	$Correcteur = $Exercice->getCorrecteur();
	
	//Commencer une transaction pour l'échange
	$Exercice->closeExercice('Cloture automatique (expiration du délai)', $Banque);
	Event::dispatch(Event::ELEVE_EXERCICE_TERMINE, array('Exercice' => $Exercice));
}
?>