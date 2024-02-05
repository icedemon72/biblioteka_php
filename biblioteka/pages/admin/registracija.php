<?php
session_start();

if (!isset($_SESSION['admin'])) {
  header('Location: ./../../admin_login.php');
  exit();
}

include('./../../db.php');
include('./../../functions/login_funkcije.php');

$clanarine = array(
  'id' => array(),
  'trajanje' => array(),
  'cena' => array()
);

$sql = "SELECT * FROM clanarine";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
  array_push($clanarine['id'], $row['id']);
  array_push($clanarine['trajanje'], $row['trajanje']);
  array_push($clanarine['cena'], $row['cena']);
}

$done = false;
$error = false;

if (isset($_POST['submit'])) {
  $user = $_POST['user'];
  $password = $_POST['password'];
  $phone = $_POST['phone'];
  $name = $_POST['name'];
  $sub = $_POST['sub'];

  $errorMsg = '';

  if (!$user) {
    $error = true;
    $errorMsg = 'Morate uneti korisničko ime!';
  }

  if (!$password) {
    $error = true;
    $errorMsg = 'Morate uneti lozinku!';
  }

  if (!$name) {
    $error = true;
    $errorMsg = 'Morate uneti ime i prezime!';
  }

  if (!$error) { // Nema greske prilikom unosa
    if (getUser($user, $conn)) {
      $error = true;
      $errorMsg = 'Korisničko ime već postoji!';
    } else {
      $password = md5($password);

      $sql = "INSERT INTO korisnici(korisnicko_ime, lozinka, ime, telefon)
                VALUES ('$user', '$password', '$name', '$phone')";

      $conn->query($sql);

      if ($sub != 'none') {
        $date = date_format(date_create(), 'Y-m-d');
        $subDuration = 1;
        $userId = getUser($user, $conn);

        $sql = "SELECT * FROM clanarine WHERE id = '$sub'";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()) {
          $subDuration = $row['trajanje'];
        }

        $sql = "INSERT INTO korisnici_clanarine (korisnici_id, clanarine_id, vazi_do)
                  VALUES('$userId', '$sub', DATE_ADD('$date', INTERVAL '$subDuration' MONTH))";

        $conn->query($sql);
      }

      $done = true;
      header('Refresh:1; URL=./registracija.php');
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="./../../style/bootstrap.min.css">
  <link rel="stylesheet" href="./../../style/index.css">
  <title>Registracija članova</title>
</head>

<body class="login_body">
  <nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Biblioteka</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
        aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link" href="./../knjige.php">Knjige</a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown"
              aria-expanded="false">
              Admin
            </a>
            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
              <li><a class="dropdown-item active" href="./registracija.php">Registracija korisnika</a></li>
              <li><a class="dropdown-item" href="./clanarine.php">Članarine</a></li>
              <li><a class="dropdown-item" href="./obavesti.php">Obavesti korisnike</a></li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li><a class="dropdown-item" href="./dodaj_knjigu.php">Dodaj knjige</a></li>
              <li><a class="dropdown-item" href="./upravljanje.php">Upravljaj knjigama</a></li>
            </ul>
          </li>
        </ul>
        <div class="d-flex">
          <a class="btn btn-light" href="./../../logout.php">Izloguj se!</a>
        </div>
      </div>
    </div>
  </nav>

  <form method="post">
    <div class="container h-100 mt-5">
      <div class="row d-flex justify-content-center align-items-center h-100">
        <div class="col-xl-9 col-md-10 col-sm-12">

          <h1 class="text-white mb-4">Registruj korisnika</h1>

          <div class="card rounded">
            <div class="card-body">
              <div class="row align-items-center pt-4 pb-3">
                <div class="col-md-3 ps-5">
                  <h6 class="mb-0">Ime i prezime</h6>
                </div>
                <div class="col-md-9 pe-5">
                  <input name="name" type="text" class="form-control form-control-lg"
                    placeholder="Unesite ime i prezime korisnika..." />
                </div>
              </div>

              <hr class="mx-n2">

              <div class="row align-items-center py-3">
                <div class="col-md-3 ps-5">
                  <h6 class="mb-0">Korisničko ime</h6>
                </div>
                <div class="col-md-9 pe-5">
                  <input name="user" type="text" class="form-control form-control-lg"
                    placeholder="Unesite korisničko ime korisnika..." required />
                  <p class="small text-muted mt-2">Ovim imenom se korisnik prijavljuje na web-sajt!</p>
                </div>
              </div>

              <hr class="mx-n2">
              <div class="row align-items-center py-3">
                <div class="col-md-3 ps-5">
                  <h6 class="mb-0">Lozinka</h6>
                </div>
                <div class="col-md-9 pe-5">
                  <input name="password" type="password" class="form-control form-control-lg"
                    placeholder="Unesite lozinku korisnika..." required />
                  <p class="small text-muted mt-2">Ovu lozinku će korisnik koristiti na sajtu!</p>
                </div>
              </div>

              <hr class="mx-n2">

              <div class="row align-items-center py-3">
                <div class="col-md-3 ps-5">
                  <h6 class="mb-0">Telefon</h6>
                </div>
                <div class="col-md-9 pe-5">
                  <input name="phone" type="text" class="form-control form-control-lg"
                    placeholder="Unesite broj telefona korisnika..." required />
                </div>
              </div>

              <hr class="mx-n2">

              <div class="row align-items-center py-3">
                <div class="col-md-3 ps-5">
                  <h6 class="mb-0">Članarina</h6>
                </div>
                <div class="col-md-9 pe-5">
                  <select name="sub" type="text" class="form-control form-control-lg">
                    <option value="none">Izaberi članarinu kasnije</option>
                    <?php for ($i = 0; $i < sizeof($clanarine['cena']); $i++): ?>
                      <option value="<?php echo $clanarine['id'][$i] ?>">
                        <?php echo $clanarine['trajanje'][$i] . 'M (' . $clanarine['cena'][$i] . ' RSD)' ?>
                      </option>
                    <?php endfor; ?>
                  </select>
                </div>
              </div>

              <div class="px-5 py-4 d-flex justify-content-center">
                <button type="submit" name="submit" class="btn btn-primary btn-lg w-50 md-w-100">Registruj
                  korisnika!</button>
              </div>

              <?php if ($error): ?>
                <p class="text-center bg-danger text-light p-1">
                  <?php echo $errorMsg; ?>
                </p>
              <?php endif; ?>

              <?php if ($done): ?>
                <p class="text-center bg-success text-light p-1">Uspešna registracija člana!</p>
              <?php endif; ?>

            </div>
          </div>
        </div>
      </div>
    </div>
  </form>

  <script src="./../../scripts/bootstrap.bundle.min.js"></script>

</body>

</html>