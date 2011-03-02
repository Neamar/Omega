<?php
/**
 * Envoyer un mail récapitulant la demande et permettant au membre d'agir s'il n'est pas à l'origine de la transaction.
 */
External::templateMailFast($Params['membre'], '/membre/virement_demande', $Params);