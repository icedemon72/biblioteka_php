<?php 
  // STRANICA ZA LOGIN ADMINA

  // session_start() oznacava zapocinjanje sesije, u sesiji se cuvaju kolacici tj. promenljive
  // koje se pamte kroz razlicite stranice, npr. kad se ulogujemo postavljamo sesijsku promenljivu
  // username koju kasnije mozemo da koristimo i na drugom delu sajta radi verifikacije npr.
  session_start();

  // ukoliko postoji sesijska promenljiva username znaci da je korisnik ulogovan, tako da ukoliko
  // pristupi stranici za prijavljivanje admina poslacemo ga na knjige.php
  if(isset($_SESSION['username'])) {
    header('Location: ./pages/knjige.php');
    exit();
  }

  // isto tako i ako je admin ulogovan ne bi trebao biti u mogucnosti pristupu ovoj stranici
  // njega saljemo na clanarine.php
  if(isset($_SESSION['admin'])) {
    header('Location: ./pages/admin/clanarine.php');
    exit();
  }

  // ukljucujemo promenljivu za pristup bazi podataka $conn
  include('./db.php');

  // ukljucujemo funkcije koje nam trebaju radi citkijeg koda prilikom provere lozinke i sl.
  include('./functions/login_funkcije.php');

  // na pocetku logovanja, $done (zavrseno) je netacno, a takodje nemamo ni gresaka prilikom prijavljivanja
  // shodno tome, $errors je isto false
  $done = false;
  $errors = false;

  // ukoliko je pritisnuto dugme koje ima name="submit"
  if(isset($_POST['submit'])) {
    // uzimamo informacije iz forme gde je name="user" i name="password", a metoda forme je POST
    $user = $_POST['user'];
    $password = $_POST['password'];

    // ukoliko se podudaraju lozinka i korisnicko ime administratora (vidi: functions/login_funkcije.php)
    if(checkAdminPassword($user, $password, $conn)) {

      // postavljamo da je $done tacno, tj. da je zavrsen proces logovanja
      $done = true;

      // dodeljujemo sesijsku promenljivu 'admin' koja je jednaka adminovom ID-u
      $_SESSION['admin'] = getAdmin($user, $conn);
      // za 2 sekunde premesticemo korisnika (tj. tek ulogovanog administratora) na pages/admin/clanarine.php
      header("Refresh:2 ; URL=./pages/admin/clanarine.php");
    } else {
      // ukoliko se ne poklapaju podaci $errors je true, tj. doslo je do greske prilikom prijavljivanja (vidi linije 86 - 91)
      $errors = true;
    }
  }
  
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="./style/bootstrap.min.css">
  <link rel="stylesheet" href="./style/index.css">
  <title>Admin prijava</title>
</head>

<body class="login_body">
  <div class="h-100 d-flex align-items-center justify-content-center">
    <div class="login_div rounded admin_login">
      <h2 class="text-center mb-2 admin">Admin prijava</h2>
      <form method="POST">
        <div class="form-group mb-4">
          <label for="exampleInputUser1">Korisničko ime</label>
          <input name="user" type="text" class="form-control" id="exampleInputUser1" aria-describedby="userHelp"
            placeholder="Unesite korisničko ime">
        </div>
        <div class="form-group mb-4">
          <label for="exampleInputLozinka1">Lozinka</label>
          <input name="password" type="password" class="form-control" id="exampleInputLozinka1" placeholder="Unesite lozinku">
        </div>
        <button name="submit" type="submit" class="w-100 btn btn-primary">Login!</button>
        <p class="mt-2 admin_text">Korisnik ste? <a href="./index.php">Prijavite se!</a></p>
      </form>
      <!-- Ovo su kondicionalna prikazivanja u PHP-u
           ukoliko ima gresaka ispisace se sve izmedju if($errors) ... endif;
           isto tako, ukoliko ih nema, ispisace se "Uspesna prijava" -->
      <?php if($errors): ?>
        <p class="text-center bg-danger text-light p-1">Greška prilikom prijave, uneti su netačni podaci!</p>
      <?php endif; ?>
      <?php if($done): ?>
        <p class="text-center bg-success text-light p-1">Uspešna prijava!</p>
      <?php endif; ?>
    </div>
  </div>


</body>

</html>