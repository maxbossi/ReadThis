<?php
// API di collegamento con GodadoMail
// verifica se un dato indirizzo email è registrato
// 1 = sì
// 0 = no

// includo il file di config
include("../inc/config.php");

// includo il config delle API
include("apiconf.php");

// recupero la querystring
$pwd = $_GET['pwd'];
$email = $_GET['email'];

// faccio dei controlli
if (!api_test_email($email)) unset($email);
if ($pwd != $api['pwd']) unset($pwd);

if ($email && $pwd)
{
  $res = @mysql_query("SELECT COUNT(id) FROM utenti WHERE email = '" . $email ."' LIMIT 1");
  $row = mysql_fetch_row($res);
  if ($row[0] > 0) echo 1; else echo 0; 
}else{
  echo 0;
}
?>