<html>
<head>
	<meta charset="utf-8">
	<title>ASvis &ndash; Sprawozdanie z postępów prac</title>
	<link rel="stylesheet" href="main.css">
</head>
<body>
<header>
	<hgroup>
		<h1>ASvis</h1>
		<h2>Dokumentacja</h2>
		<h3>Sprawozdanie z postępów prac</h3>
	</hgroup>

	<nav>
		<p class="back"><a href="index.php">&crarr; Wstecz</a></p>
	</nav>	
</header>

<section> 
	<h1>Nasza historia</h1>
	
	<h2>OrientDB</h2>

	<p>W początkowej fazie projektu otrzymaliśmy relacyjną bazę danych (MySQL), reprezntująca strukturę powiązanych ze sobą systemów autonomicznych. Ze względu na charakter danych jakie mieliśmy przetwarzać, rozpoczęliśmy poszukiwanie odpowiedniej bazy grafowej. Po zapoznaniu się z dokumentacją kilku projektów oraz przeprowadzeniem wstępnych testów zdecydowaliśmy sie na bazę OrientDB. Pozornie cechowała się ona wyczerpującą dokumentacją oraz ciągłym wsparciem od autorów.</p>
	<p>Nie wiedzieliśmy wtedy, że pochopnie podjęta decyzja będzie miała tak fatalny wpływ na przebieg prac nad projektem. Baza OrientDB pomimo posiadania dość dobrej dokumentacji i będąc ciągle rozwijana, zawiera jeszcze zbyt wiele niedociągnięć, by mogła sprostać zadaniu jakie przed nią postawiliśmy. W trakcie prac nad aplikacją zgłosiliśmy dwa krytyczne błędy dotyczące zwracanych przez bazę wyników. W dodatku dwa dotyczące bardzo słabej wydajności, jeden związany z błędnymi wynikami podczas szybkiego wielokrotnego odpytywania i kilka mniej uciążliwych niedociągnięć. Błędy za każdym razem były szybko naprawiane, jednak łącznie na poprawianiu jakości bazy straciliśmy prawie dwa miesiące czasu, bardzo powoli posuwając się przy tym do przodu z naszymi pracami.</p>
	<p>Więcej o historii związanej z OrientDB w <a href="../pres_02/index.php#slide-5">prezentacji</a>.</p>
	
	<h2>WebGL</h2>
	
	<p>Drugą zastosowaną przez nas technologią był WebGL, który okazał się dużo mniej problematyczny. Wykorzystana przez nas biblioteka ThreeJS, pomimo braku dobrej dokumentacji, okazała się przyjaznym i skutecznym narzędziem. Pomimo faktu, że WebGL posiada jedynie eksperymentalne implementacje w przeglądarkach, a biblioteka ThreeJS nie ma oficjalnych wersji stabilnych, przez cały okres prac trafiliśmy jedynie na jeden drobny błąd.</p>
	<p>Warto zauważyć, że wykorzystana przez nas wersja ThreeJS odbiega już znacznie od dostępnych w tej chwili. Jej update do najświeższej wersji nie jest więc możliwy bez zmian w kodzie aplikacji, ponieważ zostały wprowadzone duże zmiany w API.</p>
	
	<p>Na etapie projektowania aplikacji zdecydowaliśmy się na jej podział na dwa moduły &ndash; Backend i Frontend. We Frontendzie wizualizowane są za pomocą WebGL-a dane dostarczane przez Backend. Test wydajności pod przeglądarką Google Chrome pokazał, że technologia ta pozwoli nam na atrakcyjne prezentowanie grafów zawierających do kilkudziesięciu tysięcy wierzchołków. Z przebiegu całych prac najbardziej zadziwiła nas właśnie stabilność implementacji JavaScriptu i API takich jak WebGL, HTML5 History, LocalStorage w przeglądarkach Mozilla Firefox oraz Google Chrome. Nie są to jeszcze obowiązujące standardy, a w tych dwóch przeglądarkach prace z nimi były bardzo przyjemne.</p>
	
	<h2>Wydajność</h2>
	
	<p>Backend przed przekazaniem odpowiednich zasobów pobiera i przetwarza dane z bazy grafowej. Po zaprojektowaniu jej struktury i migracji rekordów z bazy relacyjnej zaczeła się nasza długa podróż pociągiem OrientDB, który z każdą kolejną milą zdawał się jechać coraz wolniej...</p>
	<p>Wielokrotnie zgłaszane były przez nas błedy związane z działaniem tej bazy. Za każdym razem problem był przez jej twórców rozwiązywane, jednak zawsze wiązało się to również z jakmiś skutkami ubocznymi (np. sześciokrotnym wydłużeniem czasu wykonania zapytania).</p> 
	<p>Ostatecznie pomimo wielu problemów i dużej ilości czasu poświęconego na skuteczne przechowywanie i uzyskiwanie danych, udało nam się wypracować rozsądny kompromis &ndash; dane dotyczące struktury sieci pozyskiwane są z bazy grafowej, natomiast pozostałe informacje z bazy relacyjnej, ponieważ (np. w zapytaniu <code>WHERE IN</code>) jest kilka rzędów wielkości szybsza.</p>
	<p>Niestety mimo tych zabiegów wciąż nie byliśmy zadowoleni z wydajności i stabilności. Pobranie struktury z głębokością przeszukiwania w okolicach 4-5 trwało od 20 sekund do nawet minuty (w skrajnych wypadkach kończyło się błędem) i potrzebowało nawet 1GB ramu dla samej bazy OrientDB. W tym momencie wiedzieliśmy, że jest to ślepy zaułek, jednak zostało niewiele czasu do końca semestru, tak więc skupiliśmy się na implementacji funkcjonalności.</p>
	
	<h2>Prace końcowe</h2>
	
	<p>Kolejnym krokiem na drodzę do ukończenia naszego projektu była implementacja następujących algorytmów: </p>
	<ul>
		<li>wyszukiwania najkrotszych scieżek pomiędzy dwoma zadanymi wierzchołkami</li>
		<li>odszukiwanie drzew o podanej głębokości w obszarze zadanego wierzchołka</li>
	</ul>
	<p>Końcowe prace skupiały się na stworzeniu i dopracowywaniu przyjaznego API, które pozwalałoby użytkownikowi na wygodne korzystanie ze wszystkich możliwości oferownych przez naszą aplikację. Przez cały ten czas prowadziliśmy również jej testy związane z funkcjonalnością oraz poprawnością jeśli chodzi o generowane wyniki.</p>
	<p>Niestety interfejs użytkownika jest najmniej dopracowaną częśćią naszej aplikacji. Zaimplementowaliśmy jedynie wycinek z funkcjonalności, które planowaliśmy na początku. Oceniamy, że gdyby nie problemy z bazą OrientDB prace nad interfejsem posunęlibyśmy o wiele dalej.</p>
	
	<h2>Wielki krok &ndash; zastąpienie OrientDB przez NodeDB</h2>
	
	<p>Już po oddaniu projektu na uczelni postanowiliśmy z ciekawości przepisać bazę OrientDB na naszą własną implementację w JavaScriptcie uruchamianym w środowisku NodeJS. NodeJS jest to interpreter JavaScriptu udostępniający stosunkowo niskopoziomowe API do systemu (np. systemu plików, socketów, serwera http). Wykorzystuje on silnik V8, który jest rozwijany m.in. przez Google dla ich przeglądarki Google Chrome, który jest teraz zdecydowanie najszybszym interpreterem JavaScript jaki jest dostępny.</p>
	
	<p>Mimo tego nie byliśmy pewni, czy uda nam się stworzyć implementację lepszą od tej w Javie, ponieważ wydajność języka interpretowanego względem kompilowanego jest mimo wszystko mniejsza. Okazało się jednak, że w niecałe dwa dni byliśmy w stanie napisać własną implementację bazy grafowej o zaskakująco dobrej wydajności. Czas importu spadł z minimum 2 minut, do około 5 sekund. Czas wykonywania się zapytań o strukturę spadł nawet 30 krotnie (mimo że zdecydowaliśmy, że NodeDB będzie dodatkowo specjalnie formatować dane wyjściowe, tak by nie musiał tego jak dotychczas robić PHP), a całej bazie starcza 50MB ramu. Wynik jest więc zaskakująco dobry i pokazuje, jak słabą implementacją jest OrientDB.</p>
	
	<h2>Podsumowanie</h2>
	
	<p>Podczas prac nad aplikacją natrafiliśmy na wiele przeszkód. Od tych związanych z niejasnością relacyjnej bazy danych, którą dostaliśmy jako wejście, przez wiele błędów znalezionych przez nas w bazie OrientDB i jej kiepską wydajnością, aż do trudności chyba najciekawszej &ndash; tak zwanego problemu złośliwego.</p>
	
	<p>Przez większą częścią czasu prac nad aplikacją tak naprawdę nie wiedzieliśmy jaka jest struktura sieci systemów autonomicznych i jakie informacje będą istotne na wizualizacji. Dopiero z wykorzystaniem naszej aplikacji możemy zdefiniować jak powinnien wyglądać program do wizualizacji tych sieci i jakie powinnien mieć funkcjonalności. Tak naprawdę dopiero teraz, na końcu projektu, mamy pomysły jak aplikacja powinna działać, by była wygodnym narzędziem do prowadzenia badań.</p>
	
</section>

</body>
</html>
