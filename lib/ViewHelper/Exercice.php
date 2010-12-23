<?php 

function ViewHelper_Exercice(Exercice $Exercice)
{
	
}
/**
 * Renvoie la matière correctement formatée
 * 
 * @param string $Matiere la matière à mettre en forme
 */
function ViewHelper_Exercice_matiere($Matiere)
{
	return '<span class="matiere">' . $Matiere . '</span>';
}

/**
 * Renvoie la classe correctement formatée
 * 
 * @param int $Classe le numéro de classe (e.g. 2)
 * @param string $NomClasse le nom donné à la classe (e.g. "Seconde")
 */
function ViewHelper_Exercice_classe($Classe, $NomClasse)
{
	return '<span class="classe">' . $NomClasse . '</span>';
}