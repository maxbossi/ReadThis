<?php
include("inc/config.php");

session_start();
$admin_usr = $_SESSION['admin_usr'];
$admin_pwd = $_SESSION['admin_pwd'];
if ((!$admin_usr || !$admin_pwd) || ($admin_usr != $cnf['admin_usr'] || $admin_pwd != $cnf['admin_pwd'])) exit;

$cosa = $_GET['c'];
if ($cosa != "U" && $cosa != "R") exit;

$id = $_GET['id'];
if (!is_numeric($id)) exit;

if ($cosa == "U") $tbl = "utenti"; else $tbl = "redirect";
$res = @mysql_query("SELECT attivo FROM " . $tbl . " WHERE id = " . $id . " LIMIT 1");
if (mysql_num_rows($res) == 0)
{
  exit;
}else{
  $row = mysql_fetch_row($res);
  $attivo = $row[0];
  if ($attivo == 1) $nuovostato = 0; else $nuovostato = 1;
  @mysql_query("UPDATE " . $tbl . " SET attivo = " . $nuovostato . " WHERE id = " . $id . " LIMIT 1");
  // se attivo un redirect mando la mail di conferma
  if ($cosa == "R" && $nuovostato == 1)
  {
    $res = @mysql_query("SELECT utenti.email, redirect.terzolivello, redirect.id_dom FROM utenti INNER JOIN redirect ON utenti.id = redirect.id_ute WHERE redirect.id = " . $id . " LIMIT 1");
    if (mysql_num_rows($res) > 0)
    {
      $row = mysql_fetch_assoc($res);
      $utemail = $row['email'];
      $uteredirect = "http://" . $row['terzolivello'] . "." . dominio_by_id($row['id_dom']);

      // invio la mail
      $emailbody = $lng['mail_body_3'];;
      $emailbody = str_replace("##REDIRECT##", $uteredirect, $emailbody);
      invia_mail($utemail, $lng['mail_subject_3'], $emailbody);
    }
  }
}
?>