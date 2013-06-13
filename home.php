<?php
include("inc/config.php");
include("inc/auth.php");

if ($ute_login)
{
  header('location: /usercp.php');
  exit;
}

$act = $_GET['act'];
if (!$act) $act = "homepage";

// correzione x Godado
$err = $_GET['err'];
if ($err != "no") unset($err);

if ($act == "homepage")
{

  $contenuto = "<div class=\"bigwhois\">" .str_replace("<table","<table align=\"center\"",mostra_contenuto("whois"));
  $contenuto .= "<div style=\"text-align:center; margin-top: 15px; font-weight: bold\"><a href=\"" . $_SERVER['PHP_SELF'] . "?act=signup&err=no\">" . $lng['txt_3'] . "</a> - <a href=\"" . $_SERVER['PHP_SELF'] . "?act=login&err=no\">" . $lng['txt_4'] . "</a></div>";
  $contenuto .= "</div>";
  $contenuto .= mostra_contenuto("lastredirs") . "<br/>" . $lng['txt_25'];
  $contenuto .= "<div class=\"credits\">" . $lng['txt_26'] . "</div>";

/*
  $contenuto = "<table border=\"0\" cellpadding==\"0\" cellspacng=\"0\" align=\"center\"><tr valign=\"top\"><td width=\"440\">";
  $contenuto .= mostra_contenuto("whois") . "<br/>";
  $contenuto .= mostra_contenuto("lastredirs") . " [<a href=\"/redirs.php?lt=09\">Tutti i Redirect</a> | <a href=\"/redirs.php?act=top\">Top Redirect</a>]";
  $contenuto .= "</td><td width=\"10\"></td><td width=\"320\">";
  $contenuto .= mostra_contenuto("login") . "<br/>";
  $contenuto .= mostra_contenuto("signup");
  $contenuto .= "</td></tr></table>";
*/
}
else if ($act == "login")
{
  $email = $_POST['email'];
  $passwd = $_POST['passwd'];
  $redirect = $_POST['redirect'];
  $id_domain = $_POST['id_domain'];
  if (!$email || !$passwd) $errmsg = $lng['err_6'];
  else if (!is_email($email) || !is_password($passwd)) $errmsg = $lng['err_7'];
  if ($errmsg)
  {
    $contenuto = mostra_contenuto("login");
  }else{
    $res = @mysql_query("SELECT id, attivo FROM utenti WHERE email = '" . $email . "' AND pass = '" . $passwd . "'");
    if (mysql_num_rows($res) == 0)
    {
      $errmsg = $lng['err_7'];
    }else{
      $row = mysql_fetch_assoc($res);
      if ($row['attivo'] == 0)
      {
        $errmsg = $lng['err_8'];
      }
      else if ($row['bannato'] == 1)
      {
        $errmsg = $lng['err_8'];
      }else{
        // creo le sessioni e faccio il redirect
        $_SESSION['ute_id'] = $row['id'];
        $_SESSION['ute_pwd'] = $passwd;
        if ($redirect && $id_domain) header("location: /usercp.php?act=whois&redirect=" . $redirect . "&id_domain=" . $id_domain);
        else header("location: /usercp.php");
        exit;
      }
    }
  }
}
else if ($act == "signup")
{
  $email1 = $_POST['email1'];
  $email2 = $_POST['email2'];
  $passwd = $_POST['passwd'];
  $rules = $_POST['rules'];
  $redirect = $_POST['redirect'];
  $id_domain = $_POST['id_domain'];
  if (!is_email($email1)) $errmsg = $lng['err_2'];
  else if ($email1 != $email2) $errmsg = $lng['err_3'];
  else if (@mysql_result(@mysql_query("SELECT COUNT(id) FROM utenti WHERE email = '" . $email1 ."' LIMIT 1"),0,0) > 0) $errmsg = $lng['err_21'];
  else if (!is_password($passwd)) $errmsg = $lng['err_4'];
  else if ($rules != 1) $errmsg = $lng['err_11'];
  if ($errmsg)
  {
    $contenuto = mostra_contenuto("signup");
  }else{
    @mysql_query("INSERT INTO utenti VALUES ('','" . $email1 . "', '" . $passwd . "', 0, 0, " . time() . ", '" . $_SERVER['REMOTE_ADDR'] . "')");
    $id_ute = @mysql_result(@mysql_query("SELECT LAST_INSERT_ID()"),0,0);

    // invio la mail
    $emailbody = $lng['mail_body_1'];;
    if ($redirect && $id_domain) $link_verifica = $cnf['sito_url'] . "/home.php?act=verify&redirect=" . $redirect. "&id_domain=" . $id_domain . "&uid=" . $id_ute . "&code=" . makecode($email1);
    else $link_verifica = $cnf['sito_url'] . "/home.php?act=verify&uid=" . $id_ute . "&code=" . makecode($email1);
    $emailbody = str_replace("##SITO_NOME##", $cnf['sito_nome'], $emailbody);
    $emailbody = str_replace("##LINK_VERIFICA##", $link_verifica, $emailbody);

    invia_mail($email1, $lng['mail_subject_1'], $emailbody);
    // stampo a video la conferma
    $contenuto = big_ok($lng['ok_1']);
  }
}
else if ($act == "verify")
{
  $code = $_GET['code'];
  $uid = $_GET['uid'];
  $redirect = $_GET['redirect'];
  $id_domain = $_GET['id_domain'];
  if (!is_numeric($uid)) $uid = 0;
  if ($code && $uid)
  {
    $res = @mysql_query("SELECT * FROM utenti WHERE id = " . $uid . " LIMIT 1");
    if (mysql_num_rows($res) == 0)
    {
      $contenuto = big_err($lng['err_1']);
    }else{
      $row = mysql_fetch_assoc($res);
      if ($code != makecode($row['email']))
      {
        $contenuto = big_err($lng['err_12']);
      }else{
        @mysql_query("UPDATE utenti SET attivo = 1 WHERE id = " . $uid . " LIMIT 1");
        // creo le sessioni di login
        $_SESSION['ute_id'] = $row['id'];
        $_SESSION['ute_pwd'] = $row['pass'];
        // stampo a video la conferma
        if ($redirect && $id_domain) $link = "/usercp.php?act=whois&redirect=" . $redirect . "&id_domain=" . $id_domain;
        else $link = "/usercp.php";
        $contenuto = big_ok(str_replace("##LINK##", $link, $lng['ok_7']));
      }
    }
  }else{
    $contenuto = big_err($lng['err_1']);
  }
}
else if ($act == "pwd_reminder")
{
  $contenuto = mostra_contenuto("pwdreminder");
}
else if ($act == "pwd_send")
{
  $email = $_POST['email'];
  if (!$email) $errmsg = $lng['err_10'];
  else if (!is_email($email)) $errmsg = $lng['err_5'];
  if (!$errmsg)
  {
    $res = @mysql_query("SELECT pass FROM utenti WHERE email = '" . $email . "' LIMIT 1");
    if (@mysql_num_rows($res) == 0)
    {
      $errmsg = $lng['err_5'];
    }else{
      $row = @mysql_fetch_assoc($res);
      $passwd = $row['pass'];

      // invio la mail
      $emailbody = $lng['mail_body_2'];;
      $emailbody = str_replace("##SITO_NOME##", $cnf['sito_nome'], $emailbody);
      $emailbody = str_replace("##MAIL##", $email, $emailbody);
      $emailbody = str_replace("##PASS##", $row['pass'], $emailbody);

      invia_mail($email, $lng['mail_subject_2'], $emailbody);
      // stampo a video la conferma
      $contenuto = big_ok($lng['ok_2']);
    }
  }
  if ($errmsg) $contenuto = mostra_contenuto("pwdreminder");
}
else if ($act == "whois")
{
  $redirect = $_POST['redirect'];
  $id_domain = $_POST['id_domain'];
  if (!verifica_redirect($redirect)) $errmsg = $lng['err_14'];
  if (!$errmsg)
  {
    $redirect_completo = $redirect . "." . dominio_by_id($id_domain);
    if (verifica_redirect_libero($redirect, $id_domain))
    {
      $contenuto = big_ok(str_replace("##REDIRECT##", $redirect_completo, $lng['ok_3']));
      $contenuto .= "<table border=\"0\" cellpadding==\"0\" cellspacng=\"0\" width=\"800\" align=\"center\"><tr valign=\"top\"><td width=\"395\">";
      $contenuto .= mostra_contenuto("signup");
      $contenuto .= "</td><td width=\"10\"></td><td width=\"395\">";
      $contenuto .= mostra_contenuto("login");
      $contenuto .= "</td></tr></table>";
    }else{ 
      $errmsg = str_replace("##REDIRECT##", $redirect_completo, $lng['err_13']);
    }
  }
  if ($errmsg) $contenuto = mostra_contenuto("whois");
}
else if ($act == "rules")
{
  $contenuto = "<h1>" . $lng['txt_8'] . "</h1>";
  $contenuto .= $lng['rules'];
}
else
{
  $contenuto = big_err($lng['err_1']);
}

// definisco il contenuto
if ($errmsg && !$err) $contenuto = errore($errmsg) . $contenuto;

// inserisco nel template
$pagina = file_get_contents($cnf['sito_path'] . "/template.html");
$pagina = str_replace("##TITOLO##", $titolopg, $pagina);
$pagina = str_replace("##CONTENUTO##", $contenuto, $pagina);
echo $pagina;

// libero la memoria
unset($contenuto);
unset($pagina);
?>