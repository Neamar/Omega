<?php
/**
 * Envoyer un mail d'informations à l'élève
 */
External::templateMailFast($Params['Eleve'], '/eleve/exercice/correction_disponible', array(), $Params['Exercice']);