<?php
/**
 * Envoyer un mail au correcteur l'informant qu'il a eu un retard. Pas bien !
 */
External::templateMailFast($Params['Correcteur'], '/correcteur/exercice/probleme/retard', array(), $Params['Exercice']);