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
		<p><img src="imgs/screen1.png" alt="Screenshot" width="650"></p>
	</header>

	<section class="slide">
		<h2>Plan prezentacji</h2>
		<ul>
			<li>Tematyka projektu</li>
			<li>Problem złośliwy</li>
			<li>Pouczająca historia, czyli przebieg prac nad projektem</li>
			<li>Aplikacja</li>
			<li>Możliwości rozwoju i wnioski</li>
		</ul>
	</section>

	<section class="slide">
		<h2>Tematyka projektu</h2>

		<blockquote><p>System wyszukiwania i wizualizacji połączeń pomiędzy autonomicznymi systemami w Internecie.</p></blockquote>

		<ul>
			<li class="slide">System autonomiczny</li>
			<li class="slide">Sieć AS-ów &ndash; szkielet Internetu</li>
			<li class="slide">Gotowe dane &ndash; baza MySQL</li>
			<li class="slide">Wizualizacja i nawigacja po grafie</li>
			<li class="slide">Wyszukiwanie struktur i ich prezentacja</li>
			<li class="slide">Prezentacja wyników w przeglądarce internetowej</li>
		</ul>
	</section>

	<section class="slide">
		<h2>Problem złośliwy</h2>
		
		<blockquote><p>Prawdziwy charakter problemu objawia się dopiero w miarę opracowywania rozwiązania.</p></blockquote>

		<ul class="slide">
			<li>Niejasne i niepełne dane w bazie MySQL</li>
			<li class="slide">Jak najlepiej prezentować sieć, skoro nie wiemy jak wygląda?</li>
			<li class="slide">Jakie struktury wyszukiwać, skoro nie wiemy jakie istnieją?</li>
		</ul>
	</section>

	<section class="slide">
		<h2>A jak inni?</h2>

		<img src="imgs/caida_vis.png" alt="Wizualizacja stworzona przez Caidę" width="700">
	</section>

	<section class="slide">
		<h2>Historia &ndash; architektura</h2>

		<ul>
			<li>Grafowa i relacyjna baza danych</li>
			<li>Backend &ndash; bezstanowa aplikacja serwerowa typu REST (w PHP)</li>
			<li class="slide">JSONPower!</li>
			<li>Frontend &ndash; webaplikacja typu single page app</li>
		</ul>
	</section>

	<section class="slide">
		<h2>Historia &ndash; OrientDB</h2>

		<ul>
			<li>Błąd</li>
			<li class="slide">Wsparcie autorów</li>
			<li class="slide">Błąd</li>
			<li class="slide">Wsparcie autorów</li>
			<li class="slide">Błąd</li>
			<li class="slide">Wsparcie autorów</li>
			<li class="slide">...</li>
			<li class="slide">Zerowa wydajność</li>
		</ul>
	</section>

	<section class="slide">
		<h2>Historia &ndash; WebGL</h2>

		<ul>
			<li>Eksperymentalne implementacje w Chrome i Firefox</li>
			<li>Żadnych problemów i świetna wydajność</li>
			<li>Pierwsze rendery...</li>
			<img src="imgs/screen2.png" alt="Pierwsze wizualizacje" width="700" class="slide">
		</ul>
	</section>

	<section class="slide">
		<h2>Historia &ndash; WebGL</h2>

		<ul>
			<li>Do dopracowania...</li>
			<img src="imgs/screen3.png" alt="Pierwsze wizualizacje" width="800">
		</ul>
	</section>

	<section class="slide">
		<h2>Historia &ndash; Bye bye, OrientDB!</h2>

		<ul>
			<li>Autorska baza grafowa w NodeJS</li>
			<li>2 dni pracy</li>
			<li>30x krótszy czas odpowiedzi</li>
			<li>20x mniejsze zużycie RAM-u</li>
			<li class="slide">Great success!</li>
		</ul>
	</section>
	
	<section class="slide">
		<h2>Aplikacja</h2>

		<ul>
			<li>Wyszukiwanie AS-ów</li>
			<li>Wizualizacja sieci (AS-y + połączenia)</li>
			<li>Algorytm grawitacyjny</li>
			<li>Informacje o AS-ach</li>
			<li>Wyszukiwanie struktur:
				<ul>
					<li>ścieżki</li>
					<li>wąskie gardła</li>
				</ul>
			</li>
		</ul>
	</section>
		
	<section class="slide">
		<h2>Wnioski i możliwości rozwoju</h2>
		<ul>
			<li>Ogólne:
				<ul>
					<li>Cache całej struktury w przeglądarce</li>
					<li>Backend jedynie do wyszukiwania struktur</li>
					<li>PHP &ndash; niepotrzebny</li>
				</ul>
			</li>
			<li>Wizualizacja:
				<ul>
					<li>Grupowanie "dużych kulek"</li>
					<li>Płynne przechodzenie pomiędzy AS-ami</li>
					<li>Czytelna prezentacja wyników wyszukiwania</li>
				</ul>
			</li>
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
