<?php
include("inc/config.php");

// Starto la session
session_start();

// variabili interne
$sqlwhere = "";
$err = "";
$executequery = false;

// Recupero do
if ($_GET['do']) $do = $_GET['do'];
else $do = "bacheca";

// Recupero save
$save = false;
if ($_POST['save'] == 1) $save = true;

// Recupero id
$id = $_GET['id'];
if (!$id) $id = $_POST['id'];
if ($id && !is_numeric($id)) $id = 0;

// recupero pg
if ($_GET['pg'] && is_numeric($_GET['pg'])) $pg = $_GET['pg'];
if (!$pg || $pg < 1) $pg = 1;

// recupero Query e Where
$query = $_GET['query'];
$where = $_GET['where'];

// Funzione di paginazione
function nav_pg()
{
  global $pg, $totpg, $do, $query, $where;
  echo "<p align=\"center\">";
  if ($pg > 1) echo "<input type=\"button\" value=\"&lt;&lt;\" onclick=\"location.href='" . $_SERVER['PHP_SELF'] . "?do=" . $do . "&query=" . $query . "&where=" . $where . "&pg=" . ($pg - 1) . "'\">&nbsp;";
  if ($totpg > $pg) echo "<input type=\"button\" value=\"&gt;&gt;\" onclick=\"location.href='" . $_SERVER['PHP_SELF'] . "?do=" . $do . "&query=" . $query . "&where=" . $where . "&pg=" . ($pg + 1) . "'\">";
  echo "</p>";
}

// Login amministratore
if ($do == "login")
{
	if ($_POST['admin_usr']) $_SESSION['admin_usr'] = htmlspecialchars(addslashes($_POST['admin_usr']));
	if ($_POST['admin_pwd']) $_SESSION['admin_pwd'] = htmlspecialchars(addslashes($_POST['admin_pwd']));
}
elseif ($do == "logout")
{
	session_destroy();
        header("location: /admincp.php");
        exit;
}
$admin_usr = $_SESSION['admin_usr'];
$admin_pwd = $_SESSION['admin_pwd'];
?>
<html>
<head>
<style>
body { background-color: #FFFFFF; color: #000033; margin: 0px; }
p, td, div, input, select, textarea { font-family: tahoma, verdana, arial; font-size: 12px; }
h1,h2 { font-family: tahoma, verdana, arial; font-weight: bold; margin: 0px 0px 10px 0px;}
h1 { font-size: 16px; padding: 4px; color: #FFFFFF; border-bottom: 1px solid #000033; background-color: #000070; }
h2 { font-size: 14px; padding-bottom: 4px; border-bottom: 3px solid #000033; }
form { margin: 0px 0px 0px 0px; }
textarea { width: 400px; height: 150px; }
a, a:visited { color: #0000FF; text-decoration: none; }
a:hover { color: #EE0000; text-decoration: none; }
ul.menu { margin: 0px; padding: 0px; list-style-type: none; }
ul.menu li { padding: 4px 0px; border-bottom: 1px solid #000033; }
tr.tit td { background: #DDDDDD; font-weight: bold; }
p.mini { font-size: 11px; }
</style>
<script type="text/javascript">
function cambia_stato_att(C,ID)
{
  var statusgif = document.getElementById("statusatt_" + ID).src;
  if (statusgif.indexOf("nonatt") != -1)
  {
    document.getElementById("statusatt_" + ID).src = "/img/ico_att.gif"
    var x = (window.ActiveXObject) ? new ActiveXObject("Microsoft.XMLHTTP") : new XMLHttpRequest();
    x.open("GET", "admincp-ajax-att.php?c=" + C + "&id=" + ID, true);
    x.send(null);
  }else{
    alert("Se vuoi bloccare usa il BAN...");
  }
}
function cambia_stato_ban(C,ID)
{
  var newgif = "/img/ico_ban.gif";
  var statusgif = document.getElementById("statusban_" + ID).src;
  if (statusgif.indexOf("nonban") == -1) newgif = "/img/ico_nonban.gif";
  document.getElementById("statusban_" + ID).src = newgif;
  var x = (window.ActiveXObject) ? new ActiveXObject("Microsoft.XMLHTTP") : new XMLHttpRequest();
  x.open("GET", "admincp-ajax-ban.php?c=" + C + "&id=" + ID, true);
  x.send(null);
}
function cancella(C,ID)
{
  if (window.confirm('Sei sicuro?'))
  {
    document.getElementById("tr_" + ID).style.display = 'none';
    var x = (window.ActiveXObject) ? new ActiveXObject("Microsoft.XMLHTTP") : new XMLHttpRequest();
    x.open("GET", "admincp-ajax-del.php?c=" + C + "&id=" + ID, true);
    x.send(null);
  }
}
</script>
<title>AdminCP - <?php echo $cnf['sito_nome']; ?></title>
</head>
<body><h1><?php echo $cnf['sito_nome']; ?> - Area di Amministrazione</h1>
<?php
if ((!$admin_usr || !$admin_pwd) || ($admin_usr != $cnf['admin_usr'] || $admin_pwd != $cnf['admin_pwd']))
{
  if ($do == "login") echo "<p style=\"padding-left: 4px;\"><b>Login errato!</b><br/>I dati inseriti non sono corretti. Riprova...</p>";
  ?>
  <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>?do=login">
  <table border="0">
  <tr><td>Username:</td><td><input type="text" name="admin_usr"></td></tr>
  <tr><td>Password:</td><td><input type="password" name="admin_pwd"></td></tr>
  <tr><td colspan="2" align="right"><input type="submit" value="Login!"></td></tr>
  </table></form>
  <?php
}else{
  if ($do == "login") $do = "bacheca";
  ?>
  <table border="0"><tr valign="top">
  <td width="120"><ul class="menu">
  <li><a href="<?php echo $_SERVER['PHP_SELF'];?>">Bacheca</a></li>
  <li><a href="<?php echo $_SERVER['PHP_SELF'];?>?do=domini">Domini</a></li>
  <li><a href="<?php echo $_SERVER['PHP_SELF'];?>?do=utenti">Utenti</a></li>
  <li><a href="<?php echo $_SERVER['PHP_SELF'];?>?do=redirect">Redirect</a></li>
  <li><a href="<?php echo $_SERVER['PHP_SELF'];?>?do=logout">Logout</a></li>
  </ul></td>
  <td width="800"><div style="margin-left: 10px;">
  <?php
  if ($do == "bacheca")
  {
    $tot_ute = @mysql_result(@mysql_query("SELECT COUNT(id) FROM utenti"),0,0);
    $tot_ute_dis = @mysql_result(@mysql_query("SELECT COUNT(id) FROM utenti WHERE attivo = 0"),0,0);
    $tot_dom = @mysql_result(@mysql_query("SELECT COUNT(id) FROM domini"),0,0);
    $tot_red = @mysql_result(@mysql_query("SELECT COUNT(id) FROM redirect"),0,0);
    $tot_red_dis = @mysql_result(@mysql_query("SELECT COUNT(id) FROM redirect WHERE attivo = 0"),0,0);
    ?>
    <h2>Statistiche</h2>
    <table border="0">
    <tr><td><b>Domini</b></td><td><a href="<?php echo $_SERVER['PHP_SELF']; ?>?do=domini"><?php echo $tot_dom;?></a></td></tr>
    <tr><td><b>Utenti</b></td><td><a href="<?php echo $_SERVER['PHP_SELF']; ?>?do=utenti"><?php echo $tot_ute;?> totali</a>, <a href="<?php echo $_SERVER['PHP_SELF']; ?>?do=utenti&where=attivo&query=0"><?php echo $tot_ute_dis;?> non attivi</a></td></tr>
    <tr><td><b>Redirect</b>&nbsp;</td><td><a href="<?php echo $_SERVER['PHP_SELF']; ?>?do=redirect"><?php echo $tot_red;?> totali</a>, <a href="<?php echo $_SERVER['PHP_SELF']; ?>?do=redirect&where=attivo&query=0"><?php echo $tot_red_dis;?> non attivi</a></td></tr>
    </table><br/>
    <h2>Cerca Utente</h2>
    <form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="do" value="utenti">
    <table border="0"><tr>
    <td><input type="text" name="query" style="width: 150px"></td>
    <td><select name="where" style="width: 75px"><option value="utente_id">ID</option><option value="email">Email</option></select></td>
    <td><input type="submit" value="Cerca!"/></td>
    </tr></table>
    </form>
    <p class="mini">
    [<b>ID</b>] ID dell'utente<br/>
    [<b>Email</b>] Indirizzo email (completo o parziale) d'iscrizione
    </p>
    <h2>Cerca Redirect</h2>
    <form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="do" value="redirect">
    <table border="0"><tr>
    <td><input type="text" name="query" style="width: 150px"></td>
    <td><select name="where" style="width: 75px"><option value="redirect_id">ID</option><option value="redirect">Redirect</option><option value="url">URL</option></select></td>
    <td><input type="submit" value="Cerca!"/></td>
    </tr></table>
    </form>
    <p class="mini">
    [<b>ID</b>] ID del Redirect<br/>
    [<b>Redirect</b>] La URL del redirect (ad es. pippo.dominio.com)<br/>
    [<b>URL</b>] Indirizzo reale cui punta il redirect
    </p>
    <?php
  }
  elseif ($do == "utenti")
  {
    echo "<h2>Utenti</h2>
    <p class=\"mini\">Il <b>pallino colorato</b> indica lo stato dell'utente: [<b>verde</b>] Accont attivato; [<b>rosso</b>] Account non attivato.<br/>
    Il simbolo del <b>divieto</b> indica se l'utente è stato bannato: [<b>grigio</b>] Utente NON bannato; [<b>rosso</b>] Utente bannato.<br/><br/>
    E' possibile modificare entrambi i valori semplicemente cliccando sull'icona corrispondente.</p>";
    if ($where)
    {
      if ($where == "attivo" && $query == 0) $sqlwhere = " WHERE attivo = 0";
      elseif ($where == "utente_id" && is_numeric($query)) $sqlwhere = " WHERE id = " . $query;
      elseif ($where == "email" && $query) $sqlwhere = " WHERE email LIKE '%" . addslashes($query) . "%'";
    }
    $totres = @mysql_result(@mysql_query("SELECT COUNT(id) FROM utenti" . $sqlwhere),0,0);
    $totpg = round($totres/50);
    if (($totpg * 50) < $totres) $totpg++;
    $res = @mysql_query("SELECT * FROM utenti" . $sqlwhere . " ORDER BY id DESC LIMIT " . (($pg * 50) - 50) . ", 50");
    if (mysql_num_rows($res) == 0)
    {
      echo "<p>Nessun utente...</p>";
    }else{
      echo "<table border=\"1\" cellspacing=\"0\" cellpadding=\"4\">
      <tr class=\"tit\">
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td width=\"200\">E-mail</td>
      <td width=\"150\">Password</td>
      <td width=\"100\">IP</td>
      <td width=\"100\">Time</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      </tr>";
      while ($row = mysql_fetch_array($res))
      {
        if ($row['attivo'] == 1) { $statusatt = "ico_att.gif"; }else{ $statusatt = "ico_nonatt.gif"; }
        if ($row['bannato'] == 1) { $statusban = "ico_ban.gif"; }else{ $statusban = "ico_nonban.gif"; }
        echo "<tr id=\"tr_" . $row['id'] . "\">
        <td><a href=\"javascript:cancella('U'," . $row['id'] . ");\"><img border=\"0\" src=\"/img/ico_delete.gif\" alt=\"Cancella\"/></a></td>
        <td><a href=\"" . $_SERVER['PHP_SELF'] . "?do=ute_edt&id=" . $row['id'] . "\"><img border=\"0\" src=\"/img/ico_edit.gif\" alt=\"Modifica\"/></a></td>
        <td><a href=\"" . $_SERVER['PHP_SELF'] . "?do=redirect&where=utente_id&query=" . $row['id'] . "\"><img border=\"0\" src=\"/img/ico_redir.gif\" alt=\"Redirect\"/></a></td>
        <td><a href=\"mailto:" . $row['email'] . "\">" . $row['email'] . "</a></td>
        <td>" . $row['pass'] . "</td>
        <td>" . $row['ip'] . "</td>
        <td>" . date("d/m/Y H:i:s", $row['ts']) . "</td>
        <td><a href=\"javascript:cambia_stato_att('U'," . $row['id'] . ");\"><img id=\"statusatt_" . $row['id'] . "\" border=\"0\" src=\"/img/" . $statusatt . "\" alt=\"Attiva/Disattiva\"/></a></td>
        <td><a href=\"javascript:cambia_stato_ban('U'," . $row['id'] . ");\"><img id=\"statusban_" . $row['id'] . "\" border=\"0\" src=\"/img/" . $statusban . "\" alt=\"Banna/Togli Ban\"/></a></td>
        </tr>";
      }
      echo "</table>";
      if ($totpg > 1) nav_pg();
    }
  }
  elseif ($do == "ute_edt" && $id)
  {
    echo "<h2>Modifica Utente</h2>";
    if ($save)
    {
      $email = $_POST['email'];
      $pass = $_POST['pass'];
      $attivo = $_POST['attivo'];
      $bannato = $_POST['bannato'];

      // correzioni automatiche
      if ($attivo != 0 && $attivo != 1) $attivo = 0;
      if ($bannato != 0 && $bannato != 1) $bannato = 0;

      // verifico gli errori
      if (!$email) $err .= "<li>Non hai inserito l'indirizzo email!</li>";
      if (!is_email($email)) $err .= "<li>L'indirizzo email non sembra corretto!</li>";
      if (!$pass) $err .= "<li>Non hai inserito la password!</li>";
      if (!is_password($pass)) $err .= "<li>La password non sembra corretta!</li>";

      if ($err)
      {
        echo "<b>Ci sono degli errori:</b><ul>" . $err . "</ul>";
      }else{
        $executequery = true;
        @mysql_query("UPDATE utenti SET email = '" . $email . "', pass = '" . $pass . "', attivo = " . $attivo . ", bannato = " . $bannato . " WHERE id = " . $id . " LIMIT 1");
        echo "<p>Utente modificato con successo!<br/>
        Per vedere l'elenco degli utenti <a href=\"" . $_SERVER['PHP_SELF'] . "?do=utenti\">clicca qui</a>.</p>";
      }
    }
    if (!$executequery)
    {
      if (!$save)
      {
        $res = @mysql_query("SELECT * FROM utenti WHERE id = " . $id . " LIMIT 1");
        if (mysql_num_rows($res) > 0)
        {
          $row = mysql_fetch_assoc($res);
          $email = $row['email'];
          $pass = $row['pass'];
          $attivo = $row['attivo'];
          $bannato = $row['bannato'];
        }else{
          exit;
        }
      }
      ?>
      <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?do=<?php echo $do; ?>">
      <input type="hidden" name="id" value="<?php echo $id; ?>" />
      <input type="hidden" name="save" value="1" />
      <table border="0">
      <tr><td>Email:</td><td><input type="text" name="email" value="<?php echo $email; ?>" style="width: 300px"/></td></tr>
      <tr><td>Password:</td><td><input type="text" name="pass" value="<?php echo $pass; ?>" style="width: 300px"/></td></tr>
      <tr><td>Attivo:</td><td><input type="radio" name="attivo" value="1"<?php if ($attivo) echo " checked";?>/> Sì <input type="radio" name="attivo" value="0"<?php if (!$attivo) echo " checked";?>/> No</td></tr>
      <tr><td>Bannato:</td><td><input type="radio" name="bannato" value="1"<?php if ($bannato) echo " checked";?>/> Sì <input type="radio" name="bannato" value="0"<?php if (!$bannato) echo " checked";?>/> No</td></tr>
      </table><p><input type="submit" value="Salva Modifiche!"/></p>
      </form>
      <?php
    }
  }
  elseif ($do == "domini")
  {
    ?>
    <h2>Aggiunggi Dominio</h2>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?do=dom_add">
    <table border="0"><tr>
    <td>Nuovo Dominio:</td>
    <td><input type="text" name="dominio" style="width: 160px"/></td>
    <td><input type="submit" value="Aggiungi!"/></td>
    </tr></table>
    </form>
    <p class="mini">Prima di aggiungere un <b>nuovo dominio</b> ricordati di <b>contattare il tuo provider</b> chiedendogli di puntare il dominio in questione su questo spazio web come alias del dominio principale (<?php echo str_replace("http://","",$cnf['sito_url']);?>).
    E' molto importante che il nuovo dominio venga puntato con le <b>wildcards</b>, cioè con la formula: <b>*.dominio.com</b>.</p>
    <?php
    echo "<h2>Elenco Domini</h2>";
    $res = @mysql_query("SELECT * FROM domini ORDER BY dominio ASC");
    if (mysql_num_rows($res) == 0)
    {
      echo "<p>Nessun dominio...</p>";
    }else{
      echo "<table border=\"1\" cellspacing=\"0\" cellpadding=\"4\">
      <tr class=\"tit\">
      <td>&nbsp;</td>
      <td width=\"200\">Dominio</td>
      </tr>";
      while ($row = mysql_fetch_array($res))
      {
        echo "<tr>
        <td><a href=\"" . $_SERVER['PHP_SELF'] . "?do=dom_del&id=" . $row['id'] . "\" onclick=\"return confirm('Sei sicuro?')\"><img border=\"0\" src=\"/img/ico_delete.gif\" alt=\"Cancella\"/></a></td>
        <td>" . $row['dominio'] . "</td>
        </tr>";
      }
      echo "</table>";
    }
  }
  elseif ($do == "dom_add" && $_POST['dominio'])
  {
    $dominio = str_replace("http://www.", "", $_POST['dominio']);
    $dominio = str_replace("www.", "", $dominio);
    if (strlen($dominio) < 5 || strlen($dominio) > 50) $err .= "<li>Il dominio deve essere di min 5 e max 50 caratteri!</li>";
    if (!verifica_dominio($dominio)) $err .= "<li>Il dominio non sembra corretto!</li>";
    if ($err)
    {
      echo "<b>Ci sono degli errori:</b><ul>" . $err . "</ul>
      <p><input type=\"button\" onclick=\"history.back()\" value=\"Torna indietro!\"/></p>";
    }else{
      @mysql_query("INSERT INTO domini VALUES ('', '" . $dominio . "')");
      echo "<h2>Dominio Aggiunto</h2>
      <p>Il dominio è stato aggiunto con successo!<br/>
      Per vedere l'elenco dei domini <a href=\"" . $_SERVER['PHP_SELF'] . "?do=domini\">clicca qui</a>.</p>";
    }
  }
  elseif ($do == "dom_del" && $id)
  {
    @mysql_query("DELETE FROM domini WHERE id = " . $id . " LIMIT 1");
    @mysql_query("DELETE FROM redirect WHERE id_dom = " . $id);
    echo "<h2>Cancellazione Dominio</h2>
    <p>Cancellazione effettuata con successo!<br/>
    Per vedere l'elenco dei domini <a href=\"" . $_SERVER['PHP_SELF'] . "?do=domini\">clicca qui</a>.</p>";
  }
  elseif ($do == "redirect")
  {
    echo "<h2>Redirect</h2>
    <p class=\"mini\">Il <b>pallino colorato</b> indica lo stato del redirect: [<b>verde</b>] Redirect attivo; [<b>rosso</b>] Redirect non attivo.<br/>
    Il simbolo del <b>divieto</b> indica se il redirect è stato bannato: [<b>grigio</b>] Redirect NON bannato; [<b>rosso</b>] Redirect bannato.<br/><br/>
    E' possibile modificare entrambi i valori semplicemente cliccando sull'icona corrispondente.</p>";
    if ($where)
    {
      if ($where == "redirect_id" && is_numeric($query))
      {
        $sqlwhere = " WHERE id = " . $query;
      }
      elseif ($where == "utente_id" && is_numeric($query))
      {
        $sqlwhere = " WHERE id_ute = " . $query;
      }
      elseif ($where == "redirect" && $query)
      {
        $query = str_replace("http://","",$query);
        if (!strpos($query,".")) $sqlwhere = " WHERE terzolivello LIKE '%" . addslashes($query) . "%'";
        else
        {
          $redir = explode(".",$query);
          $sqlwhere = " WHERE terzolivello LIKE '%" . addslashes($redir[0]) . "%'";
        }
      }
      elseif ($where == "url" && $query)
      {
        $sqlwhere = " WHERE url LIKE '%" . addslashes($query) . "%'";
      }
      elseif ($where == "attivo" && $query == 0)
      {
        $sqlwhere = " WHERE attivo = 0";
      }
    }
    $totres = @mysql_result(@mysql_query("SELECT COUNT(id) FROM redirect" . $sqlwhere),0,0);
    $totpg = round($totres/50);
    if (($totpg * 50) < $totres) $totpg++;
    $res = @mysql_query("SELECT * FROM redirect" . $sqlwhere . " ORDER BY id DESC LIMIT " . (($pg * 50) - 50) . ", 50");
    if (mysql_num_rows($res) == 0)
    {
      echo "<p>Nessun redirect...</p>";
    }else{
      echo "<table border=\"1\" cellspacing=\"0\" cellpadding=\"4\">
      <tr class=\"tit\">
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td width=\"200\">Redirect</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      </tr>";
      while ($row = mysql_fetch_array($res))
      {
        if ($row['attivo'] == 1) { $statusatt = "ico_att.gif"; }else{ $statusatt = "ico_nonatt.gif"; }
        if ($row['bannato'] == 1) { $statusban = "ico_ban.gif"; }else{ $statusban = "ico_nonban.gif"; }
        $redirect_completo = $row['terzolivello'] . "." . dominio_by_id($row['id_dom']);
        echo "<tr id=\"tr_" . $row['id'] . "\">
        <td><a href=\"javascript:cancella('R'," . $row['id'] . ");\"><img border=\"0\" src=\"/img/ico_delete.gif\" alt=\"Cancella\"/></a></td>
        <td><a href=\"" . $_SERVER['PHP_SELF'] . "?do=red_edt&id=" . $row['id'] . "\"><img border=\"0\" src=\"/img/ico_edit.gif\" alt=\"Modifica\"/></a></td>
        <td><a href=\"" . $_SERVER['PHP_SELF'] . "?do=utenti&where=utente_id&query=" . $row['id_ute'] . "\"><img border=\"0\" src=\"/img/ico_ute.gif\" alt=\"Utente\"/></a></td>
        <td><a href=\"" . $row['url'] . "\" target=\"_blank\">" . $redirect_completo . "</a></td>
        <td><a href=\"javascript:cambia_stato_att('R'," . $row['id'] . ");\"><img id=\"statusatt_" . $row['id'] . "\" border=\"0\" src=\"/img/" . $statusatt . "\" alt=\"Attiva/Disattiva\"/></a></td>
        <td><a href=\"javascript:cambia_stato_ban('R'," . $row['id'] . ");\"><img id=\"statusban_" . $row['id'] . "\" border=\"0\" src=\"/img/" . $statusban . "\" alt=\"Banna/Togli Ban\"/></a></td>
        </tr>";
      }
      echo "</table>";
      if ($totpg > 1) nav_pg();
    }
  }
  elseif ($do == "red_edt" && $id)
  {
    echo "<h2>Modifica Redirect</h2>";
    if ($save)
    {
      $redirect = $_POST['redirect'];
      $titolo = $_POST['titolo'];
      $metadesc = $_POST['metadesc'];
      $metakey = $_POST['metakey'];
      $url = $_POST['url'];
      $adv = $_POST['adv'];
      $attivo = $_POST['attivo'];
      $bannato = $_POST['bannato'];

      // correzioni automatiche
      if ($adv != 0 && $adv != 1) $adv = 1;
      if ($attivo != 0 && $attivo != 1) $attivo = 0;
      if ($bannato != 0 && $bannato != 1) $bannato = 0;

      // verifico gli errori
      if (!$titolo) $err .= "<li>Non hai inserito il titolo!</li>";
      if (strlen($titolo) < 5 || strlen($titolo) > 50) $err .= "<li>Il titolo deve essere di min 5 e max 50 caratteri!</li>";
      if (strlen($metadesc) > 50) $err .= "<li>Il campo meta-description deve essere di max 50 caratteri!</li>";
      if (strlen($metakey) > 50) $err .= "<li>Il campo meta-keywords deve essere di max 50 caratteri!</li>";
      if (!$url) $err .= "<li>Non hai inserito la URL!</li>";
      if (!is_url($url)) $err .= "<li>La URL non sembra corretta!</li>";
      if ($err)
      {
        echo "<b>Ci sono degli errori:</b><ul>" . $err . "</ul>";
      }else{
        $executequery = true;
        @mysql_query("UPDATE redirect SET titolo = '" . addslashes($titolo) . "', metadesc = '" . addslashes($metadesc) . "', metakey = '" . addslashes($metakey) . "', url = '" . addslashes($url) . "', adv = " . $adv . ", attivo = " . $attivo . ", bannato = " . $bannato . " WHERE id = " . $id . " LIMIT 1");
        echo "<p>Redirect modificato con successo!<br/>
        Per vedere l'elenco dei redirect <a href=\"" . $_SERVER['PHP_SELF'] . "?do=redirect\">clicca qui</a>.</p>";
      }
    }
    if (!$executequery)
    {
      if (!$save)
      {
        $res = @mysql_query("SELECT * FROM redirect WHERE id = " . $id . " LIMIT 1");
        if (mysql_num_rows($res) > 0)
        {
          $row = mysql_fetch_assoc($res);
          $id_dom = $row['id_dom'];
          $terzolivello = $row['terzolivello'];
          $titolo = $row['titolo'];
          $metadesc = $row['metadesc'];
          $metakey = $row['metakey'];
          $url = $row['url'];
          $adv = $row['adv'];
          $attivo = $row['attivo'];
          $bannato = $row['bannato'];
          $redirect = $terzolivello . "." . dominio_by_id($id_dom);
        }else{
          exit;
        }
      }
      if (!$url) $url = "http://";
      ?>
      <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?do=<?php echo $do; ?>">
      <input type="hidden" name="id" value="<?php echo $id; ?>" />
      <input type="hidden" name="save" value="1" />
      <input type="hidden" name="redirect" value="<?php echo $redirect; ?>" />
      <table border="0">
      <tr><td>Redirect:</td><td><b><?php echo $redirect; ?></b></td></tr>
      <tr><td>Titolo:</td><td><input type="text" name="titolo" value="<?php echo $titolo; ?>" style="width: 300px"/></td></tr>
      <tr><td>Meta-Description:</td><td><input type="text" name="metadesc" value="<?php echo $metadesc; ?>" style="width: 300px"/></td></tr>
      <tr><td>Meta-Keywords:</td><td><input type="text" name="metakey" value="<?php echo $metakey; ?>" style="width: 300px"/></td></tr>
      <tr><td>URL:</td><td><input type="text" name="url" value="<?php echo $url; ?>" style="width: 300px"/></td></tr>
      <tr><td>Pubblicità:</td><td><input type="radio" name="adv" value="1"<?php if ($adv) echo " checked";?>/> Sì <input type="radio" name="adv" value="0"<?php if (!$adv) echo " checked";?>/> No</td></tr>
      <tr><td>Attivo:</td><td><input type="radio" name="attivo" value="1"<?php if ($attivo) echo " checked";?>/> Sì <input type="radio" name="attivo" value="0"<?php if (!$attivo) echo " checked";?>/> No</td></tr>
      <tr><td>Bannato:</td><td><input type="radio" name="bannato" value="1"<?php if ($bannato) echo " checked";?>/> Sì <input type="radio" name="bannato" value="0"<?php if (!$bannato) echo " checked";?>/> No</td></tr>
      </table><p><input type="submit" value="Salva Modifiche!"/></p>
      </form>
      <?php
    }
  }
  ?>
  </div></td>
  </tr></table>
  <?php
}
?>