<?php 
  // index.php je pocetna stranica, u nasem slucaju to je prijavljivanje korisnika

  // pokrecemo sesiju 
  session_start();

  // ukoliko je vec ulogovan korisnik saljemo ga na knjige.php
  if(isset($_SESSION['username'])) {
    header('Location: ./pages/knjige.php');
    exit();
  }

  // ukoliko je vec ulogovan admin saljemo ga na clanarine.php
  if(isset($_SESSION['admin'])) {
    header('Location: ./pages/admin/clanarine.php');
    exit();
  }

  // ukljucujemo $conn promenljivu i funkcije za login
  include('./db.php');
  include('./functions/login_funkcije.php');

  // na pocetku $done i $errors ne psotoje
  $done = false;
  $errors = false;

  // ukoliko je kliknuto dugme sa name="submit"
  if(isset($_POST['submit'])) {
    // uzimamo $user i $password
    $user = $_POST['user'];
    $password = $_POST['password'];

    // proveravamo da li se poklapaju unete informacije sa onim koje imamo u bazi
    if(checkUserPassword($user, $password, $conn)) {
      $done = true;
      // ukoliko se poklapaju postavljamo sesijsku promenljivu $username da bude jednaka korisnickom imenu
      $_SESSION['username'] = $user;
      header("Refresh:2 ; URL=./pages/knjige.php");
    } else {
      // ukoliko se ne poklapaju ispisujemo errors
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
  <title>Korisnik prijava</title>
</head>

<body class="login_body">
  <div class="h-100 d-flex align-items-center justify-content-center">
    <div class="login_div rounded">
      <h2 class="text-center mb-2">Prijava korisnika</h2>
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
        <p class="mt-2 admin_text">Admin ste? <a href="./admin_login.php">Prijavite se!</a></p>
      </form>
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