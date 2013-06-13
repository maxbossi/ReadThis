<?php
function invia_mail($to, $subject, $body, $replyto=0)
{
  if (!$replyto) $replyto = "info@ikiweb.it";
  $mailheader = "From: info@ikiweb.it\r\n";
  $mailheader .= "Reply-To: " . $replyto . "\r\n";
  $mailheader .= "X-Mailer: PHP/" . phpversion();
  @mail($to, $subject, $body, $mailheader);
}
invia_mail('info@ikiweb.it', 'Prova', 'Proviamo se funge');
?>