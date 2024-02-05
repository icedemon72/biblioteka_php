<?php
// obavesti.php -> tabela korisnika gde se vide sve uzete knjige

// pokrecemo sesiju
session_start();

// proveravmo da li admin pristupa ovoj stranici
if(!isset($_SESSION['admin'])) {
  header('Location: ./../../admin_login.php');
  exit();
}

// ukljucujemo $conn promenljivu
include('./../../db.php');

// kreiramo danasnji datum
$date = date_format(date_create(), 'Y-m-d');

// pravimo asocijativni niz "preuzimanje"
// gde skladistimo sve informacije o preuzimanjima knjiga, kao i o tome da li je
// potrebno poslati obavestenje
$preuzimanje = array(
  'preuzimanje_id' => array(),
  'korisnik_ime' => array(),
  'knjiga_ime' => array(),
  'datum_uzeto' => array(),
  'datum_vraceno' => array(),
  'rok' => array(),
  'obavestenje' => array()
);


// ovo je niz koji se koristi da bi se, u tabeli, pokazalo kome je obavestenje poslato
$obavestenaPreuzimanja = array();

// IZABERI (...) IZ korisnici SPOJI SA preuzimanje NA korisnici.id = preuzimanje.korisnici_id
// SPOJI SA knjige NA preuzimanje.knjige_id = knjige.id SORTIRAJ PO (...)
// ovde uzimamo podatke o korisniku koji je preuzeo odredjenu knjigu i podatke o preuzetoj knjizi
// i podatke o samom preuzimanju (rok, da li je vraceno i sl)
$sql = "SELECT 
          korisnici.ime AS korisnik_ime, knjige.naziv AS knjiga_ime, knjige.id AS knjiga_id,
          preuzimanje.id AS preuzimanje_id,
          preuzimanje.datum_uzeto, preuzimanje.datum_vraceno, preuzimanje.rok
        FROM korisnici 
        INNER JOIN preuzimanje ON korisnici.id = preuzimanje.korisnici_id
        INNER JOIN knjige ON preuzimanje.knjige_id = knjige.id
        ORDER BY preuzimanje.vraceno ASC, preuzimanje.id DESC";
$result = $conn->query($sql);

// ubacujemo vrednosti u $preuzimanje asoc. niz
while($row = $result->fetch_assoc()) {
  array_push($preuzimanje['preuzimanje_id'], $row['preuzimanje_id']);
  array_push($preuzimanje['korisnik_ime'], $row['korisnik_ime']);
  array_push($preuzimanje['knjiga_ime'], $row['knjiga_ime']);
  array_push($preuzimanje['datum_uzeto'], $row['datum_uzeto']);
  array_push($preuzimanje['datum_vraceno'], $row['datum_vraceno']);
  array_push($preuzimanje['rok'], $row['rok']);

  // pretpostavimo da nije potrebno poslati obavestenje
  $obavestenje = false;
  // ukoliko jos uvek knjiga nije vracena
  if(!$row['datum_vraceno']) {
    // proveravamo da li je razlika izmedju roka i danasnjeg datuma manja od 2 dana
    if(floor((strtotime($row['rok']) - strtotime($date)) / (60 * 60 * 24)) <= 2) {
      // ukoliko jeste, imamo mogucnost slanja obavestenja korisniku
      $obavestenje = true;
    } 
  } 

  // dodajemo vrednost (true ili false) u asoc. niz
  array_push($preuzimanje['obavestenje'], $obavestenje);
} 

// aktivno = 0 -> nije poslato obavestenje
// aktivno = 1 -> poslato je obavestenje
// aktivno = -1 -> vracena je knjiga

// IZABERI SVE IZ obavestenja GDE JE akitvno = 1
$sql = "SELECT * FROM obavestenja WHERE aktivno = 1";
$result = $conn->query($sql);

while($row = $result->fetch_assoc()) {
  // u $obavestenaPreuzimanja ubaci ID preuzimanja, tj. ukoliko je vec poslato obavestenje
  // uzecemo ID tog preuzimanja, da ne bi slali milion obavestenja, vec samo jedno
  array_push($obavestenaPreuzimanja, $row['preuzimanje_id']);
}

// ukoliko je pritisnuto dugme za slanje obavestenja (u tabeli)
if(isset($_GET['obavesti'])) {
  // uzimamo ID preuzimanje iz URL-a GET metodom
  $preuzimanjeId = $_GET['obavesti'];
  // UBACI U obavestenja (...) VREDNOSTI (...) UKOLIKO JE ID ISTI AZURIRAJ POSTAVI DA JE aktivno = 1
  // ovo znaci da ukoliko se posalje obavestenje sa istim preuzimanje_id (tj. za isto preuzimanje) 
  // nece se praviti nova instanca u bazi, vec ce se azurirati vec postojeca
  $sql = "INSERT INTO obavestenja(preuzimanje_id) VALUES ('$preuzimanjeId') ON DUPLICATE KEY UPDATE aktivno = 1";
  $conn->query($sql);
  // refreshuj stranicu na kraju
  header('Refresh: 0, URL=./obavesti.php');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="./../../style/bootstrap.min.css">
  <link rel="stylesheet" href="./../../style/index.css">
  <title>Obavesti korisnike</title>
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
  <table class="table table-hover table-dark">
    <thead>
      <th scope="col">Ime korisnika</th>
      <th scope="col">Ime knjige</th>
      <th scope="col">Datum preuzimanja</th>
      <th scope="col">Datum vraćanja</th>
      <th scope="col">Rok</th>
    </thead>
    <tbody>
      <!-- Izlistaj sva preuizmanja -->
      <?php for ($i = 0; $i < sizeof($preuzimanje['rok']); $i++): ?>
        <tr>
          <!-- Ispisi informacije o korisnicima -->
          <td><?php echo $preuzimanje['korisnik_ime'][$i]; ?></td>
          <td><?php echo $preuzimanje['knjiga_ime'][$i]; ?></td>
          <td><?php echo date_format(date_create($preuzimanje['datum_uzeto'][$i]), 'd.m.Y.'); ?></td>
            <!-- Ukoliko imamo razloga za obavestenje (2 dana rok ili je vec poslato) -->
            <?php if($preuzimanje['obavestenje'][$i]): ?>
              <!-- Ukoliko je vec poslato ispisi "Obavestenje poslato!" -->
              <?php if(in_array($preuzimanje['preuzimanje_id'][$i], $obavestenaPreuzimanja)): ?>
                <td>Obaveštenje poslato!</td>
              <!-- U suprotnom, postavi link za GET metodu kojom ce se slati obavestenja -->
              <?php else: ?>
                <td><a class="btn btn-primary" href="?obavesti=<?php echo $preuzimanje['preuzimanje_id'][$i];?>">Obavesti!</a></td>
              <?php endif; ?>
            <!-- Ukoliko nemamo razloga za obavestenje ispisacemo datum kada je knjiga vracena -->
            <?php else: ?>
              <td><?php echo date_format(date_create($preuzimanje['datum_vraceno'][$i]), 'd.m.Y.'); ?></td>
            <?php endif; ?>
          <td><?php echo date_format(date_create($preuzimanje['rok'][$i]), 'd.m.Y'); ?></td>
        </tr>
      <?php endfor; ?>
    </tbody>
  </table>
  <script src="./../../scripts/bootstrap.bundle.min.js"></script>
</body>

</html>