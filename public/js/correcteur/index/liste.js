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
}

$(function()
{
	//Forcer le chargement de MathJax dont on aura besoin
	if(!window.MathJax)
	{
		loadMathJax();
	}
});