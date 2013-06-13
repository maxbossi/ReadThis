<?php
// Configurazione
$api['pwd'] = "SGH34DFE28";

// Funzioni
function api_test_email($email)
{
  $re  = "^([a-zA-Z0-9])+([\.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)*\.([a-zA-Z]{2,6})$";
  if (ereg($re, $email)) return true;
  else return flase;
}
?>