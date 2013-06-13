<?php
include("inc/config.php");

$res = @mysql_query("SELECT DISTINCT(email) FROM banneruser ORDER BY id ASC");
while ($row = mysql_fetch_array($res))
{
  echo strtolower($row['email']) . "<br>";
  if (@mysql_result(@mysql_query("SELECT COUNT(id) FROM utenti WHERE email = '" . strtolower($row['email']). "'"),0,0) == 0)
  {
    @mysql_query("INSERT INTO utenti VALUES ('', '" . strtolower($row['email']). "', '" . strtolower(makecode($row['email'])) . "', 1, 0, 0)");
  }
}
?>