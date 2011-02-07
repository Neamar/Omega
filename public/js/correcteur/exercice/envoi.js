//Mise en onglets
$(function(){
	$("#tabs").tabs();
});

//Utilisation de la coloration syntaxique
$(function(){
	var editor = CodeMirror.fromTextArea("corrige", {
		  parserfile: "parselatex.js",
		  path: "/public/js/CodeMirror/",
		  stylesheet: "/public/css/codeMirror/latexcolors.css"
	});
});