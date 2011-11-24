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
	<title>ASvis - wizualizacja sieci systemów autonomicznych</title>
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
		<h1>ASvis &ndash; Wizualizacja sieci AS (systemów&nbsp;autonomiczny)</h1>
		<p class="author">Maciek Bański, Piotrek Koszuliński, Paweł Kościuk</p>
		<p><img src="imgs/screen1.png" alt="Screenshot" width="600"></p>
	</header>

	<section class="slide">
		<h2>Plan prezentacji</h2>
		<ul>
			<li>Cele projektowe</li>
			<li>Architektura
				<ul>
					<li>backend</li>
					<li>frontend</li>
				</ul>
			</li>
			<li>Kamienie milowe</li>
			<li>Prezentacja działania aplikacji</li>
			<li>Plany na tydzień</li>
		</ul>
	</section>

	<section class="slide">
		<h2>Cele projektowe</h2>
		<ul>
			<li>Wizualizacja grafu sieci w przejrzysty sposób</li>
			<li>Nawigacja po sieci</li>
			<li>Wyszukiwanie interesujących struktur i ich prezentacja</li>
		</ul>
	</section>

	<section class="slide">
		<h2>Architektura</h2>
		<ul>
			<li>Backend &ndash; bezstanowa aplikacja serwerowa</li>
			<li class="slide">JSONPower!</li>
			<li>Frontend &ndash; webaplikacja typu single page app</li>
		</ul>
	</section>

	<section class="slide">
		<h2>Backend</h2>
		<ul>
			<li>Baza danych...
				<ul>
					<li>graf, więc grafowa</li>
					<li>jedno zapytanie &ndash; cała struktura grafu</li>
					<li>OrientDB &ndash; Javowa baza grafowa</li>
					<li>dlaczego ta?</li>
					<li>...the neverending story</li>
					<li class="slide"><strong>
						<span class="slide">B</slide>
						<span class="slide">U</slide>
						<span class="slide">G</slide>
						<span class="slide">S</slide>
					</strong></li>
					<li class="slide"><strong>
						<span class="slide">M</slide>
						<span class="slide">E</slide>
						<span class="slide">M</slide>
						<span class="slide">O</slide>
						<span class="slide">R</slide>
						<span class="slide">Y</slide>
					</strong></li>
					<li class="slide"><strong>
						<span class="slide">P</slide>
						<span class="slide">E</slide>
						<span class="slide">R</slide>
						<span class="slide">F</slide>
						<span class="slide">O</slide>
						<span class="slide">R</slide>
						<span class="slide">M</slide>
						<span class="slide">A</slide>
						<span class="slide">N</slide>
						<span class="slide">C</slide>
						<span class="slide">E</slide>
					</strong></li>
				</ul>
			</li>
		</ul>

		</ul>
	</section>

	<section class="slide">
		<h2>Backend</h2>
		<ul>
			<li>PHP
				<ul>
					<li>REST-owe API &ndash; micro-framework Tonic</li>
					<li>drivery do OrientDB &ndash; "binarny" i REST+JSON</li>
					<li>moduły:
						<ul>
							<li>(Orient|MySQL)Engine</li>
							<li>ObjectsMapper</li>
							<li>GraphAlgorithms</li>
						</ul>
					</li>
				<ul>
			</li>
		</ul>
	</section>

	<section class="slide">
		<h2>Backend</h2>
		<ul>
			<li>PHP &ndash; funkcjonalności:
				<ul>
					<li>Import MySQL -&gt; OrientDB</li>
					<li>REST-owe API (<a href="https://github.com/zpi2011asvis/asvis/wiki/rest-api">wiki</a>):
						<ul>
							<li>GET /nodes/find/[number]</li>
							<li>POST /nodes/meta</li>
							<li>GET /structure/graph/[node_number]/[depth]</li>
							<li>GET /structure/tree/[node_number]/[height]/[dir]</li>
							<li>GET /structure/path/[num_start]/[num_end]/[dir]</li>
							<li>GET /connections/meta/[num_for]</li>
						</ul>
					</li>
				</ul>
			</li>
		</ul>
	</section>

	<section class="slide">
		<h2>Backend &ndash; historia</h2>
		<ol>
			<li class="slide">baza MySQL &ndash; niepełna, niejasna
				<img src="imgs/mysql_schema.png" width="600" alt="MySLQ schema" class="current-only">
			</li>
			<li class="slide">pierwszy importer &ndash; przewidywany czas 8 dni (less than 50k nodes!)</li>
			<li class="slide">pierwszy kontakt z autorami bazy &ndash; odpowiadają! <span class="slide">i poprawiają dokumentację</span></li>
			<li class="slide">pierwszy schemat
				<img src="imgs/asvis_schema_old_final.svg" width="800" alt="MySLQ schema" class="current-only">
			</li>
			<li class="slide">próba pobrania danych &ndash; fetch plan</li>
		</ol>
	</section>

	<section class="slide">
		<h2>Backend &ndash; historia</h2>
		<p><img src="imgs/graph_fetchplan_final.svg" width="600" alt="MySLQ schema"></p>
	</section>

	<section class="slide">
		<h2>Backend &ndash; historia</h2>
		<ol>
			<li>baza MySQL &ndash; niepełna, niejasna</li>
			<li>pierwszy importer &ndash; przewidywany czas 8 dni (less than 50k nodes!)</li>
			<li>pierwszy kontakt z autorami bazy &ndash; odpowiadają!</li>
			<li>pierwszy schemat</li>
			<li>próba pobrania danych &ndash; fetch plan</li>
			<li class="slide">kolejny kontakt &ndash; błędy w bazie i dokumentacji &ndash; poprawiają!</li>
			<li class="slide">kolejny kontakt &ndash; błąd krytyczny w bazie &ndash; błędne wyniki &ndash; poprawiają!</li>
		</ol>
	</section>

	<section class="slide">
		<h2>Backend &ndash; historia</h2>
		<ol start="7">
			<li>kolejny kontakt &ndash; błąd krytyczny w bazie &ndash; błędne wyniki &ndash; poprawiają!</li>
			<li class="slide">inwigilacja</li>
			<li class="slide">kolejny kontakt &ndash; jednak im się nie udało &ndash; poprawiają!</li>
			<li class="slide">kolejny kontakt &ndash; wydajność spadła &gt;10x &ndash; poprawiają!</li>
			<li class="slide">kolejny kontakt (tym razem również z twórcą drivera)</li>
			<li class="slide">...dzisiaj</li>
		</ol>
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
