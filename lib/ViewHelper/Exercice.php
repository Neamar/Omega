<?php 

/**
 * Affiche les composantes de l'exercice
 * 
 * @param Exercice $Exercice
 * @param string $Tab l'onglet ouvert par défaut
 */
function ViewHelper_Exercice(Exercice $Exercice, $Tab = 'Sujet')
{
	$Tabs = array('Sujet', 'Corrige', 'Réclamation');
	$Files = $Exercice->getFiles();
	$Liens = array(
		'SUJET' => array(),
		'CORRIGE' => array(),
		'RECLAMATION' => array()
	);
	foreach ($Files as $URL => $Infos)
	{
		$Liens[$Infos['Type']][$URL] = $Infos;
	}
	
	$Disabled = array();
	$Types = array_keys($Liens);
	foreach($Types as $ID => $Nom)
	{
		if(count($Liens[$Nom]) == 0)
		{
			$Disabled[] = $ID;
		}
	}
	
	$R = '
	<div class="exercice" id="exercice-' . $Exercice->Hash . '" >
		<ul>
			<li><a href="#sujet-' . $Exercice->Hash . '">Sujet</a></li>
			<li><a href="#corrige-' . $Exercice->Hash . '">Corrigé</a></li>
			<li><a href="#reclamation-' . $Exercice->Hash . '">Réclamation</a></li>
		</ul>
		
		<div id="sujet-' . $Exercice->Hash . '">
		<p>Sujet</p>
		</div>
		<div id="corrige-' . $Exercice->Hash . '">
		<p>Corrige</p>
		</div>
		<div id="reclamation-' . $Exercice->Hash . '">
		<p>Reclamation</p>
		</div>
	</div>
	
	<script type="text/javascript">
	$(function()
	{
		$("#exercice-' . $Exercice->Hash . '").tabs({
			disabled: [' . implode(',',$Disabled) . '],
		});
	});
	</script>
	';
	$Disabled = array();
	
	return $R;
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