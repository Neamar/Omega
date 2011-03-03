<?php
/**
 * Envoyer un mail au correcteur l'informant que son offre a été validée
 */
External::templateMailFast($Params['Correcteur'], '/correcteur/exercice/offre_acceptee', array(), $Params['Exercice']);
