<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

// ======= CONFIGURAZIONE =======

// GENERALE
$cnf['sito_nome'] = "Redirect.it";
$cnf['sito_url'] = "http://www.redirect.it"; // non usare slash finale!!!
$cnf['sito_path'] = "/var/www/redirect.it"; // NON mettere la slash finale
$cnf['sito_mail'] = "info@godado.it";

// ADMIN LOGIN
$cnf['admin_usr'] = "giacomo";
$cnf['admin_pwd'] = "godado";

// DB
$cnf['dbuser'] = "redirectuser";
$cnf['dbpass'] = "god\$p2g.k";
$cnf['dbname'] = "redirect-it";
$cnf['dbhost'] = "localhost";

// ADV FRAME
$cnf['advframe_height'] = 50; // valore numerico!
$cnf['advframe_url'] = "http://www.redirect.it/adv_frame.html";

// VARIE
$cnf['lang'] = array("it","en"); // assicurati che esistano i rispettivi file lang_it.php, lang_en.php, ecc.
$cnf['def_lang'] = $cnf['lang'][0]; // linguaggio di default

// ======= FINE CONFIGURAZIONE =======

// CONNESSIONE AL DB
$db = @mysql_connect($cnf['dbhost'], $cnf['dbuser'], $cnf['dbpass']);
@mysql_select_db($cnf['dbname'], $db);

// VERIFICO CHE IL DOMINIO SIA QUELLO PRINCIPALE
if (basename($_SERVER['PHP_SELF']) != "index.php" && strtolower($_SERVER["HTTP_HOST"]) != str_replace('http://','', $cnf['sito_url']))
{
  header("location: " . $cnf['sito_url']);
}

// VARIBILI DI USO COMUNE
$x = 0;
$titolopg = $cnf['sito_nome'];
$invia = false;
if ($_GET['invia'] == "ok") $invia = true;

// INDIVIDUO IL LINGUAGGIO
$lang = $_GET['lang'];
if ($lang && in_array($lang,$cnf['lang']))
{
  setcookie("lang", $lang, time()+(3600*24*365));
}else{
  $lang = $_COOKIE['lang'];
  if (!$lang) $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
}
if (!$lang || !in_array($lang,$cnf['lang'])) $lang = $cnf['def_lang'];
include($cnf['sito_path'] . "/inc/lang_" . $lang . ".php");

// FUNZIONI
include($cnf['sito_path'] . "/inc/func.php");
?>