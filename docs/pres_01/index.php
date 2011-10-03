<?

function f($c) {
	return htmlspecialchars(
		preg_replace('/(^\n|\n$)/', '',
			str_replace("\t", '    ', $c)
		)
	);
}

?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>ZPI - wizualizacja sieci AS</title>
	<meta name="viewport" content="width=1024, user-scalable=no">
	<link rel="stylesheet" href="deck.core.css">
	<link rel="stylesheet" href="themes/style/swiss.css">
	<link rel="stylesheet" href="extensions/navigation/deck.navigation.css">
	<link rel="stylesheet" href="extensions/status/deck.status.css">
	<link rel="stylesheet" href="extensions/hash/deck.hash.css">
	<link rel="stylesheet" href="sh/styles/shCore.css">
	<link rel="stylesheet" href="sh/styles/shThemeDefault.css">
	<link rel="stylesheet" href="themes/my_style.css">
</head>
<body class="deck-container">

	<header class="slide" id="intro">
		<h1>Wizualizacja sieci AS (systemów autonomiczny)</h1>
		<p class="author">Maciek Bański, Piotrek Koszuliński, Paweł Kościuk, Michał Kułakowski</p>
	</header>

	<section class="slide">
		<h2>Grupa docelowa</h2>
		<img src="imgs/ries-t2.png" width="500" style="float:right">
		<ul>
			<li>administratorzy sieci AS</li>
			<li>Administratorzy Internetu</li>
			<li>naukowcy</li>
		</ul>
	</section>

	<section class="slide">
		<h2>Cele</h2>
		<ul>
			<li>wizualizacja sieci AS</li>
			<li>pomoc przy analizie połączeń sieci AS</li>
			<li>aplikacja webowa</li>
			<li class="slide">zdobycie &euro;nagrody&euro; i zaliczenia</li>
		</ul>
	</section>

	<section class="slide">
		<h2>Środowisko</h2>
		<ul>
			<li>backend
				<ul>
					<li>baza danych &ndash; noSQL, grafowa (?)</li>
					<li>REST-owe API &ndash; lekki framework w PHP (Recess, Frapi?)</li>
					<li>Apache, nginx, LINUX</li>
				</ul>
			</li>
			<li class="slide">komunikacja pomiędzy warstwami &ndash; JSON</li>
			<li class="slide">frontend
				<ul>
					<li>JavaScript + WebGL</li>
					<li>Three.js &ndash; framework do WebGL-a</li>
					<li>HTML5 localstorage (cache), HTML5 history (adresacja)</li>
					<li>Google Chrome, ewentualnie Firefox</li>
				</ul>
			</li>
			<li class="slide">metodologie &ndash; SCRUM (tyle o ile), TDD</li>
		</ul>
	</section>
	<!--
	<section class="slide">
		<h2></h2>
		<ul>
			<li></li>
		</ul>
	</section>

<pre><?= f("
") ?></pre>

	-->

	<a href="." class="deck-permalink" title="Permalink to this slide">#</a>
	<a href="#" class="deck-prev-link" title="Previous">&#8592;</a>
	<a href="#" class="deck-next-link" title="Next">&#8594;</a>

	<p class="deck-status">
		<span class="deck-status-current"></span>
		/
		<span class="deck-status-total"></span>
	</p>


	<script src="jquery-1.6.4.min.js"></script>
	<script src="modernizr.custom.js"></script>
	<script src="sh/scripts/shCore.js"></script>
	<script src="sh/scripts/shBrushJScript.js"></script>
	<script src="deck.core.js"></script>
	<script src="extensions/menu/deck.menu.js"></script>
	<script src="extensions/goto/deck.goto.js"></script>
	<script src="extensions/status/deck.status.js"></script>
	<script src="extensions/navigation/deck.navigation.js"></script>
	<script src="extensions/hash/deck.hash.js"></script>
	<script>
		$(function() {
			$.deck('.slide');
			$('pre').addClass('brush: js');
			SyntaxHighlighter.all();
		});
	</script>
</body>
</html>
