<?php
include("inc/config.php");
$x = 1;
$res = @mysql_query("SELECT DISTINCT(email) FROM bck_htmx ORDER BY id ASC");
while ($row = mysql_fetch_array($res))
{
  if (@mysql_result(@mysql_query("SELECT COUNT(id) FROM utenti WHERE email = '" . strtolower($row['email']). "'"),0,0) == 0)
  {
    echo $x . ") " .strtolower($row['email']) . "<br>";
    @mysql_query("INSERT INTO utenti VALUES ('', '" . strtolower($row['email']). "', '" . strtolower(makecode($row['email'])) . "', 1, 0, 0)");
    $x++;
  }
}
?>