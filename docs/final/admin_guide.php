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
	<title>ASvis &ndash; Podręcznik administratora</title>
	<link rel="stylesheet" href="main.css">
</head>
<body>

<header>
	<hgroup>
		<h1>ASvis</h1>
		<h2>Dokumentacja</h2>
		<h3>Podręcznik administratora</h3>
	</hgroup>

	<nav>
		<h1>Spis treści</h1>
		<ol>
			<li><a href="#requirements">Wymagania</a></li>
			<li><a href="#installation">Instalacja</a></li>
			<li><a href="#configuration">Konfiguracja</a></li>
			<li><a href="#run">Uruchomienie</a></li>
			<li><a href="#data">Dane</a></li>
		</ol>
	</nav>
</header>

<section id="requirements">
	<h1>Wymagania</h1>
	<ul>
		<li>Linux</li>
		<li>JRE (Java Runtime Environment) &gt;= 1.6</li>
		<li>Apache &gt;= 2.2 z włączonycm mod-deflate</li>
		<li>PHP &gt;= 5.3</li>
		<li>MySQL</li>
		<li>Node.JS &gt;= 0.6.x</li>
		<li>Zalecane przynajmniej 512MB pamięci RAM</li>
	</ul>
</section>

<section id="installation">
	<h1>Instalacja</h1>

	<p>W celu zainstalowania aplikacji należy umieścić ją w wybranym katalogu na serwerze, np. <code>/srv/www/vhosts/asvis</code>.</p>
	<p>Nastepnie należy skonfigurowac virtual host serwera Apache, tak aby document root wskazywał na główny katalog aplikacji. Przykadowa konfiguracja virtual hosta:</p>
<pre><?= f("
<VirtualHost asvis.local.pl:80>
	ServerName asvis.local.pl
	DocumentRoot /srv/www/vhosts/asvis/
	DirectoryIndex index.php
	
	<Directory />
		Options FollowSymLinks
		AllowOverride All
	</Directory>
	
	<Directory /srv/www/vhosts/asvis/>
		AllowOverride All
		allow from all
	</Directory>	
</VirtualHost>
") ?></pre>
	
	<p><strong>Po skonfigurowaniu virtual hosta trzeba zrestartowac Apache'a!</strong></p>
</section>
	
<section id="configuration">
	<h1>Konfiguracja</h1>

	<h2>Konfiguracja Apache</h2>
	<p>Należy skonfigurować <code>mod-deflate</code> aby ubsługiwał format <code>application/json</code>:</p>

<pre><?= f("
<IfModule mod_deflate.c>
	AddOutputFilterByType DEFLATE text/html text/plain text/xml
	AddOutputFilterByType DEFLATE text/css
	AddOutputFilterByType DEFLATE application/x-javascript application/javascript application/ecmascript
	AddOutputFilterByType DEFLATE application/rss+xml
	AddOutputFilterByType DEFLATE application/json
</IfModule>
") ?></pre>

	<p>Powyższy wpis można dodać w <code>deflate.conf</code> lub w <code>.htaccess</code>.</p>
	<p>Warto też zmienić dostępną ilość pamięci dla PHP (<code>memory_limit</code>) do minimum 256 MB oraz wydłużyć maksymalny czas wykonania (<code>max_execution_time</code>) do 60 sekund.</p>
		
	<h2>Konfiguracja grafowej bazy danych NodeDB</h2>

	<p>Aby stworzyć plik konfiguracyjny bazy danych należy w katalogu bazy (<code>db/nodedb/</code>) zmienić nazwę pliku <code>config.js--</code> na <code>config.js</code>. Następnie należy w nim ustawić odpowiednie wartości:</p>
	
<pre><?= f("
mysql: {
	host:       'localhost',    <- adres URL bazy MySQL z danymi ASów
	name:       'asmap',        <- nazwa bazy MySQL z danymi ASów
	user:       'user',         <- nazwa uzytkownika bazy MySQL z danymi ASów
	password:   'pass',         <- hasło użytkownika bazy MySQL z danymi ASów
}
") ?></pre>
		
	<h2>Konfiguracja aplikacji</h2>
	<p>Aby stworzyć plik konfiguracyjny aplikacji należy zmienić nazwę pliku <code>config.php--</code> na <code>config.php</code>. Następnie należy w nim ustawić odpowiednie wartości:</p>

<pre><?= f("
'mysql_db_host'		=> 'localhost', <- adres URL bazy MySQL z danymi ASów
'mysql_db_name'		=> 'asmap',     <- nazwa bazy MySQL z danymi ASów
'mysql_db_user'		=> 'user',      <- nazwa uzytkownika bazy MySQL z danymi ASów
'mysql_db_pass'		=> 'pass',      <- hasło użytkownika bazy MySQL z danymi ASów
") ?></pre>

</section>

<section id="run">
	<h1>Uruchomienie aplikacji</h1>
	
	<p>Aby uruchomić aplikację należy włączyć serwery Apache, MySQL oraz bazę NodeDB. Aby uruchomić bazę NodeDB można skorzystać ze skryptu:</p>
	
	<pre>$ ./scripts/start_nodedb.sh</pre>
	
	<p>Użyć można również skryptów do odpowiednio wyłączenia bazy oraz jej zrestartowania:</p>
	
<pre>$ ./scripts/stop_nodedb.sh
$ ./scripts/restart_nodedb.sh</pre>

</section>
	
<section id="data">
	<h1>Dane</h1>
	<h2>MySQL</h2>

	<p>Dane w bazie MySQL są przechowywane w formie 4 tabel.</p>
	<p><code>ases</code> &ndash; lista AS-ów. Ta tabel zawiera wszyskie znane AS-y.</p>
	<table>
		<thead>
			<tr><th>nazwa</th><th>typ</th><th>rola</th></tr>
		</thead>
		<tbody>			
			<tr><td>ASNum</td><td>int</td><td>Numer AS i jednocześnie primary key tabeli</td></tr>
			<tr><td>ASName</td><td>text</td><td>Nazwa AS</td></tr>
		</tbody>
	</table>
	
	<p><code>aspool</code> &ndash; lista pól adresów sieciowych AS-ów. Każdemu wpisowi w ASES zazwyczaj odpowiada kilka w ASPOOL.</p>

	<table>
		<thead>
			<tr><th>nazwa</th><th>typ</th><th>rola</th></tr>
		</thead>
		<tbody>			
			<tr><td>ASNum</td><td>int</td><td>Numer AS i jednocześnie primary key tabeli</td></tr>
			<tr><td>ASNetwork</td><td>int</td><td>Adres IP zapisany jako int *</td></tr>
			<tr><td>ASNetmask</td><td>int</td><td>Maska sieciowa ASa (liczba bitów)</td></tr>				
		</tbody>
	</table>
	<p>* wartość  adresu IP jako integer jest wyliczana ze wzoru: ASNetwork = (l1*16777216) + (l2*65536) + (l3*256) + (l4) gdzie adres IP to l1.l2.l3.l4.</p>
	
	<p><code>asup</code> oraz <code>asdown</code> &ndash; 2 tabele o tym samym schemacie, przechowują informację o skonfigurowanych UP- i DOWN- streamach na poszczególnych AS-ach. </p>
	<table>
		<thead>
			<tr><th>nazwa</th><th>typ</th><th>rola</th></tr>
		</thead>
		<tbody>			
			<tr><td>ASNum</td><td>int</td><td>Numer AS na którym skonfigurowany jest up/down stream i jednocześnie primary key tabeli</td></tr>
			<tr><td>ASNumDown/ASNumUp</td><td>int</td><td>Numer AS do którego skonfigurowany jest up/down stream i jednocześnie primary key tabeli</td></tr>
		</tbody>
	</table>

	<p>Aplikacja ASvis wykorzystuje obie bazy danych (MySQL i bazę grafową NodeDB) do działania. Ponieważ jednak dane aktualizowane są tylko w bazie MySQL konieczne jest czasowe synchronizowanie bazy NodeDB z baza MySQL. W tym celu można użyć skryptu restartującego bazę NodeDB: <code>scripts/restart_nodedb.sh</code>. Można go dodać do crontaba aby wykonywał się w określonych odstępach czasu:</p>

	<pre>0 3 * * * sciezka_do_asvis/scripts/restart_nodedb.sh</pre>

	<p>Ten wpis spowoduje, że każdej nocy o godz. 3.00 zostanie rozpoczęty import z MySQL do NodeDB. <a href="http://en.wikipedia.org/wiki/Cron">Więcej o cronie</a>.</p>
</section>

</body>
</html>
