<?php
include("inc/config.php");
include("inc/auth.php");

$act = $_GET['act'];
if (!$act) $act = "alpha";

if ($act == "alpha")
{
  $lt = $_GET['lt'];
  if ($lt != "09" && !eregi("^[a-z]{1}$", $lt)) $lt = "a";

  $pg = $_GET['pg'];
  if (!is_numeric($pg) || $pg <= 0) $pg = 1;

  if ($lt == "09") $sqlwhere = "titolo REGEXP '^[0-9]{1}'";
  else $sqlwhere = "LEFT(titolo,1) = '" . $lt . "'";

  $rs_x_pg = 50;
  $tot_rs = @mysql_result(@mysql_query("SELECT COUNT(id) FROM redirect WHERE " . $sqlwhere . " AND attivo = 1 AND bannato = 0"),0,0);
  $tot_pg = ceil($tot_rs / $rs_x_pg);
  if ($tot_pg == 0) $tot_pg = 1;
  if ($pg > $tot_pg) $pg = $tot_pg;
  $start = ($pg - 1) * $rs_x_pg;

  $res = @mysql_query("SELECT * FROM redirect WHERE " . $sqlwhere . " AND attivo = 1 AND bannato = 0 ORDER BY titolo ASC LIMIT " . $start . ", " . $rs_x_pg);
  $contenuto = "<h1>" . $lng['txt_29'] . " &gt; " . strtoupper($lt) . "</h1>";
  $alpharr = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
  $contenuto .= "<p><a href=\"" . $_SERVER['PHP_SELF']. "?lt=09\">0-9</a>";
  foreach ($alpharr as $lett)
  {
    $contenuto .= " | <a href=\"" . $_SERVER['PHP_SELF']. "?lt=" . $lett . "\">" . strtoupper($lett) . "</a>";
  }
  $contenuto .= "</p>";

  // Avviso
  $contenuto .= "<p style=\"color:#FF0000; font-weight:bold\">" . $lng['pulizia'] . "</p>";

  while ($row = mysql_fetch_array($res))
  {
    $contenuto .= "<div class=\"redir\"><a href=\"http://" . $row['terzolivello'] . "." . dominio_by_id($row['id_dom']) . "\" target=\"_blank\">" . $row['titolo'] . "</a><br/>" . $row['metadesc'] . "</div>";
  }

  if ($tot_pg > 1)
  {
    $contenuto .= "<p>Pag: ";
    for ($i=1; $i<=$tot_pg; $i++)
    {
      if ($i > 1) $contenuto .= " | ";
      if ($i == $pg) $contenuto .= "<b>" . $i . "</b>";
      else $contenuto .= "<a href=\"" . $_SERVER['PHP_SELF']. "?lt=" . $lt . "&pg=" . $i . "\">" . $i . "</a>";
    }
    $contenuto .= "</p>";
  }
}
elseif ($act == "top")
{
  $res = @mysql_query("SELECT * FROM redirect ORDER BY visite WHERE attivo = 1 AND bannato = 0 DESC LIMIT 50");
  $contenuto = "<h1>" . $lng['txt_28'] . "</h1>";
  while ($row = mysql_fetch_array($res))
  {
    $contenuto .= "<div class=\"redir\"><a href=\"http://" . $row['terzolivello'] . "." . dominio_by_id($row['id_dom']) . "\" target=\"_blank\">" . $row['titolo'] . "</a></div>";
  }
}

// inserisco nel template
$pagina = file_get_contents($cnf['sito_path'] . "/template.html");
$pagina = str_replace("##TITOLO##", $titolopg, $pagina);
$pagina = str_replace("##CONTENUTO##", $contenuto, $pagina);
echo $pagina;

// libero la memoria
unset($contenuto);
unset($pagina);
?>