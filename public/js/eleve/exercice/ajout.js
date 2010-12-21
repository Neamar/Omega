//Mise en onglets
$(function(){
	$("#tabs").tabs();
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