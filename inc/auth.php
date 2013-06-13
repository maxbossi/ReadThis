<?php
// avvio la sessione
session_start();

// variabile di default
$ute_login = false;

// verifico se l'utente è loggato
if ($_SESSION['ute_id'] && $_SESSION['ute_pwd'] && is_numeric($_SESSION['ute_id']) && is_password($_SESSION['ute_pwd']))
{
  $test = @mysql_result(@mysql_query("SELECT COUNT(id) FROM utenti WHERE id = " . $_SESSION['ute_id'] . " AND pass = '" . $_SESSION['ute_pwd'] . "'"),0,0);
  if ($test) $ute_login = true;
}
?>