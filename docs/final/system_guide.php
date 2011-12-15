<html>
<head>
	<meta charset="utf-8">
	<title>ASvis &ndash; System guide</title>
	<link rel="stylesheet" href="main.css">
</head>
<body>

<header>
	<hgroup>
		<h1>ASvis</h1>
		<h2>Dokumentacja</h2>
		<h3>Opis systemu</h3>
	</hgroup>
	
	<nav>
		<h1>Spis treści</h1>
		<ol>
			<li><a href="#modules">Architektura klient-serwer</a></li>
			<li><a href="#nodedb">NodeDB</a></li>
			<li><a href="#backend">Backend</a></li>
			<li><a href="#rest">REST API</a></li>
			<li><a href="#frontend">Frontend</a></li>
		</ol>
		<p class="back"><a href="./">&crarr; Wstecz</a></p>
	</nav>
</header>

<section id="modules">	
	<h1>Architektura klient-serwer</h1>
	
	<p>Aplikacja składa się z dwóch podstawowych części składowych: <a href="#backend">Backendu</a> (czyli aplikacji działającej na serwerze) i <a href="#frontend">Frontendu</a> (aplikacja napisana w JavaScriptcie i działająca w przegladarce internetowej).</p>
	<p><a href="#backend">Backend</a> odpowiedzialny jest za dostarczanie danych <a href="#frontend">Frontendowi</a>. Realizuje to poprzez <a href="#rest">REST-owe API</a>.</p>
	<p><a href="#frontend">Frontend</a> odpowiada za wyświetlanie GUI aplikacji. Żądania użytkownika realizuje wysyłając zapytania do <a href="#backend">Backendu</a>.</p>
</section>

<section id="nodedb">
	<h1>NodeDB</h1>
	
	<p>NodeDB jest to nasza autorska implementacja bazy grafowej. Zastąpiła Javową bazę OrientDB, z której korzystaliśmy na początku. NodeDB wykorzystuje interpreter Node.JS (środowisko wykonawcze dla aplikacji napisanych w JavaScript). Implementacja ta zapewnia bardzo szybki dostęp do struktury grafu i danych o połączeniach wierzchołków. Wydajność względem bazy OrientDB wzorsła ponad 30 krotnie, a zużycie pamięci spadło 20 krotnie.</p>
	
	<p>Podczas uruchomienia baza ta importuje dane z bazy MySQL, tworzy w pamięci ich reprezentację grafową normalizując przy tym strukturę.</p>
	
	<p>Komunikacja z bazą odbywa się przez REST-owe API. Dostępne są dwa zasoby, których odpowiedzi zgodne są z dokumentacją dotyczącą <a href="#rest">REST-owego API stworzonego w PHP</a>:</p>
	
	<ul>
		<li>GET /connections/meta/[num_for]</li>
		<li>GET /structure/graph/[node_number]/[depth]</li>
	</ul>
	
</section>

<section id="backend">
	<h1>Backend</h1>
	
	<p>Backend jest aplikacją działającą po po stronie serwera. Jest to aplikacja napisana w języku PHP. Backend odpytuje bazy danych (MySQL i NodeDB), przetrwarza otrzymane wyniki i realizuje zadania przeszukiwania grafu.</p>
	<p>Backend składa się z trzech pakietów:</p>
	
	<h3>Tonic</h3>
	<p>Tonic jest prostym frameworkiem PHP, umożliwiajacym wygodne stworzenie API REST-owego.</p>
	
	<h3>NodeDB</h3>
	<p>Ten pakiet komunikuje się z bazą NodeDB i przekształca otrzymane wyniki w ich reprezentację obiektową. Wszystkie zapytania i operacja na grafach są obsługiwane przez ten pakiet.</p>
	<p>Najważniejszymi klasami tego pakietu są NodeDBEngine oraz ObjectsMapper. NodeDBsEngine, po odpytaniu bazy danych, przekazuje wynik zapytania do ObjectsMappera który przekształca je do postaci obiektowej. Po działaniach odpowiednich dla danego zasobu REST-owego generowny jest JSON który zwracany jest jako rezultat w REST API.</p>
	<p>Do komunikacji z bazą NodeDB wykorzystywana jest biblioteka nodedb-driver czyli driver do NodeDB dla PHP.</p>

	<h3>MySQL</h3>
	<p>Ten pakiet uzupełnia funkcjonalność bazy grafowej NodeDB o obsługę tych zapytań które są szybciej wykonywane przez bazę relacyjną.</p>
	<p>W tym pakiecie wykorzystywana jest tylko jedna klasa: MySQLEngine. Odpytuje ona bazę MySQL i generuje JSON który zwracany jest jako rezultat w REST API.</p>
	
	<p>Pełna dokumentacja klas wchodzących w skład Backendu znajduje się <a href="phpdocs/index.html">tutaj</a>.</p>
</section>
	
<section id="rest">
	<h1>REST API</h1>
	
	<p>Backend udostępnia zasoby za pomocą RESTowego API.</p>

	<h2>AS-y</h2>			

	<h3>GET /nodes/find/[number]</h3>
	<p>Wyszukuje ASy po numerze, zwraca w kolejności alfabetycznej.</p>
	<ul>
		<li>parametry:
			<ul>
				<li>number [int] - wyszukuje na zasadzie LIKE value%</li>
			</ul>
		</li>
		<li>
			<p>przykład: GET /node/find/345 - wyszukuje wszystkie numery wierzchołków rozpoczynające się od "345"</p>
			<p>odpowiedź:</p>
			<pre><code>{
    "34567": {"name":"AS34567"}
    "34579": {"name":"AS34579"}
    "345": {"name":"AS345"}
}</code></pre>
		</li>
	</ul>

	<h3>POST /nodes/meta</h3>
	
	<p>Podaje metadane ASów o numerach przekazanych w parametrze - pule adresów.</p>
	<ul>
		<li>parametry:
			<ul>
				<li>numbers [str] - przykład: "[1234,2345,52345,234523]"</li>
			</ul>
		</li>
		<li>
			<p>przykład: POST /nodes/meta</p>
			<p>odpowiedź:</p>
			<pre><code>{
    "1234": {"name":"AS1234", "pools":[{"ip":"193.110.32.0","netmask":21}, ...]},
    "4234": {"name":"AS4234", "pools":[{"ip":"198.136.146.0","netmask":12, ...}, ...]},
}</code></pre>
		</li>
	</ul>

	<h2>Struktury</h2>
	<h3>GET /structure/graph/[node_number]/[depth]</h3>
	<p>Podaje strukturę połączeń grafu ASów, dodatkowo listę numerów znalezionych ASów posortowaną wg ilości połączeń oraz listę numerów znalezionych ASów posortowaną wg odległości od ASa źródłowego</p>
	<ul>
		<li>parametry:
			<ul>
				<li>node_number [int]</li>
				<li>depth [int]</li>
			</ul>
		</li>
		<li>
			<p>przykład: POST /structure/graph/345/3 - pobierz strukturę grafu od wierzchołka 345 do 3 połączeń wgłąb</p>
			<p>odpowiedź:</p>
			<pre><code>{
    "structure":{
        "306":{
            "out":[316,317,...,575],
            "in":[316,317,...,575],
            "distance":0,
            "weight":68
        },
        "306":{
            ...
        },
        ...
    },
    "weight_order":[306,575,...,343],
    "distance_order":[306,352,...,1733]
}</code></pre>
		</li>
	</ul>

	<h3>GET /structure/tree/[node_number]/[height]/[dir]</h3>
	<p>Pobiera strukturę drzewa od ASa o podanym numerze o danej wysokości i kierunku. Patrz rozdział "Wyszukiwanie drzew" w podręczniku użytkownika.</p>
	<ul>
		<li>parametry:
			<ul>
				<li>node_number [int]</li>
				<li>height [int]</li>
				<li>dir [string] - przyjmowane wartości: "in", "out", "both"</li>
			</ul>
		</li>
		<li>
			<p>przykład: GET /structure/tree/306/2/out - zwraca strukturę nodów, do których download danych spoza struktury odbywa się jedynie poprzez główny wierzchołek</p>
			<p>odpowiedź:</p>
			<pre><code>{
    "structure":{
        "306":{
            "out":[316,317,...,575],
            "in":[316,317,...,575],
            "distance":0,
            "weight":68
        },
        "306":{
            ...
        },
        ...
    },
    "weight_order":[306,575,...,343],
    "distance_order":[306,352,...,1733]
}</code></pre>
		</li>
	</ul>
	
	<h3>GET /structure/path/[num_start]/[num_end]/[dir]</h3>
	<p>Podaj ścieżkę pomiędzy podanym ASem startowym i docelowym szukając w zadanym kierunku.</p>
	<ul>
		<li>params:
			<ul>
				<li>node_start [int]</li>
				<li>num_stop [int]</li>
				<li>dir [string] - przyjmowane wartości: "up", "down", "both"</li>
			</ul>
		</li>
		<li>
			<p>przykład: GET /structure/paths/306/27066/both - zwraca tablicę zawierającą struktury node'ów, które reprezentują najkrótsze znalezione ścieżki połączeń "up" i "down" od noda końcowego do początkowego.</p>
			<p>odpowiedź:</p>
			<pre><code>{
	"paths":[[306,575,27064,...,27066]],
	"depth_left":3,
	"depth_right":2
}</code></pre>
		</li>
	</ul>

	<h2>Połączenia</h2>
	<h3>GET /connections/meta/[num_for]</h3>
	<p>Wyszukuje informacje na temat połączeń podanego wierzchołka.</p>
	<ul>
		<li>parametry:
			<ul>
				<li>num_for [int]</li>
			</ul>
		</li>
	
		<li>
			<p>przykład: GET /connections/meta/345 - wyszukuje informacje na temat połączeń wierzchołka "345"</p>
			<p>odpowiedź:</p>
			<pre><code>[
	{"with":306,"status":0,"dir":"up"},
	....
]			</code></pre>
		</li>
	</ul>
</section>
	
<section id="frontend">
	<h1>Frontend</h1>

	<p>Aplikacja frontendowa jest w napisana w JavaScriptcie (wersja ECMAScript 5) i wykorzystuje HTML5 + CSS3 do prezentacji. Podstawową ideą jest oparcie o HTML5 history w celu ograniczenia do jednego (inicjalnego) załadowania strony, by następne zmiany "podstron" prowadzić już w obrębie jednego stanu. W tym celu zostały wykorzystane zewnętrzne bibliteki oraz autorska implementacja dispatchera.</p>

	<p>Wykorzystane zewnętrzne moduły:</p>

	<ul>
		<li><a href="https://github.com/mrdoob/three.js/">Three.js</a> &ndash; JavaScript 3D Engine</li>
		<li><a href="http://millermedeiros.github.com/js-signals/">js-signals</a> &ndash; implementacja sygnałów</li>
		<li><a href="http://millermedeiros.github.com/crossroads.js/">Crossroads.js</a> &ndash; router</li>
		<li><a href="http://embeddedjs.com/">EJS</a> &ndash; system template'owy</li>
		<li><a href="http://xuijs.com/">xui.js</a> &ndash; lekka biblioteka do DOM-a</li>
		<li><a href="https://github.com/medikoo">pakiety wspierające autorstwa Mariusza Nowaka</a> &ndash; es5ext (rozszerzenie ES5), deferred (wsparcia dla pracy z asynchronicznymi wywołaniami), modules-webmake (opakowywanie modułów CommonJS do wersji zgodnej z ES5).</li>
	</ul>

	<p>Architektura:</p>

	<ul>
		<li><code>app.js</code> &ndash; główny obiekt aplikacji &ndash; scalający podstawowe kompomenty</li>
		<li><code>util.js i xui_extends.js</code> &ndash; pomocne helpery</li>
		<li><code>routes.js</code> &ndash; definicje routingu URL-i</li>
		<li><code>lib/dispatcher_adapter.js</code> &ndash; delegacja kliknięć w linki oraz wysłań formularzy i skierowanie ich do wewnętrznego routingu</li>
		<li><code>lib/resources/</code> &ndash; zasoby REST-owego API</li>
		<li><code>lib/stores/</code> &ndash; "magazyny danych" (obecnie dwa &ndash; <code>remote</code>, czyli zewnętrzny zasób pobieranych przez XHR oraz <code>local</code>, czyli cache)</li>
		<li><code>lib/widgets/</code> &ndash; implementacja architektury opartej o widgety; widgety skłają się z części właściwej oraz widoku (+ zewnętrzne template'y)</li>
		<li><code>lib/fba.js</code> &ndash; implementacja forced based algorithm (algorytmu wykorzystującego siły odpychania do rozlokowania wierzchołków grafu)</li>
		<li><code>lib/gods_finger.js, lib/camera_man.js, lib/renderer.js</code> &ndash; renderowanie 3D, obsługa kamery, obiektów itp.</li>
	</ul>
</section>

</body>
</html>
