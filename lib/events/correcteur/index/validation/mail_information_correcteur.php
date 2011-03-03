<?php
/**
 * Envoyer un mail au correcteur l'informant de sa validation
 */
External::templateMailFast($Params['Membre'], '/correcteur/compte/ok');