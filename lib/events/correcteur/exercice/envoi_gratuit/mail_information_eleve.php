<?php
/**
 * Informer l'élève qu'on lui a envoyé gratuitement son exercice.
 */

External::templateMailFast($Params['Eleve'], '/eleve/exercice/abandon', array(), $Params['Exercice']);