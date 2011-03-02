/**
 * Dernière vérification pour un message
 */
var LastCheck = (new Date()).getTime() / 1000;

/**
 * Informer de nouvelles informations
 */
function checkUpdates()
{
	$.get(
		document.location.toString().replace('/faq/', '/_lastFaq/'),
		function(data)
		{
			if(data > LastCheck)
			{
				$('#faq-nouveaux').show('slow');
				LastCheck = data + 1;
			}
		}
	);
}
$(function(){
	//Vérifier régulièrement l'arrivée de nouvelles infos
	setInterval(checkUpdates, 15000);
});

/**
 * Ajout de formulaire de réponse au clic
 */
$(function(){
	$(".faq-ajout-reponse a").click(function()
	{
		$(this).replaceWith('<form id=form_faq-reponse-exercice method=post action=""><input type=hidden value=' + $(this).data('question-id') + ' name=question /><label for=reponse>Votre réponse :</label><br /><textarea name=reponse id=reponse required=required></textarea><br /><input type=submit value="Ajouter ma réponse" name=faq-reponse-exercice /></form>');
		
		return false;
	});
});