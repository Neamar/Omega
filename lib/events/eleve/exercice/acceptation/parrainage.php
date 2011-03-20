<?php
/**
 * Si le membre est le filleul de quelqu'un, et qu'il paie son premier exercice, reverser 10% au parrain.
 */

//Est-ce un filleul ?
if(!empty($Params['Eleve']->Parrain))
{
	//Est-ce son premier exercice ?
	if(Sql::singleColumn('SELECT COUNT(*) AS S FROM Exercices_Logs WHERE Membre=' . $Params['Eleve']->getFilteredId() . ' AND NouveauStatut = "EN_COURS"', 'S') == 1)
	{
		$Parrain = $Params['Eleve']->getForeignItem('Parrain');
		
		Sql::start();
		$Affiliation = (int) (0.1 * $Params['Exercice']->pricePaid());
		Membre::getBanque()->debit($Affiliation, 'Paiement parrain', $Params['Exercice']);
		$Parrain->credit($Affiliation, 'Réception des 10% d\'un filleul');
		Sql::commit();
	}
}