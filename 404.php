<?php
$Codes_EN = array(
	404 => 'Not Found',
	403 => 'Forbidden',
);

$Codes_FR = array(
	404 => 'La page recherchée est introuvable',
	403 => 'Vous n\'avez pas le droit d\'accéder à cettre ressource.'
);

if(!isset($Code))
{
	if(!isset($_GET['E']))
	{
		//Par défaut
		$Code = 404;
	}
	else
	{
		//Récupérer le code par GET (ErrorDocument Apache)
		$Code = $_GET['E'];
	}
}

//Le code doit exister
if(!in_array($Code, array_keys($Codes_EN)))
{
	$Code = 404;
}

//Gestion du message
if(!isset($Message))
{
	$Message = $Codes_FR[$Code];
}

//Si on n'est pas chargé depuis le bootstrap, récupérer les composants de base.
if(!class_exists('OO2FS'))
{
	$File = str_replace('\\', '/', __FILE__);
	define('PATH', substr($File, 0, strrpos($File, '/')));
	include PATH . '/lib/core/constants.php';
	include PATH . '/lib/core/OO2FS.php';
}
 
header('Status: ' . $Code. ' ' . $Codes_EN[$Code], true, $Code);
?>
<!DOCTYPE html> 
<html> 
<head> 
	<meta charset=utf-8 /> 
	<title>Erreur <?php echo $Code; ?></title> 
	<link href="/public/css/head.css" rel="stylesheet" type="text/css" media="screen" /> 
	<link href="/public/css/base.css" rel="stylesheet" type="text/css" media="screen" />
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.0/jquery.min.js"></script>
	<script type="text/javascript" src="/public/js/default.js"></script> 
	<!--[if IE]>
		<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]--> 
</head> 
<body> 
 
<div class="content-head"> 
 
<div id="content-ribbon"> 
<section id="ribbon"> 
 
	<div id="ribbon-left"> 
		:(
	</div> 
	<div id="ribbon-center"> 
		Erreur <?php echo $Code; ?>
	</div> 
	<div id="ribbon-right"> 
		<a href="/">Retour à l'accueil</a> 
	</div> 
 
</section> 
</div><!-- /content-ribbon--> 
 
<section id="logo"> 
	<section id="section_logo"> 
		<a href="/" id="a_logo">&nbsp;</a> 
	</section> 
	<section id="logo-liens"> 
<ul> 
	<li><a href="/"><span>Accueil</span></a></li> 
	<li><a href="/eleve/"><span>Module élève</span></a></li> 
	<li><a href="/correcteur/"><span>Module correcteur</span></a></li> 
</ul> 
 
	</section> 
</section> 
 
<section id="ariane"> 
<ul> 
	<li> 
		<div itemscope itemtype="http://data-vocabulary.org/Breadcrumb"> 
			<a href="/" itemprop="url"> 
			   	<span itemprop="title"><span class="edevoir"><span>e</span>Devoir</span></span> 
			</a> 
		</div> 
	</li> 
	<li> 
		<div itemscope itemtype="http://data-vocabulary.org/Breadcrumb"> 
			<a href="" itemprop="url"> 
			   	<span itemprop="title">Erreur <?php echo $Code; ?></span> 
			</a> 
		</div> 
	</li>
</ul> 
 
</section> 
</div><!-- /content-head ---> 
 
<div id="content-title"> 
<section id="title"> 
	<h1>Erreur <?php echo $Code; ?></h1> 
	<p class="intro">Une erreur s'est produite, empêchant le chargement de la page.</p> 
</section> 
</div><!-- /content-title ---> 
 
<div id="main-message"> 
<div id="content-message" class="error"> 
<aside class="message error"> 
	<p><?php echo $Message; ?></p> 
</aside> 
 
</div><!-- /content-message ---> 
<div id="bottom-message" class="error"></div> 
</div><!-- /main-message ---> 
 
<div class="content-base"> 
<section id="content">


<h2 class="h2_actions" id="index-404-actions">Que faire maintenant ?</h2>
<?php 
include(OO2FS::viewHelperPath('Html'));

$Actions = array(
	'/' => array(
		"Retourner à l'accueil du site",
		'Je suis un grand garçon (ou une grande fille, pas de sexisme), je me débrouillerais seul(e) pour retrouver mon chemin.'
	),
	'/documentation/' => array(
		"Aller chercher de l'aide dans la documentation",
		'Qui sait ! Peut-être même que j\'y trouverais la réponse à ma question.'
	),
	'/contact.htm' => array(
		'Nous contacter',
		'Non, vraiment... je ne vois pas où est le problème. Vous voudriez pas m\'aider ?'
	)
);

echo ViewHelper_Html_listAction($Actions, '__URL__');
?>


<?php 
if(isset($_SESSION['Eleve']))
{
	?>
	<h2>Liens élèves</h2>
	<?php
	$Actions = array(
		'' => array(
			"Retourner à l'accueil de mon compte",
			"Ok, admettons que je n'avais rien à faire là. Je vais repartir de zéro..."
		),
		'options/' => array(
			"Modifier mes options",
			'Peut-être que ça vient de là. Ou peut-être pas. Qui sait ?'
		),
		'exercice/' => array(
			"Consulter mes exercices",
			"Je ne sais pas ce qu'il s'est passé, mais ce serait bien si on pouvait retourner au boulot maintenant. Un peu de sérieux !"
		), 
		'connexion' => array(
			'Me déconnecter de mon compte',
			"J'ai rien compris. Je vais lâcher ce PC et aller prendre l'air..."
		)
	);
	
	echo ViewHelper_Html_listAction($Actions, '/eleve/__URL__');
}?>

<?php 
if(isset($_SESSION['Correcteur']))
{
	?>
	<h2>Liens correcteurs</h2>
	<?php
	$Actions = array(
		'' => array(
			"Retourner à l'accueil de mon compte",
			"Ok, admettons que je n'avais rien à faire là. Je vais repartir de zéro..."
		),
		'options/' => array(
			"Modifier mes options",
			'Peut-être que ça vient de là. Ou peut-être pas. Qui sait ?'
		),
		'exercice/' => array(
			"Consulter mes exercices",
			"Je ne sais pas ce qu'il s'est passé, mais ce serait bien si on pouvait retourner au boulot maintenant. Un peu de sérieux !"
		), 
		'connexion' => array(
			'Me déconnecter de mon compte',
			"J'ai rien compris. Je vais lâcher ce PC et aller prendre l'air..."
		)
	);
	
	echo ViewHelper_Html_listAction($Actions, '/correcteur/__URL__');
}?>
</section>
</div><!-- /content-base ---> 
</body> 
</html>
<?php
//Terminer le script ici ; si on a appelé cette page depuis un autre endroit on ne veut pas y retourner (par exemple, le bootstrap). 
exit();
?>