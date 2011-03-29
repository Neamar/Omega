<?php
/**
 * Envoyer un mail aux correcteurs potentiellement intéressés
 */
$Destinataires = Sql::queryAssoc(
	'SELECT Membres.ID, Membres.Mail, Correcteurs.Prenom, Correcteurs.Nom
	FROM Correcteurs_Capacites Capa
	JOIN Membres ON (Capa.Correcteur = Membres.ID)
	JOIN Correcteurs ON (Capa.Correcteur = Correcteurs.ID)
	WHERE Matiere="' . Sql::escape($Params['Exercice']->Matiere) . '"
	AND ' . intval($Params['Exercice']->Classe) . ' BETWEEN Capa.Finit AND Capa.Commence',
	'ID'
);

foreach($Destinataires as $Destinataire)
{
	$Datas = array(
		'nom' => $Destinataire['Prenom'] . ' ' . $Destinataire['Nom'],
		'hash' => $Params['Exercice']->Hash,
		'titre' => $Params['Exercice']->Titre,
		'matiere' => $Params['Exercice']->Matiere,
		'niveau' => $Params['Exercice']->DetailsClasse
	);
	
	External::templateMail($Destinataire['Mail'], '/correcteur/exercice/exercice_poste', $Datas);
}