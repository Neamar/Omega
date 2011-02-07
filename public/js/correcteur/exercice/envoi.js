var IsCompiling = false;
var NbPages = 0;
var Tabs;
var Modal;

//Mise en onglets et préparation des messages et des liens comiler.
$(function()
{
	//Mettre en onglets
	Tabs = $("#tabs").tabs();
	//Préparer les messages modaux
	Modal = $('#modal').dialog({ autoOpen: false, modal:true, buttons: {
		Ok: function() {
			$( this ).dialog( "close" );
		}
	}
 });
	
	//Faire en sorte que les liens .compiler compilent.
	$('.compiler').click(function(){$('#apercu-exercice').click();});
});

//Gestion de l'éditeur
$(function()
{
	//Récupère une image de page de l'aperçu.
	function pageImg(page)
	{
		return '<img src="' + PageURL.replace('__PAGE__', page).replace('__LARGEUR__', parseInt($('#envoi-apercu').width()) - 40) + '?_=' + (new Date().getTime()).toString() + '" alt="Image de la page ' + page + ' de l\'aperçu" />';
	}
	
	//Utilisation de la coloration syntaxique
	var editor = CodeMirror.fromTextArea("corrige", {
		  parserfile: "parselatex.js",
		  path: "/public/js/CodeMirror/",
		  stylesheet: "/public/css/codeMirror/latexcolors.css",
		  autoMatchParens: true,
		  lineNumbers: true,
		  //lineNumberDelay: 2000,
		  indentUnit: 4,
		  tabMode: 'shift'
	});
	
	//Interception du submit formulaire pour l'aperçu
	$('#apercu-exercice').click(function()
	{
		IsCompiling = true;
		Modal.html('<p>Veuillez patienter pendant la compilation du document...</p>')
			.dialog('open')
			.bind("dialogbeforeclose", function(){return !IsCompiling;});
		
		$.post(
				CompilationURL,
				{texte:editor.getCode()},
				function(data)
				{
					IsCompiling = false;
					$('#pre_envoi-log').html(data);
					
					//Les liens vers des numéros de ligne.
					$('#pre_envoi-log a.line-jump').click(function()
					{
						Tabs.tabs('select', 'envoi-texte');
						editor.focus();
						editor.jumpToLine(this.href.replace('line-',''));
					});
					
					if(data.match('color:red'))
					{
						//Échec de compilation. Donner le focus à la console.
						Tabs.tabs('select', 'envoi-log')
							.tabs('disable', 'envoi-apercu');
						$('a[href=#envoi-log]').css('color', 'red');
						Modal.html('<p>Des erreurs se sont produites à la compilation. Vous trouverez plus de détails dans l\'onglet « Console » qui vient de s\'afficher (lignes en rouge).</p>');
					}
					else
					{
						//Compilation ok 
						Modal.dialog('close');
						
						Tabs.tabs('enable', 'envoi-apercu')
							.tabs('select', 'envoi-apercu');
						
						$('a[href=#envoi-log]').css('color', 'green');
						
						NbPages = data.match(/pdf \(([0-9]+) pages?,/)[1];
						R = '<p class="pager">';
						for(var i = 1; i <= NbPages; i++)
						{
							R += '<a href="#" data-page="' + i + '">' + i + '</a> '
						}
						R += '</p>';
						
						$('#envoi-apercu').html('<p>Cet aperçu ne correspond pas forcément au rendu exact. Vous pouvez <a href="' + PdfURL + '">télécharger le PDF</a>.</p>' + R + '<p id="pdf-image">' + pageImg(1) + '</p>' + R);
						$('#envoi-apercu .pager a').click(function()
						{
							$('#pdf-image').html(pageImg($(this).data('page')));
							return false;
						});
						
					}
				}
		);
		return false;
	});
});