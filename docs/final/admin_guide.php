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
	<title>ASvis &ndash; Admin guide</title>
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
		<li>Zalecane przynajmniej 2GB pamięcie RAM</li>
	</ul>
</section>

<section id="installation">
	<h1>Instalacja</h1>

	<p>W celu zainstalowania aplikacji należy umieścić ją w wybranym katalogu na serwerze, np. <code>/srv/www/vhosts/asvis</code>.</p>
	<p>Nastepnie należy skonfigurowac virtual host serwera Apache, tak aby document root wskazywal na główny katalog aplikacji. Przykadowa konfiguracja vhosta:</p>
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

	<p><strong>Po skonfigurowaniu vhosta trzeba zrestartowac Apache'a!</strong></p>
	<p>Następnie należy uruchomić bazę danych Orient DB:</p>	
	<pre>$ ./scripts/orient_server.sh</pre>
	<p>lub</p>
	<pre>$ cd db/orientdb/bin$ ./server.sh</pre>
	<p>Następnie należy zainicjalizować baze danych asvis:</p>	
	<pre>$ ./scripts/create_orient_db.sh</pre>
	<p>Ten skrypt tworzy w bazie Orient DB schemat bazy asvis.</p>
</section>
	
<section id="configuration">
	<h1>Konfiguracja</h1>

	<h2>Konfiguracja Apache</h2>
	<p>Należy skonfigurować mod-deflate aby ubsługiwał format application/json:</p>

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
		
	<h2>Konfiguracja Orient DB</h2>
	<p>Ustalene ilości pamięcie RAM dostępnej dla bazy:W pliku db/orientdb/bin/server_prod.sh nalezy zmienić linię 29</p>
	<p>Mało (0.5 GiB)</p>
	<pre>JAVA_OPTS=-Xms512m\ -Xmx512m</pre>
	<p>Średnio (1 GiB)</p>
	<pre>JAVA_OPTS=-Xms1024m\ -Xmx1024m</pre>
	<p>Dużo (2 GiB)</p>
	<pre>JAVA_OPTS=-Xms2048m\ -Xmx2048m</pre>
		
		
	<h2>Konfiguracja aplikacji</h2>
	<p>Aby stworzyć plik konfiguracyjny aplikacji należy zmienić nazwę pliku config.php-- na config.php. Następnie mozna w nim ustawić odpowiednie wartości:</p>

<pre><?= f("
'mysql_db_host'		=> 'localhost',	<- adres URL bazy MySQL z danymi ASów
'mysql_db_name'		=> 'asmap',		<- nazwa bazy MySQL z danymi ASów
'mysql_db_user'		=> 'user',		<- nazwa uzytkownika bazy MySQL z danymi ASów
'mysql_db_pass'		=> 'pass',		<- hasło użytkownika bazy MySQL z danymi ASów
") ?></pre>

</section>    
	
<section id="data">
	<h1>Dane</h1>
	<h2>MySQL</h2>

	<p>Dane w bazie MySQL są przechowywane w formie 4 tabel.</p>
	<p>ASES - lista ASów:</p>
	<table>
		<thead>
			<tr><th>nazwa</th><th>typ</th><th>rola</th></tr>
		</thead>
		<tbody>			
			<tr><td>ASNum</td><td>int</td><td>Numer AS i jednocześnie primary key tabeli</td></tr>
			<tr><td>ASName</td><td>text</td><td>Nazwa AS</td></tr>
		</tbody>
	</table>
	
	<p>ASPOOL - lista pól adresów sieciowych ASów.</p>

	<table>
		<thead>
			<tr><th>nazwa</th><th>typ</th><th>rola</th></tr>
		</thead>
		<tbody>			
			<tr><td>ASNum</td><td>int</td><td>Numer AS i jednocześnie primary key tabeli</td></tr>
			<tr><td>ASNetwork</td><td>text</td><td>Adres IP zapisany jako int</td></tr>
			<tr><td>ASNetmask</td><td>text</td><td>Maska sieciowa ASa (jako liczba bitów)</td></tr>				
		</tbody>
	</table>
	
	<p>ASUP oraz ASDOWN - 2 tabele o tym samym schemacie, przechowują informację o skonfigurowanych UP- i DOWN- streamach na poszczególnych ASach.</p>

	<table>
		<thead>
			<tr><th>nazwa</th><th>typ</th><th>rola</th></tr>
		</thead>
		<tbody>			
			<tr><td>ASNum</td><td>int</td><td>Numer AS na którym skonfigurowany jest up/down stream i jednocześnie primary key tabeli</td></tr>
			<tr><td>ASNumDown/ASNumUp</td><td>int</td><td>Numer AS do którego skonfigurowany jest up/down stream i jednocześnie primary key tabeli</td></tr>
		</tbody>
	</table>

	<p>Aplikacja ASvis wykorzystuje obie bazy danych (MySQL i Orient DB) do działania. Ponieważ jednak dane aktualizowane są tylko w bazie MySQL konieczne jest czasowe synchronizowanie bazy Orient DB z baza MySQL. Do tego celu został przygotowany skrypt <pre>scripts/import_to_orient.sh</pre>. Można go dodać do crontaba aby wykonywał się w określonych odstępach czasu:</p>

	<pre>0 3 * * * sciezka_do_asvis/scripts/import_to_orient.sh</pre>

	<p>Ten wpis spowoduje że każdej nocy o godz. 3.00 zostanie rozpoczęty import z MySQL do Orient DB. <a href="http://en.wikipedia.org/wiki/Cron">Więcej o cronie</a>.</p>
	<p>Import trwa około 5 minut, w zalezności od wydajności serwera. Na czas trwania importu aplikacja wstrzymuje swoje działanie - przy próbie otwarcia strony wyświetla komunikat o niedostepności.</p>
</section>

</body>
</html>
