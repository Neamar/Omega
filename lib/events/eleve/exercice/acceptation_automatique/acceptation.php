<?php 
/**
 * L'acceptation automatique n'étant qu'un surensemble de l'acceptation, il faut aussi dispatcher ACCEPTATION
 */
Event::dispatch(
	Event::ELEVE_EXERCICE_ACCEPTATION,
	$Params
);