<?php
include("inc/config.php");
include("inc/auth.php");

if (!$ute_login)
{
  header('location: /home.php');
  exit;
}

$act = $_GET['act'];
if (!$act) $act = "main";

if ($act == "main")
{
  $contenuto = mostra_contenuto("whois") . "<br/>";
  $contenuto .= "<h1>" . $lng['txt_15'] . "</h1>";
  $res = @mysql_query("SELECT * FROM redirect WHERE id_ute = " . $_SESSION['ute_id'] . " ORDER BY terzolivello ASC");
  if (mysql_num_rows($res) == 0)
  {
    $contenuto .= "<p>" . $lng['txt_16'] . "</p>";
  }else{
    $contenuto .= "<table border=\"0\">";
    while ($row = mysql_fetch_array($res))
    {
      $redirect_completo = $row['terzolivello'] . "." . dominio_by_id($row['id_dom']);
      $contenuto .= "<tr>";
      $contenuto .= "<td><a href=\"" . $_SERVER['PHP_SELF'] . "?act=delete&id=" . $row['id'] . "\" onclick=\"return confirm('" . $lng['txt_17'] . "');\"><img border=\"0\" src=\"/img/ico_delete.gif\" alt=\"" . $lng['txt_13'] . "\"/></a></td>";
      $contenuto .= "<td><a href=\"" . $_SERVER['PHP_SELF'] . "?act=edit&id=" . $row['id'] . "\"><img border=\"0\" src=\"/img/ico_edit.gif\" alt=\"" . $lng['txt_14'] . "\"/></a></td>";
      $contenuto .= "<td><a href=\"http://" . $redirect_completo . "\" target=\"_blank\"><img border=\"0\" src=\"/img/ico_view.gif\" alt=\"" . $lng['txt_10'] . "\"/></a></td>";
      $contenuto .= "<td><img border=\"0\" src=\"/img/divisore.gif\"/></td>";
      $contenuto .= "<td><a href=\"http://www.facebook.com/share.php?u=" . urlencode("http://" . $redirect_completo) . "\" target=\"_blank\"><img border=\"0\" src=\"/img/ico_facebook.gif\" alt=\"" . $lng['txt_18'] . " Facebook\"/></a></td>";
      $contenuto .= "<td><a href=\"http://twitter.com/home?status=Visita subito " . urlencode("http://" . $redirect_completo) . "\" target=\"_blank\"><img border=\"0\" src=\"/img/ico_twitter.gif\" alt=\"" . $lng['txt_18'] . " Twitter\"/></a></td>";
      $contenuto .= "<td><a href=\"http://digg.com/submit?url=" . urlencode("http://" . $redirect_completo) . "\" target=\"_blank\"><img border=\"0\" src=\"/img/ico_digg.gif\" alt=\"" . $lng['txt_18'] . " Digg\"/></a></td>";
      $contenuto .= "<td><a href=\"http://del.icio.us/post?url=" . urlencode("http://" . $redirect_completo) . "&title=" . $row['titolo'] . "\" target=\"_blank\"><img border=\"0\" src=\"/img/ico_delicious.gif\" alt=\"" . $lng['txt_18'] . " Delicious\"/></a></td>";
      $contenuto .= "<td><a href=\"http://www.diigo.com/post?url=" . urlencode("http://" . $redirect_completo) . "&title=" . $row['titolo'] . "\" target=\"_blank\"><img border=\"0\" src=\"/img/ico_diigo.gif\" alt=\"" . $lng['txt_18'] . " Diigo\"/></a></td>";
      $contenuto .= "<td><img border=\"0\" src=\"/img/divisore.gif\"/></td>";
      $contenuto .= "<td width=\"300\">" . $redirect_completo . " (" . $row['visite'] . " <i>hits</i>)</td>";
      $contenuto .= "</tr>";
    }
    $contenuto .= "</table>";
  }
}
else if ($act == "whois")
{
  $redirect = $_POST['redirect'];
  if (!$redirect) $redirect = $_GET['redirect'];
  $id_domain = $_POST['id_domain'];
  if (!$id_domain) $id_domain = $_GET['id_domain'];
  // verifico gli errori
  if (!verifica_redirect($redirect)) $errmsg = $lng['err_14'];
  else if (!verifica_redirect_libero($redirect, $id_domain)) $errmsg = str_replace("##REDIRECT##", $redirect_completo, $lng['err_13']);
  if (!$errmsg)
  {
    $contenuto = mostra_contenuto("formredir");
  }else{
    $contenuto = mostra_contenuto("whois");
  }
}
else if ($act == "delete")
{
  $delete_id = $_GET['id'];
  if (!is_numeric($delete_id))
  {
    header('location: /usercp.php');
    exit;
  }else{
    @mysql_query("DELETE FROM redirect WHERE id = " . $delete_id . " AND id_ute = " . $_SESSION['ute_id'] . " LIMIT 1");
    if (mysql_affected_rows())
    {
      $contenuto = big_ok($lng['ok_6']);
    }else{
      $contenuto = big_err($lng['err_20']);
    }
  }
}
else if ($act == "edit")
{
  $edit_id = $_POST['id'];
  if (!$edit_id) $edit_id = $_GET['id'];
  if (!is_numeric($edit_id)) unset($edit_id);
  if (!$edit_id)
  {
    header('location: /usercp.php');
    exit;
  }else{
    $res = @mysql_query("SELECT * FROM redirect WHERE id = " . $edit_id . " AND id_ute = " . $_SESSION['ute_id'] . " LIMIT 1");
    if (mysql_num_rows($res) == 0)
    {
      header('location: /usercp.php');
      exit;
    }else{
      $row = mysql_fetch_assoc($res);
      $redirect = $row['terzolivello'];
      $id_domain = $row['id_dom'];
      $titolo = $row['titolo'];
      $metadesc = $row['metadesc'];
      $metakey = $row['metakey'];
      $url = $row['url'];
      $contenuto = mostra_contenuto("formredir");
    }
  }
}
else if ($act == "redirect")
{
  $edit_id = $_POST['edit_id'];
  if (!$edit_id) $edit_id = $_GET['edit_id'];
  if (!is_numeric($edit_id)) unset($edit_id);

  $redirect = $_POST['redirect'];
  $id_domain = $_POST['id_domain'];
  $titolo = $_POST['titolo'];
  $metadesc = $_POST['metadesc'];
  $metakey = $_POST['metakey'];
  $url = $_POST['url'];

  // verifico gli errori
  if (!$edit_id)
  {
    if (!verifica_redirect($redirect)) $errmsg = $lng['err_14'];
    else if (!verifica_redirect_libero($redirect, $id_domain)) $errmsg = str_replace("##REDIRECT##", $redirect_completo, $lng['err_13']);
  }
  if ($errmsg)
  {
    $contenuto = mostra_contenuto("whois");
  }else{
    if (!$titolo) $errmsg = $lng['err_15'];
    else if (strlen($titolo) < 5 || strlen($titolo) > 50) $errmsg = $lng['err_16'];
    else if (strlen($metadesc) > 100) $errmsg = $lng['err_17'];
    else if (strlen($metakey) > 100) $errmsg = $lng['err_18'];
    else if (!is_url($url)) $errmsg = $lng['err_19'];
    if (!$errmsg)
    {
      // salvo nel DB
      $redirect_completo = $redirect . "." . dominio_by_id($id_domain);
      if (!$edit_id)
      {
        // Nuovo redirect
        @mysql_query("INSERT INTO redirect VALUES ('', " . $_SESSION['ute_id'] . ", " . $id_domain . ", '" . strtolower($redirect) . "', '" . addslashes($titolo) . "', '" . addslashes($metadesc) . "', '" . addslashes($metakey) . "', '" . addslashes($url) . "', 1, 0, 0, 0)");
        $contenuto = big_ok(str_replace("##REDIRECT##", $redirect_completo, $lng['ok_4']));
      }else{
        // Modifico il redirect
        @mysql_query("UPDATE redirect SET titolo =  '" . addslashes($titolo) . "', metadesc =  '" . addslashes($metadesc) . "', metakey =  '" . addslashes($metakey) . "', url =  '" . addslashes($url) . "', attivo = 0 WHERE id = " . $edit_id . " LIMIT 1");
        $contenuto = big_ok(str_replace("##REDIRECT##", $redirect_completo, $lng['ok_5']));
      }
    }else{ 
      $contenuto = mostra_contenuto("formredir");
    }
  }
}
else if ($act == "logout")
{
  session_destroy();
  header("location: /home.php");
}

// Titolo
$titolopg = "User CP - " . $titolopg;

// creo il menu
$menu = "<div class=\"cpmenu\">";
$menu .= "<a href=\"/usercp.php\">CP Home</a> | ";
$menu .= "<a href=\"/usercp.php?act=logout\">Logout</a>";
$menu .= "</div>";

// definisco il contenuto
if ($errmsg) $contenuto = $menu . errore($errmsg) . $contenuto;
else $contenuto = $menu . $contenuto;

// inserisco nel template
$pagina = file_get_contents($cnf['sito_path'] . "/template.html");
$pagina = str_replace("##TITOLO##", $titolopg, $pagina);
$pagina = str_replace("##CONTENUTO##", $contenuto, $pagina);
echo $pagina;

// libero la memoria
unset($contenuto);
unset($pagina);
?>