<?php
/**
 * Envoyer un mail aux correcteurs l'informant qu'une réclamation a été déposée
 */
External::templateMailFast($Params['Correcteur'], '/correcteur/exercice/probleme/reclamation', array(), $Params['Exercice']);