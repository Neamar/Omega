/**
 * Sommes-nous en train de compiler le document ?
 * (affichage d'une fenêtre modale)
 * 
 * @var Boolean
 */
var IsCompiling = false;

/**
 * Combien y-a-t-il de pages dans le document actuel ?
 * 
 * @var Number
 */
var NbPages = 0;

/**
 * Quelle page consultons-nous actuellement ? (1 = première page)
 * 
 * @var Number
 */
var CurrentPage = 1;

/**
 * Élément HTML contenant les différents onglets.
 */
var Tabs;

/**
 * Élément HTML contenant la fenêtre modale d'informations.
 */
var Modal;

/**
 * Élément HTML contenant la liste des anciennes versions du texte.
 */
var Historique;

/**
 * L'éditeur codemirror représentant le textarea amélioré.
 */
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
		  tabMode: 'shift',
		  indent: 'flat',
		  electricChars: false,
		  
	});
});

/**
 * Gestion de l'interface en onglets et des messages.
 * Gestion de l'historique et de l'aperçu.
 */
$(function()
{
	//Mettre en onglets
	Tabs = $("#tabs").tabs().css('min-height', '1000px');
	
	//Afficher le message "aperçu non à jour" quand nécessaire
	$("#tabs").bind("tabsselect", function(e, ui){
		if(ui.index == 1)
		{
			if($('#envoi-apercu').data('last-version') != editor.getCode())
			{
				$('#apercu-obsolete').show();
			}
		}
	});
	$('#apercu-obsolete').hide();
	$('#envoi-apercu').data('last-version', $('#corrige').val());
	
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
		Largeur = parseInt($('#envoi-apercu').width()) - 40;
		CurrentPage = page;
		Img = '<img \
			id="apercu" \
			src="' + PageURL.replace('__PAGE__', page).replace('__LARGEUR__', Largeur) + '?_=' + (new Date().getTime()).toString() + '" \
			alt="Image de la page ' + page + ' de l\'aperçu" \
			style="width:' + Largeur + 'px; height:' + Math.round(Largeur * 1.4142) + 'px; background:url(/public/images/global/loader.gif) center center no-repeat;" \
			onload="this.removeAttribute(\'style\');" \
			onclick="$.prettyPhoto.open(\'http://omega.localhost/correcteur/exercice/_preview/70bdb1/page/' + page + '/width/1300\', \'Page' + page + '\')" \
		/>';
		
		return Img;
	}
	
	//Clic sur le bouton aperçu
	$('#apercu-exercice').click(function()
	{
		//Lancer la compilation en récupérant le code et en affichant un message modal
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
					
					//Mettre à jour l'onglet console
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
						Modal.html('<p>Des erreurs se sont produites à la compilation. Vous trouverez plus de détails dans l\'onglet «&nbsp;Console&nbsp;» qui vient de s\'afficher (lignes en rouge).</p>');
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
						Pages = []
						for(var i = 1; i <= NbPages; i++)
						{
							Pages[i - 1] = '<a href="#" data-page="' + i + '">' + i + '</a> ';
						}
						R = '<p class="pager">' + Pages.join(' &ndash; ') + '</p>';
						
						//Mettre à jour l'onglet aperçu. Masquer le message comme quoi le contenu est obsolète.
						$('#apercu-obsolete').hide();
						$('#envoi-apercu').data('last-version', editor.getCode());
						$('#apercu').html('<p>Cet aperçu ne correspond pas forcément au rendu exact. Vous pouvez <a href="' + PdfURL + '">télécharger le PDF</a>.</p>' + R + '<p id="pdf-image">' + pageImg(CurrentPage) + '</p>' + R);
						$('#apercu .pager a').click(function(e)
						{
							$('#pdf-image').html(pageImg($(this).data('page')));
							e.preventDefault();
						});
						
						//Mettre à jour l'onglet historique
						Revision = data.match(/Révision #([0-9]+)\n/)[1];
						var Now = new Date();
						$('#historique').prepend('<option value="' + Revision + '">' + Now.getHours() + ':' + Now.getMinutes() + ' (' + Texte.length + ' caractère' + (Texte.length>1?'s':'') + ')</option>');
						
						//Amener le scroll au bon endroit
						var container = $('html');
						container.scrollTop(
							$('#apercu').offset().top
						);
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
	var Ressources = $('#ressources');
	
	/**
	 * Mettre à jour la liste des ressources affichées.
	 */
	function updateRessources()
	{
		Ressources.load(
			RessourcesURL,
			function()
			{
				Ressources.find('li').click(function()
				{
					Tabs.tabs('select', 'envoi-texte');
					Modal.html('<p>Vous pouvez insérer cette ressource en utilisant ce code :<br /><input type="text" readonly="readonly" value="\\includegraphics{' + $(this).text() + '}" onclick=this.select() /></p>')
						.dialog('open');
					
				});
			});
	}
	
	$('#ressource-upload').uploadify({
		uploader : '/public/js/Uploadify/uploadify.swf',
		buttonText: 'Envoyer un fichier',
		script : '/correcteur/exercice/_ressource',
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
