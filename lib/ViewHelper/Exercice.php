<?php 
/**
 * Exercice.php - 23 déc. 2010
 *
 * Offrir des primitives de haut niveau pour l'affichage des Exercices.
 *
 * PHP Version 5
 *
 * @category  ViewHelper
 * @package   Root
 * @author    Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @copyright 2010 Matthieu Bacconnier
 * @license   Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link      http://edevoir.com
 */

//Ces fonctions nécessitent le chargement de l'aide de vue pour les dates.
if(!function_exists("ViewHelper_Date_countdown"))
{
	include OO2FS::viewHelperPath('Date');
}
//Ces fonctions nécessitent le chargement de l'aide de vue pour les listes.
if(!function_exists("ViewHelper_Html_list"))
{
	include OO2FS::viewHelperPath('Html');
}

/**
 * Affiche les composantes de l'exercice
 * 
 * @param Exercice $Exercice
 * @param string $Tab l'onglet ouvert par défaut
 */
function ViewHelper_exercice(Exercice $Exercice, $Tab = 'Sujet')
{
	$Tabs = array('Sujet', 'Corrige', 'Réclamation');
	$Liens = $Exercice->getSortedFiles();
	//Ajouter l'onglet "Informations"
	$Liens = array('INFO'=>array(true)) + $Liens;
	
	$Disabled = array();
	$Content = array();
	
	//Boucler sur tous les types.
	//Passer par array_key pour pouvoir récupérer l'identifiant du tableau.
	$Types = array_keys($Liens);
	foreach($Types as $ID => $Nom)
	{
		if(count($Liens[$Nom]) == 0)
		{
			$Disabled[] = $ID;
		}
		else
		{
			if(!isset($Liens[$Nom]))
			{
				$Content[$Nom] = '';
			}
			else
			{
				$Thumbs = array();
				foreach($Liens[$Nom] as $URL => $Infos)
				{
					$Thumbs[$Infos['ThumbURL']] = array(
						'URL' => $URL,
						'alt' => $Exercice->Titre . ' ' . $Nom . ', fichier ' . $Infos['NomUpload']
					);
				}
				$Content[$Nom] = ViewHelper_Exercice_thumbs($Thumbs, $Exercice->LongHash);
			}
		}
	}
	//Mettre en forme les remarques :
	$Remarques = '';
	if(!empty($Exercice->InfosEleve))
	{
		$Remarques .= '
		<p>Informations complémentaires élèves :</p>
		<p class="infos">
			' . $Exercice->InfosEleve . '
		</p>';
	}
	if(!empty($Exercice->InfosCorrecteur))
	{
		$Remarques .= '
		<p>Informations complémentaires correcteur :</p>
		<p class="infos">
			' . $Exercice->InfosCorrecteur . '
		</p>';
	}
	elseif(!empty($Exercice->InfosReclamation))
	{
		$Remarques .= '
		<p>Informations complémentaires réclamation :</p>
		<p class="infos">
			' . $Exercice->InfosReclamation . '
		</p>';
	}
	//Récupérer les infos intéressantes.
	$Infos = ViewHeper_Exercice_props($Exercice);
	
	
	//Renvoyer tout ça.
	$R = '
	<div class="exercice" id="exercice-' . $Exercice->Hash . '" >
		<ul>
			<li><a href="#infos-' . $Exercice->Hash . '">Informations</a></li>
			<li><a href="#sujet-' . $Exercice->Hash . '">Sujet</a></li>
			<li><a href="#corrige-' . $Exercice->Hash . '">Corrigé</a></li>
			<li><a href="#reclamation-' . $Exercice->Hash . '">Réclamation</a></li>
		</ul>
		<div class="exercice-tab exercice-infos" id="infos-' . $Exercice->Hash . '">
		<h2><a href="/' . $_GET['module'] . '/exercice/index/' . $Exercice->Hash . '">' . $Exercice->Titre . '</a></h2>
		' . ViewHelper_Html_list($Infos) . '
		
		' . $Remarques . '
		<p><a href="/' . $_GET['module'] . '/exercice/zip/' . $Exercice->Hash . '">Télécharger dans un zip</a></p>
		</div>
		<div class="exercice-tab exercice-sujet" id="sujet-' . $Exercice->Hash . '">
		<p>Fichiers composant <a href="/eleve/exercice/sujet/' . $Exercice->Hash . '">le sujet</a> :</p>
		<p>' . (isset($Content['SUJET'])?$Content['SUJET']:'Sujet non disponible.') . '</p>
		</div>
		<div class="exercice-tab exercice-corrige" id="corrige-' . $Exercice->Hash . '">
		<p>Fichiers composant <a href="/eleve/exercice/corrige/' . $Exercice->Hash . '">le corrigé</a> :</p>
		<p>' . (isset($Content['CORRIGE'])?$Content['CORRIGE']:'Corrigé non disponible.') . '</p>
		</div>
		<div class="exercice-tab exercice-reclamation" id="reclamation-' . $Exercice->Hash . '">
		<p>' . (isset($Content['RECLAMATION'])?$Content['RECLAMATION']:'Réclamation non disponible.') . '</p>
		</div>
	</div>
	
	<script type="text/javascript">
	$(function()
	{
		$("#exercice-' . $Exercice->Hash . '").tabs({
			disabled: [' . implode(',', $Disabled) . '],
		});
	});
	</script>
	';
	$Disabled = array();
	
	return $R;
}

/**
 * Renvoie toutes les propriétés intéressantes d'un exercice.
 * 
 * @param Exercice $Exercice l'exercice à prendre en compte
 * 
 * @return array un tableau à afficher.
 */
function ViewHeper_Exercice_props(Exercice $Exercice)
{
	$Infos = array();
	
	$Infos[] = 'Matière : ' . ViewHelper_Exercice_matiere($Exercice->Matiere);
	$Infos[] = 'Niveau scolaire : ' . ViewHelper_Exercice_classe($Exercice->DetailsClasse, $Exercice->Section);
	$Infos[] = 'Type de l\'exercice : <span class="type" title="' . $Exercice->Type . '">' . $Exercice->DetailsType . '</span> (<span class="demande" title="' . $Exercice->Demande . '">' . $Exercice->DetailsDemande . '</span>)';
	$Infos[] = 'Statut : <span title="' . $Exercice->Statut . '">' . $Exercice->DetailsStatut . '</span>';
	
	$Dates = ViewHelper_Exercice_date($Exercice);
	
	if(count($Dates)>1)
	{
		$Infos[] = "Dates :\n" . ViewHelper_Html_list($Dates);
	}
	else
	{
		$Infos[] = $Dates[0];
	}
	
	return $Infos;
}

/**
 * Renvoie les dates intéressantes de l'exercice.
 * Le critère "intéressant" dépendant de l'état de l'exercice.
 * 
 * @param Exercice $Exercice l'exercice à prendre en compte
 * 
 * @return array un tableau de dates intéressantes
 */
function ViewHelper_Exercice_date(Exercice $Exercice)
{
	$Dates = array();
	
	$Dates[] = 'Expiration : ' . ViewHelper_Date_countdown($Exercice->Expiration);
	if($Exercice->isCancellable())
	{
		$Dates[] = 'Annulation automatique élève : ' . ViewHelper_Date_countdown($Exercice->TimeoutEleve);
	}
	if(!is_null($Exercice->TimeoutCorrecteur) && $Exercice->Statut == 'ATTENTE_ELEVE')
	{
		$Dates[] = 'Annulation automatique correcteur : ' . ViewHelper_Date_countdown($Exercice->TimeoutCorrecteur);
	}
	
	return $Dates;
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
 * @param string $DetailsClasse le nom donné à la classe (e.g. "Seconde")
 * @param string $Section la section (e.g. "S")
 */
function ViewHelper_Exercice_classe($DetailsClasse, $Section = '')
{
	if($Section != '')
	{
		$Section = ' <small>(' . $Section . ')</small>';
	}
	return '<span class="classe">' . $DetailsClasse . $Section . '</span>';
}

/**
 * Retourne la liste des miniatures passées en paramètre.
 * 
 * @param array $Thumbs, les clés sont les URLs de la miniature, les valeurs le contenu alternatif (ou un tableau URL =>, alt => pour un lightbox)
 * @param string $LongHash le hash de l'exercice.
 * 
 * @return string les images.
 */
function ViewHelper_Exercice_thumbs(array $Thumbs, $LongHash)
{
	$R = '';
	$Hash = substr($LongHash, 0, HASH_LENGTH);
	$BaseURL = '/public/exercices/' . $LongHash;
	
	$Images = array('jpg', 'jpeg', 'png', 'gif');
	$Documents = array('pdf', 'doc', 'docx', 'odt');

	foreach($Thumbs as $URL => $Datas)
	{
		if(is_array($Datas))
		{
			$Extension = Util::extension($Datas['URL']);
			if(in_array($Extension, $Images))
			{
				$Fichier = $BaseURL . $Datas['URL'];
			}
			elseif(in_array($Extension, $Documents))
			{
				$FichierURL = URL . $BaseURL . $Datas['URL'];
				$Fichier = 'http://docs.google.com/viewer?embedded=true&url=' . urlencode($FichierURL) . '&iframe=true&width=90%&height=90%';
			}
			else
			{
				$Fichier = $BaseURL . $Datas['URL'];
			}
			
			$R .= '	<a href="' . $Fichier . '" rel="prettyPhoto[' . $Hash . ']"><img src="' . $BaseURL . $URL . '" alt="' . $Datas['alt'] . '" /></a>';
		}
		else
		{
			$R .= ' <img src="' . $BaseURL . $URL . '" alt="' . $Datas . '" />';
		}
	}
	
	return $R;
}