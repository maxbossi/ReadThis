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

if ($cosa == "U")
{
  @mysql_query("DELETE FROM utenti WHERE id = " . $id . " LIMIT 1");
  @mysql_query("DELETE FROM redirect WHERE id_ute = " . $id);
}else{
  @mysql_query("DELETE FROM redirect WHERE id = " . $id . " LIMIT 1");
}
?>