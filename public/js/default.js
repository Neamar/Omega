/**
 * Script javascript par défaut.
 * 
 * Charge les bonnes librairies si nécessaires, effectue les différentes actions.
 * Voir les commentaires pour les détails.
 * 
 * @author eDevoir <webmaster@edevoir.com>
 */


/**
 * Gestion des dates via la librairie prettyDate tweakée.
 * 
 * @see http://ejohn.org/blog/javascript-pretty-date/
 */
function dynamicDate()
{
	var Times = $('time.date');
	if(Times.length > 0)
	{
		$.getScript('/public/js/jquery-prettydate.js', function(){
			Times.prettyDate();
		});
	}
}
/**
 * Gestion des dates dynamiques.
 * 
 * Encapsulées dans des balises <time class="date" />,
 * ces informations sont mises en forme pour faire un compte à rebours négatif
 */
$(dynamicDate);

/**
 * Délai (ms) entre deux mise à jour de tableaux dynamiques
 * 
 * @var int
 */
var DELAY = 60000;

/**
 * Gestion des tableaux Ajax
 * 
 * Ces tableaux récupèrent leur contenu régulièrement du site pour être
 * en permanence à jour.
 * 
 * Les données sont récupérées en JSON.
 */
$(function()
{
	var Tables = $('table.ajax-table');
	
	if(Tables.length > 0)
	{
		Tables.each(function(){
			var Table = $(this);
			function update()
			{
				$.ajax({
					url: Table.data('source'),
					success: function(data){
						data = jQuery.parseJSON(data);
						Lignes = '';
						for(var i = 0 ; i < data.length ; i++)
						{
							Lignes += '<tr>';
							
							for(var j = 0 ; j < data[i].length ; j++)
							{
								Lignes += '<td>' + data[i][j] + '</td>';
							}
							Lignes += "</tr>\n";
						}
						
						Table.find('tbody').html(Lignes);
						
						if(Table.data('callback'))
						{
							eval(Table.data('callback') + '.call(Table[0])');
						}
					}
				});
			}
			
			update();
			setInterval(update, 60000);
			
			Table.data('timer', update);
		});
	}
});


/**
 * Charge la librairie mathjax sur la page actuelle et la configure
 */
function loadMathJax()
{
	var script = document.createElement("script");
	script.type = "text/javascript";
	script.src = "/public/js/MathJax/MathJax.js";

	var config = 'MathJax.Hub.Config({ config: "MathJax.js" }); ' +
				'MathJax.Hub.Startup.onload();';

	if (window.opera)
	{
		script.innerHTML = config;
	}
	else
	{
		script.text = config;
	}

	document.getElementsByTagName("head")[0].appendChild(script);
}

/**
 * Gestion des mathématiques
 * 
 * Certains composants du site peuvent avoir besoin d'afficher des mathématiques dans le HTML.
 * Pour cela, on utilise la libraire MathJax.
 * 
 * Cette librairie étant assez lourde, elle n'est chargée que si la page le demande explicitement
 * en incluant dans le HTML une balise avec class="texable", pour "tex-able".
 */
$(function()
{
	if($('.texable').length > 0)
	{
		loadMathJax();
	}
});

/**
 * Gestion des quickactions
 * Il s'agit d'une liste déroulante dont les valeurs sont des URLs.
 * Un simple clic sur une ligne amène à l'URL spécifiée.
 */
$(function()
{
	$('.quickjump').change(function()
	{
		if(this.value != '')
		{
			document.location = this.value;
		}
	});
});

/**
 * Gestion des composants HTML5.
 * 
 * Ajoute des fonctionnalités basiques HMTL5 pour les navigateurs ne le supportant pas.
 * Qu'IE aille rotir en enfer.
 */
$(function()
{
	//Prend un input et enlève tous les caractères non numériques
	var Numeric = function()
	{
		Valeur = parseInt(this.value.replace(/[^0-9\.]/g,''), 10);
		
		if(isNaN(Valeur))
			this.value = '';
		else
			this.value = Valeur;
	};
	
	//Prend un input, enlève tous les caractères non numériques et vérifie la validité de la donnée saisie par rapport aux bornes min et max
	var Number = function()
	{
		Numeric.call(this);
		
		if(this.value == '')
			return;
		
		jItem = $(this);
		
		if(jItem.attr('min'))
		{
			if(this.value < parseInt(jItem.attr('min')))
			{
				jItem.val(jItem.attr('min'));
			}
		}
		
		if(jItem.attr('max'))
		{
			if(this.value > parseInt(jItem.attr('max')))
			{
				jItem.val(jItem.attr('max'));
			}
		}
	};
	
	$('input[type=number]').change(Number);
	$('input[type=number]').keyup(Number);
	$('input[type=number]').keydown(Number);
	
	$('input[type=numeric]').change(Numeric);
	$('input[type=numeric]').keyup(Numeric);
	$('input[type=numeric]').keydown(Numeric);
});

/**
 * Gestion des textarea de type "tex-container"
 * 
 * Ajoute un bouton permettant de prévisualiser le rendu.
 */
$(function()
{
	var NotLoaded = true;
	var Apercu;
	var TeXContainers = $('textarea.tex-container');
	
	if(TeXContainers.length > 0 && !window.MathJax)
	{
		loadMathJax();
	}
	
	TeXContainers.after(function()
	{
		var jTextArea = $(this);
		var Item = $('<span class="tex-container-preview">Prévisualiser</span>').click(function()
		{
			if(NotLoaded)
			{
				//Initialiser la fenête d'aperçu
				Apercu = $('<div class="texable"></div>');
				$('body').append(Apercu);
				Apercu.hide();
				Apercu.dialog({
					modal: true,
					autoOpen: false,
					width: '60%',
					title: 'Prévisualisation du texte',
					buttons: {
						Ok: function() {
							$( this ).dialog( "close" );
						}
					}
				});
				NotLoaded = false;
			}
			
			Apercu.html(jTextArea.val().replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br />$2'));
			if(jTextArea.val().indexOf('$') != -1)
			{
				MathJax.Hub.Queue(["Typeset", MathJax.Hub, Apercu[0]]);
			}

			Apercu.dialog('open');
			
			
		});
		
		return Item;
	});
});
