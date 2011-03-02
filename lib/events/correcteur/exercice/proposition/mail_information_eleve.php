<?php
/**
 * Envoyer un mail à l'élève l'informant de la proposition effectuée
 */
External::templateMailFast($Params['Eleve'], '/eleve/exercice/proposition', array(), $Params['Exercice']);