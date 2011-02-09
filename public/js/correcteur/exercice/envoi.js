var IsCompiling = false;
var NbPages = 0;
var CurrentPage = 1;
var Tabs;
var Modal;
var Historique;
var editor;

/**
 * Gestion de la coloration syntaxique de la zone de texte
 * 
 * @see http://codemirror.net/manual.html
 */
$(function()
{
	//Utilisation de la coloration syntaxique
	editor = CodeMirror.fromTextArea("corrige", {
		  parserfile: "parselatex.js",
		  path: "/public/js/CodeMirror/",
		  stylesheet: "/public/css/codeMirror/latexcolors.css",
		  autoMatchParens: true,
		  lineNumbers: true,
		  //lineNumberDelay: 2000,
		  indentUnit: 4,
		  tabMode: 'shift'
	});
});

/**
 * Gestion de l'interface en onglets et des messages.
 * Gestion de l'historique et de l'aperçu.
 */
$(function()
{
	//Mettre en onglets
	Tabs = $("#tabs").tabs();
	
	//Préparer les messages modaux
	Modal = $('#modal').dialog({
		autoOpen: false,
		modal: true,
		buttons: {
			OK: function() {
				$(this).dialog("close");
			}
		}
	});
	
	//Faire en sorte que les liens .compiler lancent la compilation
	$('.compiler').click(function(){
		$('#apercu-exercice').click();
	});
	
	//Revert vers une ancienne version
	Historique = $('#historique').change(function()
	{
		if(confirm('Voulez-vous vraiment remplacer votre texte actuel par la révision du ' + Historique.find('option:selected').text() + ' ?'))
		{
			$.ajax({
				url: RevertURL.replace('__ID__', this.value),
				success: function(data){
					Tabs.tabs('select', 'envoi-texte');
					editor.setCode(data);
				}
			});
		}
	});
	
	//Disabler l'onglet historique s'il est vide
	if(Historique.find('option').length == 0)
	{
		Tabs.tabs('disable', 'apercu-historique');
	}

	/**
	 * Helper-function pour récupérer une image d'une des pages de l'aperçu.
	 * 
	 * @param int page le numéro de page à récupérer
	 * 
	 * @return string le code HTML de l'image représentant la page
	 */
	function pageImg(page)
	{
		CurrentPage = page;
		return '<img src="' + PageURL.replace('__PAGE__', page).replace('__LARGEUR__', parseInt($('#envoi-apercu').width()) - 40) + '?_=' + (new Date().getTime()).toString() + '" alt="Image de la page ' + page + ' de l\'aperçu" />';
	}
	
	//Clic sur le bouton aperçu
	$('#apercu-exercice').click(function()
	{
		//Lancer la compilation en réucpérant le code et en affichant un message modal
		IsCompiling = true;
		Modal.html('<p>Veuillez patienter pendant la compilation du document...</p>')
			.dialog('open')
			.bind("dialogbeforeclose", function(){return !IsCompiling;});
		var Texte = editor.getCode();
		
		$.post(
				CompilationURL,
				{texte:Texte},
				function(data)
				{
					IsCompiling = false;
					
					//Mettre à jout l'onglet console
					$('#pre_envoi-log').html(data);
					
					//Les liens vers des numéros de ligne.
					$('#pre_envoi-log a.line-jump').click(function()
					{
						Tabs.tabs('select', 'envoi-texte');
						editor.focus();
						editor.jumpToLine(this.href.replace('line-',''));
					});
					
					//Détecter de façon très laide la présence d'erreurs
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
						
						//Mettre à jour les onglets
						Tabs.tabs('enable', 'envoi-apercu')
							.tabs('enable', 'apercu-historique')
							.tabs('select', 'envoi-apercu');
						$('a[href=#envoi-log]').css('color', 'green');
						
						//Récupérer le nombre de pages.
						NbPages = data.match(/pdf \(([0-9]+) pages?,/)[1];
						R = '<p class="pager">';
						for(var i = 1; i <= NbPages; i++)
						{
							R += '<a href="#" data-page="' + i + '">' + i + '</a> ';
						}
						R += '</p>';
						
						//Mettre à jour l'onglet aperçu
						$('#envoi-apercu').html('<p>Cet aperçu ne correspond pas forcément au rendu exact. Vous pouvez <a href="' + PdfURL + '">télécharger le PDF</a>.</p>' + R + '<p id="pdf-image">' + pageImg(CurrentPage) + '</p>' + R);
						$('#envoi-apercu .pager a').click(function()
						{
							$('#pdf-image').html(pageImg($(this).data('page')));
							return false;
						});
						
						//Mettre à jour l'onglet historique
						Revision = data.match(/Révision #([0-9]+)\n/)[1];
						var Now = new Date();
						$('#historique').prepend('<option value="' + Revision + '">' + Now.getHours() + ':' + Now.getMinutes() + '(' + Texte.length + ' caractères)</option>');
					}
				}
		);
		return false;
	});
});

/**
 * Gestion d'uploadify.
 */
$(document).ready(function() {
	$('#ressource-upload').uploadify({
		uploader : '/public/js/Uploadify/uploadify.swf',
		script : '/correcteur/exercice/_ressource',
		scriptData : {hash : LongHash, token : Token},
		cancelImg : '/public/css/images/cancel.png',
		folder : '/home',
		fileExt : '*.png;*.jpg;*.gif;*.pdf;*.svg;*.dvi;*.ps;*.tex',
		fileDesc : 'Fichiers de ressources',
		auto : true,
	});
});
