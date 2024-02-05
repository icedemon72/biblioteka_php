<?php
// pokrecemo sesiju
session_start();

// ukoliko korisnik nije ulogovan vracamo na login stranicu, ovo vazi i za administratore
// ali kad pristupe administratori index.php bice poslati na clanarine.php
if (!isset($_SESSION["username"])) {
  header("Location: ./../index.php");
  exit();
}

// ukljucujemo $conn promenljivu i funkcije
include('./../db.php');
include('./../functions/login_funkcije.php');

// pravimo asocijativni niz $knjige (objasnjeni su u knjige.php)
$knjige = array(
  'naziv' => array(),
  'autor' => array(),
  'datum_uzeto' => array(),
  'datum_vraceno' => array(),
  'rok' => array()
);

// danasnji datum
$date = date_format(date_create(), 'Y-m-d');

// na pocetku pretpostavljamo da korisnik nema clanarinu (ovo sluzi za navbar, tj. ona poruka gde pise do kada je aktivna clanarina)
$imaClanarinu = false;

// uzimamo korisnicki ID iz login_funkcije.php
$userId = getUser($_SESSION['username'], $conn);

// IZABERI SVE IZ korisnici_clanarine GDE JE vazi_do VECI OD $date I korisnici_id JEDNAK '$userId'"
$sql = "SELECT * FROM korisnici_clanarine WHERE vazi_do > '$date' AND korisnici_id = '$userId'";
$result = $conn->query($sql);

// ukoliko dobijemo povratnu vrednost, korisnik ima clanarinu
if ($result->num_rows == 1) {
  $imaClanarinu = true;
}

// ovo je malo komplikovani SQL query koji kaze:
// IZABERI (...) IZ KNJIGE SPOJI SA preuzimanje 
// NA knjige.id = preuzimanje.knjige_id GDE JE preuzimanje.korisnici_id = $userId 
// SORTIRAJ PO preuzimanje.id OPADAJUCE
// otp ovde uzimamo knjige iz "knjige" tabele i njihove odgovarajuce informacije iz 
// preuzimanje tabele, npr. knjiga "Primer" se cuva u "knjige" tabeli, korisnik ju je uzeo 01.01.2024.
// sad se to cuva u "preuzimanje" tabeli, a pomocu stranih kljuceva su ove dve povezane
// mozemo da uzmemo na osnovu ID-a knjige i ID-a korisnika informacije i o knjizi i o, npr, roku vracanja
$sql = "SELECT knjige.id, knjige.naziv, knjige.autor, knjige.broj_primeraka,
          preuzimanje.datum_uzeto, preuzimanje.datum_vraceno, preuzimanje.rok
        FROM knjige 
        INNER JOIN preuzimanje ON knjige.id = preuzimanje.knjige_id
        WHERE preuzimanje.korisnici_id = '$userId' 
        ORDER BY preuzimanje.id DESC";
$result = $conn -> query($sql);

// definisemo promenljive ali im ne dodajemo vrednost
$idKnjige;
$ukupnoKnjiga;

// ubacujemo sve u asocijativni niz
while ($row = $result->fetch_assoc()) {
  array_push($knjige['naziv'], $row['naziv']);
  array_push($knjige['autor'], $row['autor']);
  array_push($knjige['datum_uzeto'], $row['datum_uzeto']);
  array_push($knjige['datum_vraceno'], $row['datum_vraceno']);
  array_push($knjige['rok'], $row['rok']);

  // ukoliko datum_vraceno ne postoji, znaci da korisnik jos uvek nije vratio knjigu
  // pa cemo informacije o toj knjizi i njenom broju da cuvamo u posebnim promenljivama
  // koje ce nam olaksati posao prilikom vracanja te knjige
  if(!$row['datum_vraceno']) {
    $idKnjige = $row['id'];
    $ukupnoKnjiga = $row['broj_primeraka'];
  }

}

// ukoliko je korisnik kliknuo dugme za vracanje knjige 
if(isset($_POST['submit'])) {
  // IZABERI SVE IZ preuizmanje GDE JE korisnici_id = $userId I vraceno = 0
  $sql = "SELECT * FROM preuzimanje WHERE korisnici_id = '$userId' AND vraceno = 0";
  $result = $conn->query($sql);
  
  // samo definisemo promenljivu $preuzimanjeId
  $preuzimanjeId;

  // ukoliko postoji knjiga koja nije vracena
  if($result -> num_rows == 1) {
    // uzimamo $preuzimanjeid iz samog upita
    $preuzimanjeId = $result -> fetch_assoc()["id"];

    // AZURIRAJ preuzimanje POSTAVI DA JE vraceno = 1, datum_vraceno = $date (danasnji datum) 
    // GDE JE id = $preuzimanjeid
    $sql = "UPDATE preuzimanje SET vraceno = 1, datum_vraceno = '$date' 
            WHERE id = '$preuzimanjeId'";
    $conn->query($sql);
  
    // ukoliko je prosao gornji upit na prethodni broj knjiga dodajemo 1
    $ukupnoKnjiga++;
    // AZURIRAJ knjige POSTAVI DA JE broj_primeraka = $ukupnoKnjiga GDE JE id=$idKnjige
    // tj. mi ovde azuriramo knjige tabelu i na osnovu promenljivihi na linijama 60-61 
    // znamo koju knjigu je korisnik uzeo pa samo azuriramo vrednost ukupno knjiga
    $sql = "UPDATE knjige SET broj_primeraka = '$ukupnoKnjiga' WHERE id='$idKnjige'";
    $conn->query($sql);
    
    // ukoliko postoji obavestenje stavi da je aktivno = 0 (tj. da vise nije aktivno)
    $sql = "UPDATE obavestenja SET aktivno = 0 WHERE preuzimanje_id = '$preuzimanjeId'";
    $conn->query($sql); 

    // nakon zavrsetka svih naredbi refresh-uj stranicu posle 0s (momentalno)
    header('Refresh: 0');
  }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="./../style/bootstrap.min.css">
  <link rel="stylesheet" href="./../style/index.css">
  <title>Uzete knjige</title>
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
            <a class="nav-link" href="./knjige.php">Knjige</a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown"
              aria-expanded="false">
              Profil
            </a>
            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
              <li>
                <a class="dropdown-item active" href="./uzete_knjige.php">Uzete knjige</a>
              </li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li>
                <a class="dropdown-item" href="#">Članarina:
                  <?php echo ($imaClanarinu) ? 'aktivna' : 'neaktivna'; ?>
                </a>
              </li>
            </ul>
          </li>
        </ul>
        <div class="d-flex">
          <a class="btn btn-light" href="./../logout.php">Izloguj se!</a>
        </div>
      </div>
    </div>
  </nav>

  <div class="container d-flex justify-content-center">
    <section class="row w-100 d-flex justify-content-start">

      <?php for ($i = 0; $i < sizeof($knjige['naziv']); $i++): ?>

      <div class="col-sm-12 col-md-6 col-lg-4 d-flex justify-content-center">
        <div class="card my-2">
          <div class="bg-image hover-overlay ripple" data-mdb-ripple-color="light">
            <img src="./../images/knjiga.jpg" class="img-fluid rounded" alt="Slika knjige" />
          </div>
          <div class="card-body">
            <h5 class="card-title font-weight-bold">
              <?php echo $knjige['naziv'][$i]; ?>
            </h5>
            <ul class="list-unstyled list-inline mb-0">
              <li class="list-inline-item">
                <p class="text-muted">
                  <?php echo $knjige['autor'][$i]; ?>
                </p>
              </li>
            </ul>
            <hr class="my-4" />
            <p class="text-center">
              Knjiga uzeta: <?php echo date_format(date_create($knjige['datum_uzeto'][$i]), 'd. m. Y.'); ?>
            </p>
            
            <?php if(!$knjige['datum_vraceno'][$i]): ?>
              <form method="post">
                <div class="d-flex justify-content-center w-100">
                  <button type="submit" name="submit" class="btn btn-primary w-50 md-w-100">Vrati knjigu!</button>
                </div>
              </form>
              <p class="text-muted text-center">Rok za vraćanje: <?php echo date_format(date_create($knjige['rok'][$i]), 'd. m. Y.'); ?></p>
            <?php else: ?>
              <p class="text-center">
                Knjiga vraćena: <?php echo date_format(date_create($knjige['datum_vraceno'][$i]), 'd. m. Y.'); ?>
              </p>
            <?php endif; ?>
  
          </div>
        </div>
      </div>

      <?php endfor; ?>
    </section>
  </div>

  <script src="./../scripts/bootstrap.bundle.min.js"></script>
</body>

</html>