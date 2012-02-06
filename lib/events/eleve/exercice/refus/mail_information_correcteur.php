<?php
/**
 * Envoyer un mail informant le correcteur que son offre a été refusée.
 */
External::templateMailFast($Params['Correcteur'], '/correcteur/exercice/offre_refusee', array(, $Params['Exercice']);
