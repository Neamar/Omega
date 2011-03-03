<?php
/**
 * Envoyer un mail à l'élève l'informant du retard :(
 */
External::templateMailFast($Params['Eleve'], '/eleve/exercice/retard', array('montant' => $Params['Montant']), $Params['Exercice']);