<?php
$Params['Correcteur'] = $Params['Exercice']->getCorrecteur();

$Datas = array(
	'hash' => $Params['Exercice']->Hash,
	'titre' => $Params['Exercice']->Titre,
	'nom' => $Params['Correcteur']->identite(),
	'prix' => $Params['Exercice']->priceAsked(),
);

External::templateMail($Params['Correcteur']->Mail, '/correcteur/acceptation', $Datas);
