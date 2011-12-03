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

	<p>W początkowej fazie projektu otrzymaliśmy relacyjną bazę danych, reprezntująca strukturę powiązanych ze sobą systemów autonomicznych. Ze względu na charakter danych jakie mieliśmy przetwarzać, rozpoczęliśmy poszukiwanie odpowiedniej bazy grafowej. Po zapoznaniu się z dokumentacją kilku projektów oraz przeprowadzeniem wstępnych testów zdecydowaliśmy sie na bazę OrientDB.</p>
	<p>Nie wiedzieliśmy wtedy, że pochopnie podjęta decyzja będzie tak fatalnie rzutować na całą naszą pracę. Baza OrientDB pomimo posiadania dość dobrej dokumentacji i będąc ciągle rozwijana, zawiera jeszcze zbyt wiele niedociągnięć by mogła sprostać zadaniu jakie przed nią postawiliśmy</p>
	<p>Drugą zastosowaną przez nas technologią był WebGL, który okazał się dużo mniej problemowy. Ponadto biblioteka ThreeJS pomimo braku dobrej dokumentacji okazała się przyjaznym i skutecznym narzędziem.</p>
	<p>Na etapie projektowania aplikacji zdecydowaliśmy się na jej podział na dwa moduły &ndash; Backend i Frontend. We Frontendzie wizualizowane są dane dostarczane przez Backend za pomocą WebGL. Test wydajności pod przeglądarką Google Chrome pokazał, że technologia ta pozwoli nam na atrakcyjne prezentowanie grafów zawierających do kilkudziesięciu wierzchołków.</p>
	<p>Backend przed przekazaniem odpowiednich zasobów pobiera i przetwarza dane z bazy grafowej. Po zaprojektowaniu jej struktury i migracji rekordów z bazy relacyjnej zaczeła się nasza długa podróż pociągiem OrientDB, który z każdą kolejną milą zdawał się jechać coraz wolniej...</p>
	<p>Wielokrotnie zgłaszane były przez nas błedy związane z działaniem tej bazy. Za każdym razem problem był przez jej twórców rozwiązywany, jednak zawsze wiązało się to również z jakmiś skutkami ubocznymi (np. sześciokrotnym wydłużeniem czasu wykonania zapytania).</p> 
	<p>Ostatecznie pomimo wielu problemów i dużej ilości czasu poświęconego na skuteczne przechowywanie i uzyskiwanie danych, udało się wypracować rozsądny kompromis &ndash; obecnie dane dotyczące struktury sieci pozyskiwane są z bazy grafowej, natomiast pozostałe informacje z bazy relacyjnej.</p>
	<p>Kolejnym krokiem na drodzę do ukończenia naszego projektu była implementacja następujących algorytmów: </p>
	<ul>
		<li>wyszukiwania najkrotszych scieżek pomiędzy dwoma zadanymi wierzchołkami</li>
		<li>odszukiwanie drzew o podanej głębokości w obszarze zadanego wierzchołka</li>
	</ul>
	<p>Końcowe prace skupiały się na stworzeniu i dopracowywaniu przyjaznego API, które pozwalałoby użytkownikowi na wygodne korzystanie ze wszystkich możliwości oferownych przez naszą aplikację. Przez cały ten czas prowadziliśmy również jej testy związane z funkcjonalnością oraz poprawnością jeśli chodzi o generowane wyniki.  
</section>

</body>
</html>
