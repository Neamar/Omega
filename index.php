<?php 
session_start();
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset=utf-8 />
	<title>Bienvenue sur eDevoir !</title>
	<link rel="stylesheet" media="all" href="/public/css/index/base.css" />
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.0/jquery.min.js"></script>
	<!--[if IE]>
			<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
	<script type="text/javascript">
		var _gaq = _gaq || [];
		_gaq.push(['_setAccount', 'UA-21852845-1']);
		_gaq.push(['_trackPageview']);
	
		(function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		})();
	</script>
</head>
<body>
<div id="main-top-r">
	<div id="content-top">
<!-- Logo -->
<header role="banner" id="banner"> 
    <a href="/">
    <hgroup id="logo">
        <h1>eDevoir.com</h1>
        <h2>Le site qui fait vos devoirs</h2>
    </hgroup>
    </a> 
</header>

<!-- Top Barre -->
<nav id="top">
    <p>Bienvenue sur <strong><span>e</span>Devoir</strong> !</p>
    <ul>
        <li><a href="/eleves.htm">Vous êtes élève ?</a></li>
        <li><a href="/parents.htm">Vous êtes parent ?</a></li>
    </ul>
</nav>

<!-- Présentation du site -->
<section id="content">
<p><strong>Un devoir à réaliser ?</strong> Une équipe de gens compétents se tient à votre
disposition pour vous fournir – dans les délais que <span>vous</span> fixez – un
rendu de qualité et un suivi personnalisé adapté à <span>vos besoins</span>.</p>

<nav>
    <ul>
		<li><a class="active" href="/"><span>Accueil</span></a></li>
        <li><a href="/fonctionnement.htm"><span>Fonctionnement du site</span></a></li>
        <li><a href="/blog/"><span>Blog</span></a></li>
        <li><a href="/exemples.htm"><span>Exemples de corrigé</span></a></li>
    </ul>
    </nav>
</section>

<div id="content-form">
<!-- Formulaire de connexion élève -->
<section id="eleve_form">
<?php 
if(!isset($_SESSION['Eleve']))
{
	?>
	<h1>Connexion élève</h1>
	<form method="post" action="/eleve/connexion" id="connexion-eleve">
		<label for="eleve_email">E-mail :</label>
		<input type="email" name="email" id="eleve_email" placeholder="nom@fai.fr" required="required" /><br />
		<label for="eleve_password">Mot de passe :</label>
		<input type="password" name="password" id="eleve_password" required="required" /><br />
		<input type="submit" name="connexion-eleve" value="Connexion" />
	</form>
	<p><a href="/eleve/inscription">Pas encore inscrit ?</a></p>
	<?php 
}
else
{
	?>
	<h1>Élève connecté</h1>
	<p class="deja-connecte">Vous êtes connecté.</p>
	<ul>
		<li><a href="/eleve/">Accéder à votre compte</a></li>
		<li><a href="/eleve/connexion">Me déconnecter</a></li>
	</ul>
	<?php
}
?>
</section>

<!-- Formulaire de connexion correcteur -->
<section id="correcteur_form">
<?php 
if(!isset($_SESSION['Correcteur']))
{
	?>
	<h1>Connexion correcteur</h1>
	<form method="post" action="/correcteur/connexion" id="connexion-correcteur">
		<label for="correcteur_email">E-mail :</label>
		<input type="email" name="email" id="correcteur_email" placeholder="nom@fai.fr" required="required" /><br />
		<label for="correcteur_password">Mot de passe :</label>
		<input type="password" name="password" id="correcteur_password" required="required" /><br />
		<input type="submit" name="connexion-correcteur" value="Connexion" />
	</form>
	<p><a href="/correcteur/inscription">Pas encore inscrit ?</a></p>
	<?php 
}
else
{
	?>
	<h1>Correcteur connecté</h1>
	<p class="deja-connecte">Vous êtes connecté.</p>
	<ul>
		<li><a href="/correcteur/">Accéder à votre compte</a></li>
		<li><a href="/correcteur/connexion">Me déconnecter</a></li>
	</ul>
	<?php
}
?>
</section>
</div><!-- /content-form -->

<section id="ouverture">
	<p>Offre parrainage ! <a href="/blog/article/consulter/4">Parrainez un élève et gagnez 10% du prix de son premier exercice</a> !</p>
</section>
<!-- Liens "légaux" -->
<footer>
<ul>
	<li><a href="/documentation/">Documentation</a></li>
	<li><a href="/cgu.htm">CGU</a></li>
	<li><a href="/cgv.htm">CGV</a></li>
	<li><a href="/legal.htm">Mentions légales</a></li>
	<li><a href="/confidentialite.htm">Confidentialité</a></li>
	<li><a href="/presse.htm">Presse</a></li>
	<li><a href="/contact.htm">Nous contacter</a></li>
</ul>

<aside id="social-network">
<!-- Twitter -->
<a href="http://twitter.com/share" class="twitter-share-button" data-url="http://edevoir.com" data-text="RT @eDevoircom : http://edevoir.com, le site qui fait vos devoirs !" data-count="vertical" data-lang="fr">Tweet</a>
<script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>

<!-- Facebook -->
<!--<iframe src="http://www.facebook.com/plugins/like.php?href=http%3A%2F%2FeDevoir.com&amp;layout=box_count&amp;show_faces=false&amp;width=65&amp;action=like&amp;colorscheme=light&amp;height=65" style="border:none; overflow:hidden; width:65px; height:65px;"></iframe>-->
<iframe src="http://www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2Fpages%2FEDevoir%2F194459280586020&amp;layout=box_count&amp;show_faces=true&amp;width=65&amp;action=like&amp;colorscheme=light&amp;height=65" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:65px; height:65px;" allowTransparency="true"></iframe>
</aside>
</footer>
    </div><!-- /content-top -->
</div><!-- /main-top-r -->

<!--[if IE 6]>
	<script type="text/javascript">
		var IE6UPDATE_OPTIONS = {
			icons_path: "public/js/ie6update/images/"
		}
	</script>
	<script type="text/javascript" src="/public/js/ie6update/ie6update.js"></script>
<![endif]-->
</body>
</html>