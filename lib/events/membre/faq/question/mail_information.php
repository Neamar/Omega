<?php
$Datas = array(
	'hash' => $Params['Exercice']->Hash,
	'titre' => $Params['Exercice']->Titre,
	'question' => $Params['Question']
);

if($Params['Correcteur']->ID != $Params['Membre']->ID)
{
	External::templateMailFast($Params['Correcteur'], '/correcteur/exercice/faq/question', array(), $Params['Exercice']);
}

if($Params['Eleve']->ID != $Params['Membre']->ID)
{
	External::templateMailFast($Params['Eleve'], '/eleve/exercice/faq/question', array(), $Params['Exercice']);
}
