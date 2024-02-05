<?php
// korisnici.php -> stranica gde dodajemo zeljenu clanarinu zeljenom korisniku
// pokrecemo sesiju
session_start();

// provera da li je admin
if (!isset($_SESSION['admin'])) {
  header('Location: ./../../admin_login.php');
  exit();
}

// ukljucujemo $conn promenljivu i funkcije
include('./../../db.php');
include('./../../functions/login_funkcije.php');

// ukoliko nije set-ovan ID korisnika vrati ga (admina) na clanarine.php
if(!isset($_GET['korisnik'])) {
  header('Location: ./clanarine.php');
  exit();
}

// vidi knjige.php linije 35-38 za objasnjenje GET request-a
$userId = $_GET['korisnik'];

// kreiramo danasnji datum i skladistimo ga u promenljivu $date
$date = date_format(date_create(), 'Y-m-d');

// asocijativni niz (vidi knjige.php 16-21 za objasnjenje) $clanarine
$clanarine = array(
  'id' => array(),
  'trajanje' => array(),
  'cena' => array()
);

// na pocetku, definisemo promenljive koje imaju vrednost ''
$korisnikIme = '';
$korisnikKorIme = '';
$korisnikTelefon = '';

// IZABERI SVE IZ clanarine
$sql = "SELECT * FROM clanarine";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
  // dodajemo u asocijativni niz vrednosti
  array_push($clanarine['id'], $row['id']);
  array_push($clanarine['trajanje'], $row['trajanje']);
  array_push($clanarine['cena'], $row['cena']);
}

// na pocetku dodavanja clanarine nemamo gresku, niti smo zavrsili
$done = false;
$error = false;

// IZABERI SVE IZ korisnici GDE JE id = $userId 
$sql = "SELECT * FROM korisnici WHERE id = '$userId'";
$result = $conn->query($sql);
// te vrednosti dodajemo nasim prethodno definisanim promenljivama
while($row = $result->fetch_assoc()) {
  $korisnikIme = $row['ime'];
  $korisnikKorIme = $row['korisnicko_ime'];
  $korisnikTelefon = $row['telefon'];
}

// ukoliko je kliknuto dugme za dodavanje clanarine
if(isset($_POST['submit'])) {
  // uzimamo vrednost <select name="sub"> -> sub kao subscription - pretplata
  $sub = $_POST['sub'];

  // ukoliko $sub nije none (tj ako je izabrano polje koje nije "Izaberi clanarinu")
  if($sub != 'none') {
    // pretpostavljamo da je trajanje nase izabrane clanarine 1
    $subDuration = 1;
    // IZABERI SVE IZ clanarine GDE JE id = $sub
    $sql = "SELECT * FROM clanarine WHERE id = '$sub'";
    $result = $conn->query($sql);
    // podesavamo da je $subDuration onoliko koliko je izabrano u polju
    while ($row = $result->fetch_assoc()) {
      $subDuration = $row['trajanje'];
    }
    // UBACI U korisnici_clanarine (...) VREDNOSTI (...)
    $sql = "INSERT INTO korisnici_clanarine (korisnici_id, clanarine_id, vazi_do)
            VALUES('$userId', '$sub', DATE_ADD('$date', INTERVAL '$subDuration' MONTH))";
  
    $conn->query($sql);
    // na kraju, kazemo da je zavrsen proces dodavanja clanarine, tj. $done = true
    $done = true;
    header('Refresh: 1, URL=./clanarine.php');
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

  <title>Članarine</title>
</head>

<body>
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
              <li><a class="dropdown-item active" href="./clanarine.php">Članarine</a></li>
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

          <h1 class="text-white mb-4">Dodaj članarinu</h1>

          <div class="card rounded">
            <div class="card-body">
              <div class="row align-items-center pt-4 pb-3">
                <div class="col-md-3 ps-5">
                  <h6 class="mb-0">Ime i prezime</h6>
                </div>
                <div class="col-md-9 pe-5">
                  <p class="form-control form-control-lg"><?php echo $korisnikIme; ?></p>
                </div>
              </div>

              <hr class="mx-n2">

              <div class="row align-items-center py-3">
                <div class="col-md-3 ps-5">
                  <h6 class="mb-0">Korisničko ime</h6>
                </div>
                <div class="col-md-9 pe-5">
                  <p class="form-control form-control-lg"><?php echo $korisnikKorIme; ?></p>
                </div>
              </div>

              <hr class="mx-n2">

              <div class="row align-items-center py-3">
                <div class="col-md-3 ps-5">
                  <h6 class="mb-0">Telefon</h6>
                </div>
                <div class="col-md-9 pe-5">
                  <p class="form-control form-control-lg"><?php echo $korisnikTelefon; ?></p>
                </div>
              </div>

              <hr class="mx-n2">

              <div class="row align-items-center py-3">
                <div class="col-md-3 ps-5">
                  <h6 class="mb-0">Članarina</h6>
                </div>
                <div class="col-md-9 pe-5">
                  <select name="sub" type="text" class="form-control form-control-lg">
                    <option value="none">Izaberi članarinu</option>
                    <!-- Na osnovu podataka iz asoc. niza $clanarine listamo ih sve pomocu for petlje-->
                    <?php for ($i = 0; $i < sizeof($clanarine['cena']); $i++): ?>
                    <option value="<?php echo $clanarine['id'][$i] ?>">
                      <?php echo $clanarine['trajanje'][$i] . 'M (' . $clanarine['cena'][$i] . ' RSD)' ?>
                    </option>
                    <?php endfor; ?>
                  </select>
                </div>
              </div>

              <div class="px-5 py-4 d-flex justify-content-center">
                <button type="submit" name="submit" class="btn btn-primary btn-lg w-50 md-w-100">Učlani korisnika!</button>
              </div>

              <?php if ($done): ?>
              <p class="text-center bg-success text-light p-1">Uspešno dodavanje članarine!</p>
              <?php endif; ?>

            </div>
          </div>
        </div>
      </div>
    </div>
  </form>

</body>


</html>