/**
 * Gestion dynamique des mathématiques enchâssées et des lightbox
 * 
 * Le tableau récupère du contenu en permanence, qui peut contenir du LaTeX.
 * Il faut donc les mettre en forme.
 */
function updateTex()
{
	var jThis = $(this);
	if(window.MathJax)
	{
		MathJax.Hub.Queue(["Typeset", MathJax.Hub, this]);
	}
	
	jThis.find("a[rel^='prettyPhoto']").prettyPhoto({
		theme: 'light_rounded'
	});
	
	Temps = DELAY / 1000;
	
	if(jThis.find('td[colspan]').length == 1)
	{
		jThis.find('td[colspan]').html('Aucun exercice à afficher.<br /><a href="/correcteur/options/matieres">Définir mes compétences</a>.')
	}
}

var Temps;
/**
 * Chargement de MathJax
 * et initialisation du décompte indiquant le temps restant avant la prochaine màj.
 */
$(function()
{
	//Forcer le chargement de MathJax dont on aura besoin
	if(!window.MathJax)
	{
		loadMathJax();
	}
	
	Delai = $('#prochaine-maj');
	Temps = DELAY / 1000;
	setInterval(function(){Delai.text(Temps--);}, 1000);
	
	$('#maj-now').click(function(e)
	{
		$('table.ajax-table').data('timer').call();
		e.preventDefault();
	});
});