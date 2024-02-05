<?php 
  session_start();

  if(isset($_SESSION['username'])) {
    header('Location: ./pages/knjige.php');
    exit();
  }

  if(isset($_SESSION['admin'])) {
    header('Location: ./pages/admin/clanarine.php');
    exit();
  }

  include('./db.php');
  include('./functions/login_funkcije.php');

  $done = false;
  $errors = false;

  if(isset($_POST['submit'])) {
    $user = $_POST['user'];
    $password = $_POST['password'];

    if(checkUserPassword($user, $password, $conn)) {
      $done = true;
      $_SESSION['username'] = $user;
      header("Refresh:2 ; URL=./pages/knjige.php");
    } else {
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