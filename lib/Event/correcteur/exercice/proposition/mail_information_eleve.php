<?php
//Préparer l'envoi du mail à l'élève.
$Exercice = $Params['Exercice'];

$Params['Eleve'] = $Params['Exercice']->getEleve();
$Datas = array(
	'mail' => $Params['Eleve']->Mail,
	'titre' => $Params['Exercice']->Titre,
	'hash' => $Params['Exercice']->Hash,
	'prix' => $Params['Prix'],
);
External::templateMail($Params['Eleve']->Mail, '/eleve/proposition', $Datas);