<?php
/**
 * Envoyer un mail d'informations à l'élève l'informant que son corrigé est disponible
 */
External::templateMailFast($Params['Eleve'], '/eleve/exercice/correction_disponible', array(), $Params['Exercice']);