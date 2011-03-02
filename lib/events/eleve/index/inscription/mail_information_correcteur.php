<?php
/**
 * Envoyer un mail avec le lien pour valider le compte
 */
External::templateMail($Params['mail'], '/eleve/compte/en_attente', $Params);	