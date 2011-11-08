<?php

// config
use asvis\Config as Config;
require_once __DIR__ . '/../config.php';

if (Config::get('env') === 'dev') {
	// Report all PHP errors (see changelog)
	error_reporting(E_ALL | E_STRICT);
	ini_set('display_errors', 1);
}

function getSubdirFiles($main_dir) {
	$result = array();

	$dirs = scandir($main_dir); 
	foreach ($dirs as $dir)  { 
		$sub_dir = $main_dir .'/'. $dir;
		if ($dir === '.' || $dir === '..') { 
			continue;
		} 

		if (is_file($sub_dir)) {
			$result[] = $sub_dir;
		}
		else {
			$files = scandir($sub_dir); 
			foreach ($files as $file)  { 
				if ($file === '.' || $file === '..') { 
					continue; 
				}
				else { 
					$result[] = $sub_dir .'/'. $file; 
				} 
			}
		} 
	}    
	return $result; 
}

function includeJS($paths) {
	foreach ($paths as $path) {
		printf('<script src="%s/js/%s"></script>', Config::get('frontend_base_uri'), $path);
	}
}

function includeTemplates() {
	$dir = __DIR__ . '/templates';
	
	$files = getSubdirFiles($dir);
	foreach ($files as $file) {
		$name = str_replace($dir.'/', '', $file);
		$name = substr($name, 0, strrpos($name, '.'));
		echo '<script type="text/ejs" class="template" data-name="'. $name .'">';
		readfile($file);
		echo '</script>';
	}
}


?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>ASvis</title>
	<link rel="stylesheet" href="<?= Config::get('frontend_base_uri') ?>/css/init.css">
	<link rel="stylesheet" href="<?= Config::get('frontend_base_uri') ?>/css/components.css">
	<link rel="stylesheet" href="<?= Config::get('frontend_base_uri') ?>/css/layout.css">
</head>
<body id="container">
	<header id="top">
		<h1><a href="/"><strong>AS</strong>vis</a></h1>
	</header>
	<p id="flash"><span class="message"></span></p>
	<div id="graph">
		<div id="graph_renderer"></div>
	</div>
	<aside id="sidebar">
		<section id="node_data" class="section l1">
			<h1>Node <span class="as_num">#455</span></h1>
			<div class="content with_subs">
				<div class="padded_content">
					<p class="name">Nazwa: <span class="as_name">AS455</span></p>
				</div>
		
				<section id="node_data_conns" class="section l2">
					<h1>Połączenia (<span class="count">235 &ndash; U:123, D:145</span>)</h1>
					<div class="content scroll">
						<table class="std">
							<thead>
								<tr>
									<th>U/D</th>
									<th>Do</th>
									<th>Stan</th>
								</tr>
							</thead>
							<tbody>
								<tr class="ok up">
									<td class="as_dir_up">UP</td>
									<td><span class="as_num">#348543</span></td>
									<td class="as_conn_ok">OK</td>
								</tr>
								<tr class="ok up">
									<td class="as_dir_up">UP</td>
									<td><span class="as_num">#348543</span></td>
									<td class="as_conn_ok">OK</td>
								</tr>
								<tr class="ok up">
									<td class="as_dir_up">UP</td>
									<td><span class="as_num">#348543</span></td>
									<td class="as_conn_ok">OK</td>
								</tr>
								<tr class="ok up">
									<td class="as_dir_up">UP</td>
									<td><span class="as_num">#348543</span></td>
									<td class="as_conn_ok">OK</td>
								</tr>
								<tr class="ok down">
									<td class="as_dir_down">DOWN</td>
									<td><span class="as_num">#348543</span></td>
									<td class="as_conn_ok">OK</td>
								</tr>
								<tr class="ok down">
									<td class="as_dir_down">DOWN</td>
									<td><span class="as_num">#348543</span></td>
									<td class="as_conn_ok">OK</td>
								</tr>
								<tr class="ok down">
									<td class="as_dir_down">DOWN</td>
									<td><span class="as_num">#348543</span></td>
									<td class="as_conn_ok">OK</td>
								</tr>
								<tr class="bad up">
									<td class="as_dir_up">UP</td>
									<td><span class="as_num">#348543</span></td>
									<td class="as_conn_bad with_hint" title="Brak połączenia w węźle docelowym">BAD</td>
								</tr>
								<tr class="bad up">
									<td class="as_dir_up">UP</td>
									<td><span class="as_num">#348543</span></td>
									<td class="as_conn_bad with_hint" title="Brak połączenia w węźle docelowym">BAD</td>
								</tr>
							</tbody>
						</table>
					</div>
				</section>

				<section id="node_data_pools" class="section l2">
					<h1>Pule adresów (<span class="count">23</span>)</h1>
					<div class="content scroll">
						<table class="std">
							<thead>
								<tr>
									<th>IP</th>
									<th>CIDR</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td><span class="aspool_ip">134.35.53.34</span></td>
									<td><span class="aspool_cidr">4</span></td>
								</tr>
								<tr>
									<td><span class="aspool_ip">134.35.53.34</span></td>
									<td><span class="aspool_cidr">4</span></td>
								</tr>
								<tr>
									<td><span class="aspool_ip">134.35.53.34</span></td>
									<td><span class="aspool_cidr">4</span></td>
								</tr>
								<tr>
									<td><span class="aspool_ip">134.35.53.34</span></td>
									<td><span class="aspool_cidr">4</span></td>
								</tr>
								<tr>
									<td><span class="aspool_ip">134.35.53.34</span></td>
									<td><span class="aspool_cidr">4</span></td>
								</tr>
								<tr>
									<td><span class="aspool_ip">134.35.53.34</span></td>
									<td><span class="aspool_cidr">4</span></td>
								</tr>
								<tr>
									<td><span class="aspool_ip">134.35.53.34</span></td>
									<td><span class="aspool_cidr">4</span></td>
								</tr>
							</tbody>
						</table>
					</div>
				</section>
			</div>
		</section>
	</aside>
	
	<? includeJS(array(
		'../data/sample_1.js',
		'../data/sample_2.js',
		'../data/sample_3.js',
		'vendor/three.js',
		'vendor/signals.js',
		'vendor/crossroads.js',
		'vendor/xui.js',
		'vendor/cjs_exports_webmade.js',
		'vendor/ejs.js',
		'xui_extends.js',
		'app.js',
		'util.js',
		'lib/dispatcher_adapter.js',
		'lib/xhr_adapter_xui.js',
		'lib/local_db.js',
		'lib/flash.js',
		'lib/templates.js',
		'lib/vizir.js',
		'lib/camera_man.js',
		'lib/renderer.js',
		'lib/stores/store.js',
		'lib/stores/remote_store.js',
		'lib/resources/resource.js',
		'lib/resources/nodes_resource.js',
		'lib/resources/structures_resource.js',
		'lib/widgets/widget.js',
		'lib/widgets/start_form_widget.js',
		'lib/widgets/graph_widget.js',
	)) ?>
	<? includeTemplates() ?>
	<script>
		this.DEBUG = false; // <?= Config::get('env') === 'dev' ? 'true' : 'false' ?>;
		app.start({
			root: '<?= Config::get('frontend_base_uri') ?>/'
		});
	</script>
</body>
</html>
