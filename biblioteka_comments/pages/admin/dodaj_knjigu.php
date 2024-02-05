<?php
// dodaj_knjigu.php -> stranica gde admini dodaju knjige
// pokrecemo sesiju
session_start();

// ukoliko admin nije ulogovan prebaci ga na admin_login.php
if (!isset($_SESSION["admin"])) {
  header("Location: ./../../admin_login.php");
  exit();
}

// danasnji datum u formatu Y - datum 01.01.2024 bio bi 2024
// tj. ovo je samo godina i ovo se koristi da se ne bi dodale knjige u buducnosti
// pogledati <input ... max={... $date}>
$date = $date = date_format(date_create(), 'Y');

// ukljuci $conn promenljivu
include('./../../db.php');

// na pocetku dodavanja knjige nemamo gresku, niti smo uspeli da je dodamo
$done = false;
$error = false;

// ukoliko je kliknuto dugme
if (isset($_POST['submit'])) {
  // uzimamo informacije iz <input ... /> polja
  $name = $_POST['name'];
  $author = $_POST['author'];
  $year = $_POST['year'];
  $number = $_POST['number'];

  // IZABERI SVE IZ knjige GDE JE naziv = $name I autor = $autor I godina = $year
  $sql = "SELECT * FROM knjige WHERE naziv = '$name' AND autor = '$author' AND godina = '$year'";
  $result = $conn -> query($sql);
  // ukoliko imamo row koji nam je vracen, znaci da knjiga vec postoji -> izbaci gresku
  if($result->num_rows == 1) {
    $error = true;
  } else {
    // u suprotnom, nemamo gresku i trebamo ubaciti knjigu
    // UBACI U knjige (...) VREDNOSTI (...)
    $sql = "INSERT INTO knjige (naziv, autor, godina, broj_primeraka)
            VALUES('$name', '$author', '$year', '$number')";

    $conn->query($sql);
    // postavljamo da smo zavrsili ($done = true) i refresh-ujemo stranicu nakon 2s
    $done = true;
    header('Refresh:2; URL=./dodaj_knjigu.php');
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
  <title>Dodavanje knjiga</title>
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
              <li><a class="dropdown-item" href="./registracija.php">Registracija korisnika</a></li>
              <li><a class="dropdown-item" href="./clanarine.php">Članarine</a></li>
              <li><a class="dropdown-item" href="./obavesti.php">Obavesti korisnike</a></li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li><a class="dropdown-item active" href="./dodaj_knjigu.php">Dodaj knjige</a></li>
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
          <h1 class="text-white mb-4">Dodaj knjigu</h1>
          <div class="card rounded">
            <div class="card-body">
              <div class="mb-3">
                <label for="name" class="form-label">Naziv knjige</label>
                <input name="name" type="text" class="form-control form-control-lg" id="name" placeholder="Unesite naziv knjige" required>
              </div>
              <div class="mb-3">
                <label for="author" class="form-label">Autor</label>
                <input name="author" type="text" class="form-control form-control-lg" id="author" placeholder="Unesite autora knjige" required>
              </div>
              <div class="mb-3">
                <label for="year" class="form-label">Godina izdavanja knjige</label>
                <input name="year" type="number" max="<?php echo $date; ?>" class="form-control form-control-lg" id="year"
                  placeholder="Unesite godinu izdanja" required>
              </div>
              <div class="mb-3">
                <label for="number" class="form-label">Broj primeraka knjiga</label>
                <input name="number" type="number" min="1" value="1" class="form-control form-control-lg" id="number"
                  placeholder="Unesite broj primeraka knjige" required>
              </div>
              <div class="d-flex justify-content-center">
                <button type="submit" name="submit" class="btn btn-primary btn-lg w-50 md-w-100 my-2">Unesi knjigu!</button>
              </div>
              <div class="d-flex justify-content-center">
                <?php if ($error): ?>
                  <p class="text-center bg-danger text-light p-2 w-50 md-w-100">Knjiga već postoji u bazi!</p>
                <?php endif; ?>
  
                <?php if ($done): ?>
                  <p class="text-center bg-success text-light p-2 w-50 md-w-100">Uspešno dodavanje knjige!</p>
                <?php endif; ?>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
    </div>
  </form>

  <script src="./../../scripts/bootstrap.bundle.min.js"></script>
</body>

</html>