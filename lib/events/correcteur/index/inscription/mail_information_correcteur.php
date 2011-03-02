<?php
//Envoyer un mail informant de la création du compte.
External::templateMail($Params['mail'], '/correcteur/compte/en_attente', $Params);