<?php
/**
 * Envoyer un mail au membre l'informant de son blocage
 */
External::templateMailFast($Params['Membre'], '/membre/compte/bloque');