<?php
include("inc/config.php");

$res = @mysql_query("SELECT * FROM bck_4000 ORDER BY id ASC");
while ($row = mysql_fetch_array($res))
{
  $id_ute = @mysql_result(@mysql_query("SELECT id FROM utenti WHERE email = '" . strtolower($row['email']) . "'"),0,0);
  $id_dom = 15;
  if ($id_ute && $id_dom)
  {
    echo $row['account'] . "." . $row['domain'] . "<br>";
    @mysql_query("INSERT INTO redirect VALUES ('', " . $id_ute . ", " . $id_dom . ", '" . strtolower($row['account']) . "', '" . $row['title'] . "', '" . $row['description'] . "', '" . $row['keywords'] . "', '" . $row['url'] . "',1,0,1)");
  }
}
?>