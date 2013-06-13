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
$res = @mysql_query("SELECT bannato FROM " . $tbl . " WHERE id = " . $id . " LIMIT 1");
if (mysql_num_rows($res) == 0)
{
  exit;
}else{
  $row = mysql_fetch_row($res);
  $bannato = $row[0];
  if ($bannato == 1) $nuovostato = 0; else $nuovostato = "1, attivo = 1";
  @mysql_query("UPDATE " . $tbl . " SET bannato = " . $nuovostato . " WHERE id = " . $id . " LIMIT 1");
  if ($cosa == "U") @mysql_query("UPDATE redirect SET bannato = " . $nuovostato . " WHERE id_ute = " . $id);
}
?>