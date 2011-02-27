//Mise en onglets
$(function(){
	$("#tabs").tabs();
	$("#ajout-basique").show();
});

//jQuery multiple file upload :
$(function(){
	$( "#fichiers").MultiFile({
		STRING: {
			remove: 'Supprimer',
			file: '$file',
			denied: 'Ce type de fichier ($ext) n\'est pas autorisé.',
			duplicate: 'Le fichier $file a déjà été ajouté.'
		}
	});
});


/**
 * Gestion d'uploadify.
 */
$(document).ready(function() {
	$('#ressource-upload').uploadify({
		uploader : '/public/js/Uploadify/uploadify.swf',
		buttonText: 'Envoyer un fichier',
		script : '/eleve/exercice/_ressource',
		scriptData : {hash : LongHash, token : Token},
		cancelImg : '/public/css/images/cancel.png',
		folder : '/home',
		fileExt : '*.png;*.jpg;*.gif;*.pdf;*.svg;*.ps',
		fileDesc : 'Fichiers de ressources',
		auto : true,
		onComplete : updateRessources
	});
	
	updateRessources();
});
