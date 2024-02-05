<?php
// clanarine.php -> stranica gde admini dodaju i imaju pregled nad clanarinama korisnika
// pocinjemo sesiju
session_start();

// ukoliko nije ulogovan admin saljemo ga na admin_login.php
if (!isset($_SESSION['admin'])) {
  header('Location: ./../../admin_login.php');
  exit();
}

// uzimamo $conn promenljivu
include('./../../db.php');

// danasnji datum
$date = date_format(date_create(), 'Y-m-d');

// asocijativni niz (vidi knjige.php) korisnika
$korisnici = array(
  'id' => array(),
  'ime' => array(),
);

// asocijativni niz korisnickih ID-eva i informacija o clanarinama
$clanarine = array(
  'korisnici_id' => array(),
  'vazi_do' => array(),
  'trajanje' => array()
);

// IZABERI SVE IZ korisnici
$sql = "SELECT * FROM korisnici";

$result = $conn->query($sql);

// ubaci odgovarajuce vrednosti gde treba
while($row = $result->fetch_assoc()) {
  array_push($korisnici['id'], $row['id']);
  array_push($korisnici['ime'], $row['ime']);
}

// IZABERI (...) IZ korisnici_clanarine 
// SPOJI SA clanarine NA korisnici_clanarine.clanarine_id = clanarine.id
// GDE JE korisnici_clanarine.vazi_do VECI OD $date (danasnjeg datuma)
// ovde selektujemo clanarine i korisnicke ID-eve iz tabele korisnici_clanarine gde
// clanarina jos uvek vazi
$sql = "SELECT 
          korisnici_clanarine.korisnici_id, korisnici_clanarine.vazi_do, clanarine.trajanje
        FROM korisnici_clanarine
        INNER JOIN clanarine ON korisnici_clanarine.clanarine_id = clanarine.id
        WHERE korisnici_clanarine.vazi_do >= '$date'";
$result = $conn->query($sql);

// ubacujemo sve u niz
while($row = $result->fetch_assoc()) {
  array_push($clanarine['korisnici_id'], $row['korisnici_id']);
  array_push($clanarine['vazi_do'], $row['vazi_do']);
  array_push($clanarine['trajanje'], $row['trajanje']);
}
// pogledati linije 121+

?>

<!DOCTYPE html>
<html lang="en" class="login_body">

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

  <table class="table table-hover table-dark">
    <thead>
      <th scope="col">Ime korisnika</th>
      <th scope="col">Članarina</th>
      <th scope="col">Važi do</th>
      <th scope="col">Trajanje</th>
    </thead>
    <tbody>
      <!-- for petlja koja ide kroz sve korisnike -->
      <?php for ($i = 0; $i < sizeof($korisnici['id']); $i++): ?>
        <tr>
          <td><?php echo $korisnici['ime'][$i]?></td>
          <!-- definisemo promenljivu $index koja ima vrednost indeksa $korisnici['id'][$i] u $clanarine['korisnici_id]
               ovo znaci da ukoliko imamo niz [5, 1, 6], index od 6 bi bio 2 jer je niz[2] = 6 i sl. -->
          <?php $index = array_search($korisnici['id'][$i], $clanarine['korisnici_id']); ?>
          <!-- ukoliko ne postoji index, znaci da clanarina nije uplacena -->
          <?php if($index === false): ?>
            <td>Nije uplaćena</td>
            <td>-</td>
            <td><a class="btn btn-primary" href="./korisnici.php?korisnik=<?php echo $korisnici['id'][$i];?>">Uplati?</td>
          <?php else: ?>
            <!-- U suprotnom, uplacena je -->
              <td>Uplaćena</td>
              <td><?php echo date_format(date_create($clanarine['vazi_do'][$index]), 'd.m.Y.'); ?></td>
              <td><?php echo $clanarine['trajanje'][$index]; ?>M</td>
          <?php endif; ?>

        </tr>
      <?php endfor; ?>
    </tbody>
  </table>

  <script src="./../../scripts/bootstrap.bundle.min.js"></script>
</body>

</html>