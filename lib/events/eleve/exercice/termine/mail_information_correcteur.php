<?php
/**
 * Envoyer un mail au correcteur l'informant de son paiement
 */
External::templateMailFast($Params['Correcteur'], '/correcteur/exercice/paiement', array('message' => $Params['Message']), $Params['Exercice']);