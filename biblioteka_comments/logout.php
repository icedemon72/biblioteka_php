<?php
  // logout.php je fajl na koji se salju korisnici koji kliknu "Izloguj se" dugme
  // ovde brisemo (unset-ujemo) sve sesijske promenljive radi izlogovanja korisnika/admina
  session_start();

  if(isset($_SESSION['username'])) {
    unset($_SESSION['username']);
    header('Location: ./index.php');
  }

  if(isset($_SESSION['admin'])) {
    unset($_SESSION['admin']);
    header('Location: ./admin_login.php');
  }  
?>