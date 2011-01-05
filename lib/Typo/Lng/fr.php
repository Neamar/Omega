<?php
self::$Balise=array(
//Balise à un argument.
	'#\\\\(emph|i){(.+)}#isU'=>'<em>$2</em>',													//Mise en emphase (italique le plus souvent)
	'#\\\\(emph|i){(.+)}#sUi'=>'<em>$2</em>',													//On est obligé de tricher pour permettre l'imbrication de cette balise.
	'#\\\\(emph|i){(.+)}#Uis'=>'<em>$2</em>',													//...
	'#\\\\emph\[([1-6])\]{(.+)}#sUi'=>'<em class="emph$1">$2</em>',				//Emphases spéciales.
	'#\\\\textit{(.+)}#Uis'=>'<em class="italique">$1</em>',								//Vraie mise en italique (ne s'annule pas quand imbriquée)
	'#\\\\(textbf|b){(.+)}#isU'=>'<strong>$2</strong>',										//Mise en gras (bold face)

	'#\\\\textss{(.+)}#isU'=>'<span class="ss">$1</span>',								//Texte sans serif
	'#\\\\(textms|texttt){(.+)}#isU'=>'<tt>$2</tt>',												//Texte monospace

	'#\\\\quote{(.+)}#isU'=>'<q>$1</q>',															//Citation
	'#\\\\up{(.+)}#isU'=>'<sup>$1</sup>',															//Texte en exposant
	'#\\\\down{(.+)}#isU'=>'<sub>$1</sub>',														//Texte en indice
	'#\\\\small{(.+)}#isU'=>'<small>$1</small>',												//Petit texte
	'#\\\\big{(.+)}#isU'=>'<big>$1</big>',															//Gros texte
	'#\\\\quickref{(.+)}#isU'=>'<span class="quickref">$1</span>',					//Référence rapide.
	'#\\\\verse{(.+)}#isU'=>'<span class="ver">$1</span>',								//Pour écrire des vers.
	'#\\\\(century|c){\s?-?([XVICM]+)}#isU'=>'<span class="century">$2</span><sup>e</sup>',//Pour écrire un siècle en chiffres romains confortables
	'#\\\\roman{([XVICM]+)}#isU'=>'<span class="roman">$1</span>',			//Pour écrire un siècle en chiffres romains confortables
	'#\\\\verbatim{(.+)}#isU'=>'$1',																		//Pour écrire des balises sans les parser. Cf. $Escape_And_Prepare.
	'#\\\\date{-?([0-9]+)}#'=>'$1',																			//Balise purement sémantique
	'#\\\\taxonomy{(.+)}#'=>'<em>$1</em>',																			//Nom taxonomique

//Balise à deux arguments
	'#\\\\acronym\[(.+)\]{(.+)}#isU'=>'<acronym title="$1">$2</acronym>',		//Acronyme
	'#\\\\abrev\[(.+)\]{(.+)}#isU'=>'<abbr title="$1">$2</abbr>',						//Abréviation
	'#\\\\color\[(.+)\]{(.+)}#isU'=>'<span style="color:$1;">$2</span>',			//Texte en couleur
	'#\\\\color\[(.+)\]{(.+)}#siU'=>'<span style="color:$1;">$2</span>',			//Texte en couleur (2)
	'#\\\\color\[(.+)\]{(.+)}#sUi'=>'<span style="color:$1;">$2</span>',			//Texte en couleur (2)
	'#\\\\(lien|link|l)\[(.+)\]{(.+)}#iU'=>'<a href="$2">$3</a>',							//Lien
	'#\\\\image\[nf\|(.*)\]{(.+)}#iU'=>'<img src="$2" alt="$1" title="$1" class="nonflottant centre"/>',			//Mettre une omage et son texte alternatif
	'#\\\\image\[(.*)\]{(.+)}#iU'=>'<img src="$2" alt="$1" title="$1" />',			//Mettre une image et son texte alternatif
	'#\\\\labelimage\[(.*)\]{(.+)}#iU'=>'<span class="labelimage"><span><img src="$2" alt="$1" title="$1" /><br />$1</span></span>',			//Mettre une image et son texte alternatif
	'#\\\\label\[([a-z]+)\]{(.*)}#isU'=>'<span id="$1">$2</span>',						//Pour faire des références dans les longs textes. (signalement)
	'#\\\\ref\[(.+)\]{(.+)}#isU'=>'<a href="#$1" title="Voir $1">$2</a>',			//Pour faire des références dans les longs textes. (déplacement)
	);

self::$_SpecialChar=array(
	'&amp&nbsp;; '	=>'&amp;',						//Esperluette.
	'  '							=>' ',								//Double espace
	' &nbsp;'				=>'&nbsp;',						//Espace plus espace insécable : donne une espace insécable.
	'. . .'						=>'...',								//Points de suspension en un unique caractère.
	' .'							=>'.',								//Le point absorbe l'espace le précédant.
	'. »'						=>'.»',								//Le guillemet absorbe l'espace du point.
	'. &rdquo;'			=>'.&rdquo;',					//Idem
	'» ,'						=>'»,',								//Idem
	'&rdquo; ,'			=>'&rdquo;,',					//Idem
	'&rdquo; .'			=>'&rdquo;.',					//Idem
	'&rdquo;&nbsp;.'	=>'&rdquo;.',					//Idem
	'\\shy&nbsp;; '					=>'&shy;',						//Tiret de césure optionnelle
	'\\nbsp&nbsp;; '					=>'&nbsp;',						//Tiret de césure optionnelle
);

self::$SpecialChar=array(
	' &nbsp;'		=>'&nbsp;',						//Espace plus espace insécable : donne une espace insécable.
	'? &nbsp;!'	=>'&#8253;',					//Point exclarrogatif ?!
	'! &nbsp;?'	=>'&#8253;',					//Point exclarrogatif !?
	'?&nbsp;!'	=>'&#8253;',						//Point exclarrogatif ?!
	'!&nbsp;?'	=>'&#8253;',						//Point exclarrogatif !?
	'...'				=>'&hellip;',					//Points de suspension en un unique caractère.

	' .'				=>'.',									//Le point absorbe l'espace le précédant.
	'» ,'			=>'»,',									//Idem

	' °C'				=>'&nbsp;&#8451;',		//Degré Celsius
	'°C'				=>'&nbsp;&#8451;',		//Degré Celsius
	' °F'				=>'&nbsp;&#8457;',		//Degré Fahrenheit
	'°F'				=>'&nbsp;&#8457;',		//Degré Fahrenheit
	'n°'				=>'n<sup>o</sup>',
	'oe'				=>'&oelig;',						//Ligature du oe minuscule : ?
	'OE'				=>'&OElig;',					//Ligature du OE majuscule : ?
	'Oe'				=>'&OElig;',
	'ae'				=>'&aelig;',						//Ligature du ae minuscule : æ
	'AE'				=>'&AElig;',						//Ligature du AE majuscule : Æ
	'Ae'				=>'&AElig;',

	'---'				=> '&mdash;',					//Tiret quadratin (pour les dialogues)
	'--'					=>'&ndash;',					//Tiret demi quadratin (pour les incises)
	' - '				=>' &ndash; ',					//Tiret demi quadratin non signalé (pour les incises)
	' -,'				=>' &ndash;,',					//Tiret demi quadratin non signalé (pour les incises)
	' -;'				=>' &ndash;;',					//Tiret demi quadratin non signalé (pour les incises)
);

self::$Ponctuation=array(
	'#&#'										=>'&amp;',//Esperluette HTML. À faire en premier, car des & apparaitront en cacactères spéciaux après.
	//Enlever les délires littéraires tels que les répétitions de ! ou de ...
	"#!{3,}#"								=>'!!',// !!!!!!!!!!! => !!
	"#\.{4,}#"								=>'...',// ......... => ...

	//Caractères doubles et ponctuation forte  : (espace fine avant, espace forte après)
	//Concerne : ". ; : ? !"
	"#\s?([;:!?])\s?#isU"					=>'&nbsp;$1 ',
	'#\! &nbsp;\!#'							=>'!!',//Double point d'exclamation

	//Caractères finaux, de segmentation : . , ...
	"#\s?([\.,])\s?#isU"					=>'$1 ',
	"#([0-9]) ,([0-9])#"					=>'$1,$2',//Nombres écrits avec des virgules.
	'#\. \. \.#'										=>'...',//La regexp n'est pas parfaite : il faut la corriger dans le cas des points de suspension.
	"#\. (-?[a-z]\.) #isU"				=>'.$1',//Et des abréviations ! (penser au - dans le cas de J.-C.)
	"#\. ([-a-z]\.) #sUi"				=>'.$1',//Et des abréviations ! (à répéter)
	"#([a-z]\.[a-z]\.) ([a-z])#sUi"				=>'$1$2',//Repérer le dernier si non suivi d'un point
	"#([0-9])(\.|,) ([0-9])#sU"				=>'$1$2$3',//Et des chiffres !

	//Guillemets internes : les guillemets à l'intérieur de guillemet. Ils sont signalés dans le texte par des "' texte '".
	'#\s?"\'(.+)\'"\s?#isU'				=>' &ldquo;$1&rdquo; ',

	//Guillemets : remplacer les guillemets typographiques par des guillemets français..
	'#(\s)?"(.+)"(\s)?#isU'				=>'$1«&nbsp;$2&nbsp;»$3',

	//Et les guillemets manuels doivent être modifiés aussi.
	'#« (.+) »#Us'							=>'«&nbsp;$1&nbsp;»',
	'#«([^\s&].+[^;])»#Us'							=>'«&nbsp;$1&nbsp;»',

	//Tenter de rattraper les imbrications de guillemets mal faites
	'#»([a-z0-9].+)«#iU'					=>' &ldquo;$1&rdquo; ',
	//Entames de dialogues
	'#\n- #isU'								=>"\n&mdash; ",
	//Unités : elles doivent être précédées d'une espace fine insécable.
	"#([0-9., ][0-9]) ?([%a-z$&]+)(\s|$)#i"		=>'$1&nbsp;$2$3',
	//Nombre décimaux
	"#([0-9])\.([0-9])#i"					=>'$1,$2',
);
?>