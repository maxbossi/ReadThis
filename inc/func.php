<?php
function makecode($str)
{
  return strtoupper(substr(md5(strrev($str)), 5, 15));
}
function is_password($pass)
{
  $re  = "^[a-z0-9]{5,10}$";
  if (eregi($re, $pass)) return true; else return false;
}
function is_email($email)
{
  $re  = "^([a-z0-9])+([\.a-z0-9_-])*@([a-z0-9_-])+(\.[a-z0-9_-]+)*\.([a-z]{2,6})$";
  if (eregi($re, $email)) return true; else return false;
}
function is_url($url)
{
  $re  = "^[http|https|ftp]+:\/\/.*\.([a-z]{2,6})$";
  if (eregi($re, $url)) return true; else return false;
}
function invia_mail($to, $subject, $body, $replyto=0)
{
  global $cnf;
  if (!$replyto) $replyto = $cnf['sito_mail'];
  $mailheader = "From: " . $cnf['sito_mail'] . "\r\n";
  $mailheader .= "Reply-To: " . $replyto . "\r\n";
  $mailheader .= "X-Mailer: PHP/" . phpversion();
  $body .= "\n\n---------\n" . $cnf['sito_nome'] . "\n" . $cnf['sito_url'];
  @mail($to, $subject, $body, $mailheader);
}
function verifica_redirect($redirect)
{
  $verifica = false;
  if ($redirect != "www" && strlen($redirect) >= 3 && strlen($redirect) <= 50 && eregi("^[a-z0-9]+[_-]*[a-z0-9]+$", $redirect)) $verifica = true;
  return $verifica;
}
function verifica_dominio($dominio)
{
  if (eregi("^[a-z0-9]+\.[a-z0-9]+$", $dominio)) return true;
  else return false;
}
function dominio_by_id($id)
{
  if (!is_numeric($id))
  {
    return "???";
  }else{
    $res = @mysql_query("SELECT dominio FROM domini WHERE id = " . $id . " LIMIT 1");
    if (mysql_num_rows($res) == 0) return "???";
    else{
      $row = mysql_fetch_assoc($res);
      return $row['dominio'];
    }
  }
}
function id_by_dominio($dominio)
{
  if (!verifica_dominio($dominio))
  {
    return 0;
  }else{
    $res = @mysql_query("SELECT id FROM domini WHERE dominio = '" . $dominio . "' LIMIT 1");
    if (mysql_num_rows($res) == 0) return 0;
    else{
      $row = mysql_fetch_assoc($res);
      return $row['id'];
    }
  }
}
function verifica_redirect_libero($redirect, $id_dominio)
{
  if (is_numeric($id_dominio))
  {
    if (@mysql_result(@mysql_query("SELECT COUNT(id) FROM redirect WHERE id_dom = " . $id_dominio . " AND terzolivello = '" . addslashes($redirect) . "'"),0,0) == 1) return false;
    else return true;
  }else{
    return false;
  }
}
function errore($str)
{
  return "<div class=\"errmsg\"><div>" . $str . "</div></div>";
}
function big_err($str)
{
  return "<div class=\"big_err\"><div>" . $str . "</div></div>";
}
function big_ok($str)
{
  return "<div class=\"big_ok\"><div>" . $str . "</div></div>";
}
function mostra_contenuto($cont)
{
  global $lng;
  $output = "";
  if ($cont == "signup")
  {
    global $email1, $email2, $passwd, $redirect, $id_domain;
    $output .= "<h1>" . $lng['txt_3'] . "</h1>";
    $output .= "<form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "?act=signup\">";
    if ($redirect) $output .= "<input type=\"hidden\" name=\"redirect\" value=\"" . $redirect . "\"/>";
    if ($id_domain) $output .= "<input type=\"hidden\" name=\"id_domain\" value=\"" . $id_domain . "\"/>";
    $output .= "<table border=\"0\">";
    $output .= "<tr><td>E-mail</td><td><input type=\"text\" name=\"email1\" value=\"" . $email1 . "\" style=\"width:200px\"/></td></tr>";
    $output .= "<tr><td>" . $lng['txt_7'] . "</td><td><input type=\"text\" name=\"email2\" value=\"" . $email2 . "\" style=\"width:200px\"/></td></tr>";
    $output .= "<tr><td>Password</td><td><input type=\"password\" name=\"passwd\" value=\"" . $passwd . "\" style=\"width:200px\"/></td></tr>";
    $output .= "<tr><td><a href=\"home.php?act=rules\" target=\"_blank\">" . $lng['txt_8'] . "</a></td><td><input class=\"rad\" type=\"radio\" name=\"rules\" value=\"1\" checked/>" . $lng['txt_5'] . " <input class=\"rad\" type=\"radio\" name=\"rules\" value=\"0\"/>" . $lng['txt_6'] . "</td></tr>";
    $output .= "<tr><td></td><td align=\"right\"><input type=\"submit\" value=\"" . $lng['txt_3'] . "\" class=\"bt\"/></td></tr>";
    $output .= "</table></form>";
  }
  else if ($cont == "login")
  {
    global $email, $passwd, $redirect, $id_domain;
    $output .= "<h1>" . $lng['txt_4'] . "</h1>";
    $output .= "<form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "?act=login\">";
    if ($redirect) $output .= "<input type=\"hidden\" name=\"redirect\" value=\"" . $redirect . "\"/>";
    if ($id_domain) $output .= "<input type=\"hidden\" name=\"id_domain\" value=\"" . $id_domain . "\"/>";
    $output .= "<table border=\"0\">";
    $output .= "<tr><td>E-mail</td><td><input type=\"text\" name=\"email\" value=\"" . $email . "\" style=\"width:200px\"/></td></tr>";
    $output .= "<tr><td>Password</td><td><input type=\"password\" name=\"passwd\" value=\"" . $passwd . "\" style=\"width:200px\"/></td></tr>";
    $output .= "<tr><td></td><td align=\"right\"><input type=\"submit\" value=\"" . $lng['txt_4'] . "\" class=\"bt\"/></td></tr>";
    $output .= "<tr><td colspan=\"2\"><img border=\"0\" src=\"/img/ico_login.gif\" style=\"vertical-align: middle\"/> <a href=\"" . $_SERVER['PHP_SELF'] . "?act=pwd_reminder\">" . $lng['txt_9'] . "</a></td></tr>";
    $output .= "</table></form>";
  }
  else if ($cont == "whois")
  {
    global $redirect, $id_domain;
    $output .= "<h1>" . $lng['txt_11'] . "</h1>";
    $output .= "<form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "?act=whois\"><table border=\"0\"><tr>";
    $output .= "<td><input type=\"text\" name=\"redirect\" value=\"" . $redirect . "\" style=\"width:160px\"/></td>";
    $output .= "<td><b>.</b></td>";
    $output .= "<td><select name=\"id_domain\">";
    $res = @mysql_query("SELECT id, dominio FROM domini ORDER BY dominio ASC");
    while ($row = mysql_fetch_array($res))
    {
      $output .= "<option value=\"" . $row['id'] . "\"";
      if ($id_domain == $row['id']) $output .= " selected";
      $output .= ">" . $row['dominio'] . "</option>\n";
    }
    $output .= "</select></td>";
    $output .= "<td><input type=\"submit\" value=\"&gt;&gt;\" class=\"bt\"/></td>";
    $output .= "</tr></table></form>";
  }
  else if ($cont == "pwdreminder")
  {
    $output .= "<h1>" . $lng['txt_9'] . "</h1>";
    $output .= "<form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "?act=pwd_send\"><table border=\"0\">";
    $output .= "<tr><td>E-mail</td><td><input type=\"text\" name=\"email\" style=\"width:300px\"/></td><td><input type=\"submit\" value=\"&gt;&gt;\" class=\"bt\"/></td></tr>";
    $output .= "</table></form>";
  }
  else if ($cont == "formredir")
  {
    global $edit_id, $redirect, $id_domain, $titolo, $metadesc, $metakey, $url;
    if (!$url) $url = "http://";
    $redirect_completo = $redirect . "." . dominio_by_id($id_domain);
    if ($edit_id) $output .= "<h1>" . $lng['txt_12'] . "</h1>"; else $output .= "<h1>" . $lng['txt_11'] . "</h1>";
    $output .= $lng['txt_20'] . "<br/><br/>";
    $output .= "<form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "?act=redirect\">";
    if ($edit_id) $output .= "<input type=\"hidden\" name=\"edit_id\" value=\"" . $edit_id . "\"/>";
    $output .= "<input type=\"hidden\" name=\"redirect\" value=\"" . $redirect . "\"/>";
    $output .= "<input type=\"hidden\" name=\"id_domain\" value=\"" . $id_domain . "\"/>";
    $output .= "<table border=\"0\">";
    $output .= "<tr><td>Redirect</td><td><b>" . $redirect_completo . "</b></td></tr>";
    $output .= "<tr valign=\"top\"><td>" . $lng['txt_2'] . "</td><td><input type=\"text\" name=\"titolo\" value=\"" . $titolo . "\" style=\"width:500px\" maxlength=\"50\"/><br/><small>" . $lng['txt_21'] . "</small></td></tr>";
    $output .= "<tr valign=\"top\"><td nowrap=\"nowrap\">Meta-Description</td><td><input type=\"metadesc\" name=\"metadesc\" value=\"" . $metadesc . "\" style=\"width:500px\" maxlength=\"100\"/><br/><small>" . $lng['txt_22'] . "</small></td></tr>";
    $output .= "<tr valign=\"top\"><td>Meta-Keywords</td><td><input type=\"metadesc\" name=\"metakey\" value=\"" . $metakey . "\" style=\"width:500px\" maxlength=\"100\"/><br/><small>" . $lng['txt_23'] . "</small></td></tr>";
    $output .= "<tr valign=\"top\"><td>URL</td><td><input type=\"text\" name=\"url\" value=\"" . $url . "\" style=\"width:500px\" maxlength=\"250\"/><br/><small>" . $lng['txt_24'] . "</small></td></tr>";
    $output .= "<tr><td></td><td align=\"right\"><input type=\"submit\" value=\"" . $lng['txt_1'] . "\" class=\"bt\"/></td></tr>";
    $output .= "</table></form>";
  }
  else if ($cont == "lastredirs")
  {
    $output .= "<h1>" . $lng['txt_19'] . "</h1><div style=\"font-size: 11px;\">";
    $res = @mysql_query("SELECT id_dom, terzolivello FROM redirect WHERE attivo = 1 AND bannato = 0 ORDER BY id DESC LIMIT 20");
    while ($row = mysql_fetch_array($res))
    {
      $redir = $row['terzolivello'] . "." . dominio_by_id($row['id_dom']);
      $output .= "<a href=\"http://" . $redir . "\" target=\"_blank\">" . $redir . "</a>, ";
    }
    // tolgo ultima virgola
    $output = substr($output, 0, -2) . "... <a href=\"/redirs.php?lt=09\">" . $lng['txt_27'] . "</a>!</div>";
  }
  return $output;
}
?>