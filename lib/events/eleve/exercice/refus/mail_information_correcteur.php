<?php
$Datas = array(
	'hash' => $Params['Exercice']->Hash,
	'titre' => $Params['Exercice']->Titre,
	'nom' => $Params['Correcteur']->Prenom . ' ' . $Params['Correcteur']->Nom,
	'prix' => $Params['Exercice']->Enchere,
);

External::templateMail($Params['Correcteur']->Mail, '/correcteur/refus', $Datas);