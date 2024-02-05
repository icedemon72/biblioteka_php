<?php 
  // Ovde zadajemo informacije za bazu i pravimo promenljivu $conn koju koristimo kroz ceo sajt
  $server = 'localhost';
  $user = 'root';
  $pw = '';
  $db = 'biblioteka';
  $conn = new mysqli($server, $user, $pw, $db);

  if($conn->connect_error) { 
    echo $conn->connect_error;
    die();
  }
?>