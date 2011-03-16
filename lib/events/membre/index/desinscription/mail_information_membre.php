<?php
/**
 * Envoyer un mail de confirmation au membre
 */
External::templateMailFast($Params['Membre'], '/membre/compte/desinscription');