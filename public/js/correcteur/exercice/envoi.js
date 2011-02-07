var IsCompiling = false;

//Mise en onglets et préparation des messages et des liens comiler.
$(function()
{
	//Mettre en onglets
	$("#tabs").tabs();
	//Préparer les messages modaux
	$('#modal').dialog({ autoOpen: false, modal:true });
	
	//Faire en sorte que les liens .compiler compilent.
	$('.compiler').click(function(){$('#apercu-exercice').click()});
});

//Gestion de l'éditeur
$(function()
{
	
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
		$('#modal').html('<p>Veuillez patienter pendant la compilation du document...')
			.dialog('open')
			.bind("dialogbeforeclose", function(){return !IsCompiling;});
		
		$.post(
				document.location.toString().replace('envoi', '_compilation'),
				{texte:editor.getCode()},
				function(data)
				{
					IsCompiling = false;
					$('#modal').dialog('close');
					$('#pre_envoi-log').html(data);
					if(data.match('color:red'))
					{
						$("#tabs").tabs('select', 'envoi-log');
					}
					else
					{
						$("#tabs").tabs('select', 'envoi-apercu');
					}
				}
		);
		return false;
	});
});