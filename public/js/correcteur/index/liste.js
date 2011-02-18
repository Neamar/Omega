/**
 * Gestion dynamique des mathématiques enchâssées
 * 
 * Le tableau récupère du contenu en permanence, qui peut contenir du LaTeX.
 * Il faut donc les mettre en forme.
 */
function updateTex()
{
	if(window.MathJax)
	{
		MathJax.Hub.Queue(["Typeset", MathJax.Hub, this]);
	}
	
	Temps = DELAY / 1000;
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