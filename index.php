<?php
include("inc/config.php");

// variabile interna
$usotmpl = 0;

// recupero la URL
$url = strtolower($_SERVER["HTTP_HOST"]);

// suddivo la URL
$arrurl  = explode(".", $url);

// elimino il www se presente
if ($arrurl[0] == "www") array_shift($arrurl);

// conto gli elementi dell'array (devono essere 3)
if (count($arrurl) < 3)
{
  header("location: " . $cnf['sito_url'] . "/home.php");
  exit;
}else{
  // recupero il nome del 3 livello
  $terzo_livello = $arrurl[0];

  // recupero l'ID del dominio di riferimento
  $id_dominio = id_by_dominio($arrurl[1] . "." . $arrurl[2]);

  $res = @mysql_query("SELECT id, url, titolo, metadesc, metakey, adv, attivo, bannato FROM redirect WHERE id_dom = " . $id_dominio . " AND terzolivello = '" . $terzo_livello. "' LIMIT 1");
  if (mysql_num_rows($res) == 0)
  {
    $usotmpl = 1;
    $contenuto = $lng['err_9'];
  }else{
    $row = mysql_fetch_assoc($res);

    if ($row['attivo'] == 0)
    {
      $usotmpl = 1;
      $contenuto = $lng['err_22'];
    }
    else if ($row['bannato'] == 1)
    {
      $usotmpl = 1;
      $contenuto = $lng['err_23'];
    }else{
      // aggiorno le visite
      @mysql_query("UPDATE redirect SET visite = visite + 1 WHERE id = " . $row['id'] . " LIMIT 1");

      // creo il frameset
      $contenuto .= "<html><head>";
      $contenuto .= "<title>" . $row['titolo'] . "</title>\n";
      $contenuto .= "<meta name=\"keywords\" content=\"" . $row['metakey'] . "\">\n";
      $contenuto .= "<meta name=\"description\" content=\"" . $row['metadesc'] . "\">\n";
      $contenuto .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\">";
      $contenuto .= "</head>\n";
      if ($row['adv'] == 1)
      {
        $contenuto .= "<frameset rows=\"*," . $cnf['advframe_height'] . "\" frameborder=\"no\" border=\"0\" framespacing=\"0\">\n";
        $contenuto .= "<frame name=\"main_frame\" src=\"" . $row['url'] . "\">\n";
        $contenuto .= "<frame name=\"ad_frame\" scrolling=\"no\" noresize src=\"" . $cnf['advframe_url'] . "\">\n";
        $contenuto .= "</frameset>";
      }else{
        $contenuto .= "<frameset rows=\"*\" frameborder=\"no\" border=\"0\" framespacing=\"0\">\n";
        $contenuto .= "<frame name=\"main_frame\" src=\"" . $row['url'] . "\">\n";
        $contenuto .= "</frameset>";
      }
      $contenuto .= "<noframes>\n";
      $contenuto .= "<body bgcolor=\"#FFFFFF\" text=\"#000000\">";
      $contenuto .= "<p><a href=\"" . $row['url'] . "\">Click here</a> to visit &quot;" . $row['titolo'] . "&quot;</p>\n";
      $contenuto .= "<p>Powered by <a href=\"" . $cnf['sito_url'] . "\">" . $cnf['sito_nome'] . "</a></p>\n";
      $contenuto .= "</body></noframes></html>";
    }
  }

  if ($usotmpl)
  {
    // inserisco nel template
    $pagina = file_get_contents($cnf['sito_path'] . "/template.html");
    $pagina = str_replace("##TITOLO##", "Ops!", $pagina);
    $pagina = str_replace("##CONTENUTO##", big_err($contenuto), $pagina);
    echo $pagina;
  }else{
    echo $contenuto;
  }

  // libero la memoria
  unset($contenuto);
  if ($pagina) unset($pagina);
}
?>
