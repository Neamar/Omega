<?php
/**
 * Envoyer un mail avec le lien pour valider le compte
 */
$Params['mail_echappe'] = urlencode($Params['mail']);
External::templateMail($Params['mail'], '/eleve/compte/en_attente', $Params);	