<?php
$Datas = array(
	'hash' => $Params['Exercice']->Hash,
	'titre' => $Params['Exercice']->Titre,
	'question' => $Params['Question']
);

if($Params['Correcteur']->ID != $Params['Membre']->ID)
{
	External::templateMail($Params['Correcteur']->Mail, '/correcteur/faq_question', $Datas);
}

if($Params['Eleve']->ID != $Params['Membre']->ID)
{
	External::templateMail($Params['Eleve']->Mail, '/eleve/faq_question', $Datas);
}
