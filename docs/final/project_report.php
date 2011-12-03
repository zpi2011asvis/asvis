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
</header>

<section> 
	<h1>Nasza historia</h1>

	<p>W początkowej fazie projektu otrzymaliśmy relacyjną baze reprezntująca strukturę powiązanych ze sobą sieci autonomicznych</p> 
	<p>Ze względu na charakter danych jakie mieliśmy przetwarzać, rozpoczęliśmy poszukiwanie odpowiedniej bazy grafowej. Po zapoznaniu się z dokumentacją kilku projektów oraz przeprowadzeniem wstępnych testów zdecydowaliśmy sie na bazę OrientDB.</p>
	<p>Nie wiedzieliśmy wtedy, że pochopnie podjęta decyzja będzie fatalnie rzutować na całą naszą pracę. 
	<p>Baza OrientDB pomimo posiadania dość dobrej dokumentacji i będąc ciągle rozwijana, zawiera jeszcze zbyt wiele niedociągnięć by mogła sprostać zadaniu jakie przed nią postwaliśmy</p>
	<p>Drugą zastosowaną przez nas technologią był WebGL, który okazał dużo mniej problemowy. Ponadto biblioteka ThreeJS pomimo braku dobrej dokumentacji okazała się wygodnym i skutecznym narzędziem.</p>
	<p>Na etapie projektowania aplikacji zdecydowaliśmy się na jej podział na dwa moduły &ndash; Backend i Frontend. Frontend to moduł zajmujący się wyświetlaniem danych z dostarczonych brzez Backend</p>
	<p>We frontendzie dane wizualizowane są za pomocą WebGL. Test wydajności pod przeglądarką Google Chrome pokazał, że technologia ta pozwoli nam atrakcyjne prezentowanie grafu zawierającego do kilkudziesięciu wierzchołków.</p>
	<p>Backend przed przekazaniem odpowiednich zasobów pobiera i przetwarza dane z bazy OrientDB</p>
	<p>Po zaprojektowaniu struktury i migracji rekordów do bazy grafowej zaczeła się nasza długa podróż pociągiem OrientDB, który z każdą kolejną milą i stacją zdawał się jechać coraz wolniej</p>
	<p>Wielokrotnie zgłaszane były przez nas błędy z działaniem bazy. Za każdym razem problem był rozwiązywany przez twórców jednak z bardzo różnymi skutkami (np. spadek wydajności obsługi zapytań o 10%)</p> 
	<p>Ostatecznie pomimo wielu problemów udało się zrealizować w miarę wygodny interfejs po stronie frontendu oraz wykorzystywane przez niego algorytmy po stronie backendu:</p>
	<ul>
		<li>Wyszukiwanie najkrotszych scieżek pomiędzy dwoma zadanymi wierzchołkami</li>
		<li>Odszukiwanie drzew o podanej głębokości w obszarze zadanego wierzchołka</li>
	<ul>
</section>

</body>
</html>