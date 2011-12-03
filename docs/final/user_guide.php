<?

function f($c) {
	return htmlspecialchars(
		preg_replace('/(^\n|\n$)/', '',
			str_replace("\t", '    ', $c)
		)
	);
}

?><html>
<head>
	<meta charset="utf-8">
	<title>ASvis &ndash; Podręcznik użytkownika</title>
	<link rel="stylesheet" href="main.css">
</head>
<body>

<header>
	<hgroup>
		<h1>ASvis</h1>
		<h2>Dokumentacja</h2>
		<h3>Podręcznik uzytkownika</h3>
	</hgroup>

	<nav>
		<h1>Spis treści</h1>
		<ol>
			<li><a href="#requirements">Wymagania</a></li>
			<li><a href="#start">Otwieranie aplikacji</a></li>
			<li><a href="#screen">Ekran aplikacji</a></li>
			<li><a href="#options">Opcje</a></li>
			<li><a href="#browsing">Przeglądanie połączeń</a></li>
			<li><a href="#trees">Wyszukiwanie drzew</a></li>
			<li><a href="#paths">Wyszukiwanie ścieżek</a></li>
		</ol>
	</nav>
</header>

<section id="requirements">
	<h1>Wymagania</h1>

	<p>Do prawidłowego działania aplikacja ASvis wymaga przeglądarki internetowej wspierającej wspierającjej WebGL, oraz karty graficznej kompatybilnej z WebGL.</p>
	<p>W chwili pisania dokumentacji przeglądarkami wspierającymi WebGL są Google Chrome w wersji 15 lub nowszej i Mozilla Firefox w wersji 8 lub nowszej. Wydajność Chrome'a pod względem wykonywania JavaScriptu jest znacznie wyższa niż Firefoksa, dlatego zalecamy Chrome'a jako przeglądarkę do pracy z ASvis.</p>
	<p>Jeżeli WebGL nie będzie dostępny, aplikacja będzie działać w trybie kompatybilności (kontekst 2D przy użyciu <code>Canvas</code>, <strong>dużo</strong> wolniejszym, i nie wyświetlającym wszystkich elementów graficznych, lecz wystarczającym do pracy z aplikacją.</p>
</section>
	
<section id="start">
	<h1>Otwieranie aplikacji</h1>

	<p>Aby otworzyć aplikację wystarczy w przeglądarce internetowej wpisać adres URL serwera aplikacji. Otworzy się strona aplikacji, z domyślnie otwartym zapytaniem o podanie numeru AS oraz głębokości wyszukiwania. (patrz <a href="#browsing">Przeglądanie połączeń</a>).</p>
</section>
	
<section id="screen">
	<h1>Ekran aplikacji</h1>

	<p>Ekran aplikacji podzielony jest na 2 części. Po lewej stronie znajduje się miejsce, w którym wyświetlana jest trójwymiarowa reprezentacja połączeń pomiędzy ASami. Aktualnie wybrany AS jest zaznaczany jako czerwone koło. Po prawej, na pionowym pasku, wyświetlane są informacje dotyczące wybranego ASa - jego numer, nazwa, lista połączeń (wraz z ich typem i stanem), i lista pól adresów.</p>
	<a href="imgs/overview.png"><img src="imgs/overview.png"></a>

	<p>Nawigacja w trójwymiarowym widoku:</p>
	<ul>
		<li>aby obrócić widok należy przytrzymać lewy przycisk myszy i przeciągnąć</li>
		<li>aby przesunąć elementy grafu należy przytrzymać jednocześnie CTRL oraz lewy przycisk myszy i przeciągnąć</li>
		<li>aby przybliżyć/oddalić widok należy przekręcić rolkę myszy</li>
	</ul>

	<p>Po najechaniu kursorem na któryś z wyświetlanych ASów pojawi się dymek z jego numerem i nazwą (w przypadku wyświetlania większych grafów nie wszystkie nazwy mogą być załadowane).</p>
	<a href="imgs/node_popup.png"><img src="imgs/node_popup.png"></a>
	<p>W menu dymka dostępne są polecenia "pokaż z głębokością" oraz "pokaż ścieżki".</p>
	<p>Polecenie "pokaż z głębokością" spowoduje przejście do danego AS-a i wyświetlenie jego otoczenia z wybraną głębokością. (patrz <a href="#browsing">Przeglądanie połączeń</a>)</p>
	<p>Polecenie "pokaż ścieżki" spowoduje wyszukanie i wyświetlenie ścieżek pomiędzy aktualnie wybranym (na czerwono) AS-em i AS-em zaznaczonym. (patrz <a href="#paths">Wyszukiwanie ścieżek</a>)</p>
	<p>Panel z prawej strony, oprócz wyswietlania informacji o wybranym AS-ie pozwala nawigować po połączeniach - kliknięcie na numer ASa z listy połączeń spowoduje jego wybranie. (patrz <a href="#browsing">Przeglądanie połączeń</a>) Ponadto przytrzymanie kursora nad danym połączeniem powoduje oznaczenie tego połączenia na grafie czerwoną linią.</p>
</section>
	
<section id="options">
	<h1>Opcje</h1>

	<p>Panel opcji pozwala na zmianę sposobu wyświetlania AS-ów i połączeń.</p>
	<p>Ustawienia mgły pozwalają na wygaszenie obiektów znajdujących się głębiej w polu widzenia &ndash; przydatne przy oglądaniu dużych grafów.</p>

	<a href="imgs/settings_fog_1.png"><img src="imgs/settings_fog_1.png"></a>
	<a href="imgs/settings_fog_2.png"><img src="imgs/settings_fog_2.png"></a>

	<p>Ustawienia grafu pozwalają na zmianę wielkości AS-ów (zielonych kwadratów) oraz na zmianę przezroczystości wszystkich linii połączeń (aż do ich calkowitej przezroczystości). To ustawienie jest szczególnie przydatne przy <a href="#paths">wyszukiwaniu ścieżek</a> ponieważ nawet przy wyłączonym wyświetlaniu linii połączeń ściezki są wyświetlane. Można w ten sposób prześledzić ścieżkę bez innych, zasłaniajacych widok, połączeń.</p>
	<a href="imgs/path_with_conns.png"><img src="imgs/path_with_conns.png"></a>
	<a href="imgs/path_without_conns.png"><img src="imgs/path_without_conns.png"></a>
	<p>Przycisk "uruchom siły odpychania" pozwala na uruchomienie algorytmu lepiej rozmieszczającego graf w przestrzeni. Algorytm ten włącza się automatycznie przy otwieraniu grafów połączeń i zwieksza ich czytelność poprzez zmiane rozkładu węzłów grafu w przestrzeni. Algorytm ten z powodu swojej czasochłonności nie jest automatycznie uruchamiany dla dużych grafów (powyżej 500 AS-ów). Nie radzimy uruchamiać algorytmu dla grafów powyżej 5000 wierzchołków, ponieważ ma złożoność N<sup>2</sup>/2.</p>
</section>
	
<section id="browsing">
	<h1>Przeglądanie połączeń</h1>

	<p>Przeglądanie połączeń pomiędzy ASami polega na przeglądaniu wycinka całej sieci połączeń. Wycinek taki zorientowany jest wokół wybranego ASa, nazywanego tutaj źródłowym. Połączenia wczytywane są w zależności od zadanej głębokości wyczukiwania.</p>

	<a href="imgs/graph_prompt.png"><img src="imgs/graph_prompt.png"></a>

	<p>Głębokość wyszukiwania jest parametrem określającym jak dalecy sąsiedzi ASa źródłowego mają zostać pokazani. Głębokość 1 oznacza tylko bezpośrednich sąsiadów, bo tylko do nich można dotrzeć z ASa źródłowego przechodząc po maksymalnie 1 połączeniu. Głębokość 2 wyszuka sąsiadów do których można dotrzeć z ASa źródłowego przechodząc po maksymalnie 2 połączeniach, itd.</p>
</section>
	
<section id="trees">
	<h1>Wyszukiwanie drzew</h1>

	<p>Pod pojęciem "drzew" kryją się struktury mające korzeń, jednak nie muszą one być drzewami z matematycznego punktu widzenia. Strukturę taką należy rozumieć jako podsieć połączoną z resztą AS-ów jedynie przez zadanego roota. Pomiędzy liśćmi i gałęziami mogą znajdować się połączenia, jeśli nie będą tworzyły dodatkowego kanału komunikacji poza obręb tej podsieci. Drzewo o wysokości jeden to krzak.</p>
	<p>By wyszukać drzewo należy wybrać korzeń (jedyny punkt komunikacji z resztą świata), maksymalną głębokość poszukiwań i kierunek połączeń.</p>

	<a href="imgs/tree_prompt.png"><img src="imgs/tree_prompt.png"></a>

	<p>Drzewo o wysokości jeden. Jak widać niezaznaczone są AS-y, które mają więcej połączeń niż jedynie z rootem.</p>
	<a href="imgs/tree_highlight_1.png"><img src="imgs/tree_highlight_1.png"></a>
	<p>Inne drzewo o wysokości jeden. Widać połączenia między liścmi i brak zaznaczena AS-ów mających inne połączenia.</p>
	<a href="imgs/tree_highlight_2.png"><img src="imgs/tree_highlight_2.png"></a>
</section>
	
<section id="paths">
	<h1>Wyszukiwanie ścieżek</h1>

	<p>Wyszukiwanie ścieżek pozwala na znalezienie najkrótszych ścieżek łączących dane AS-y. Mając wybrany AS źródłowy (<a href="#browsing">Przeglądanie połączeń</a>) klikamy wyszukiwanie ścieżek i wprowadzamy nr ASa docelowego. Następnie wybieramy czy wyszukujemy ścieżkę prowadzącą DO ASa źródłowego (Downstram), OD (Upstream) czy w dowolnym kierunku (Downstream i Upstream).</p>
	<p>W wyniku zostaje zaznaczona na niebiesko znaleziona ścieżka (lub ściezki, jeżeli jest więcej niż jedna o tej samej długości).</p>
	<a href="imgs/path_highlight.png"><img src="imgs/path_highlight.png"></a>
</section>

</body>
</html>

