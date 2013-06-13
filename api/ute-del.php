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
  $uteres = @mysql_query("SELECT id FROM utenti WHERE email = '" . $email . "' LIMIT 1");
  if (mysql_num_rows($uteres) > 0)
  {
    $uterow = mysql_fetch_row($uteres);
    $uteid = $uterow[0];
    @mysql_query("DELETE FROM utenti WHERE id = " . $uteid . " LIMIT 1");
    @mysql_query("DELETE FROM redirect WHERE id_ute = " . $uteid);
    echo 1;
  }else{
    echo 0;
  } 
}else{
  echo 0;
}
?>