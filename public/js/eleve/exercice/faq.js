//Ajout de formulaire de réponse au clic
$(function(){
	$(".faq-ajout-reponse a").click(function()
	{
		$(this).replaceWith('<form id=form_faq-reponse-exercice method=post action=""><input type=hidden value=' + $(this).data('question-id') + ' name=question /><label for=reponse>Votre réponse :</label><br /><textarea name=reponse id=reponse></textarea><br /><input type=submit value="Ajouter ma réponse" name=faq-reponse-exercice /></form>');
		
		return false;
	});
});