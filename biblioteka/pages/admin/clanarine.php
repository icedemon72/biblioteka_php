<?php
session_start();

if (!isset($_SESSION['admin'])) {
  header('Location: ./../../admin_login.php');
  exit();
}

include('./../../db.php');

$date = date_format(date_create(), 'Y-m-d');

$korisnici = array(
  'id' => array(),
  'ime' => array(),
);

$clanarine = array(
  'korisnici_id' => array(),
  'vazi_do' => array(),
  'trajanje' => array()
);

$sql = "SELECT * FROM korisnici";

$result = $conn->query($sql);

while($row = $result->fetch_assoc()) {
  array_push($korisnici['id'], $row['id']);
  array_push($korisnici['ime'], $row['ime']);
}

$sql = "SELECT 
          korisnici_clanarine.korisnici_id, korisnici_clanarine.vazi_do, clanarine.trajanje
        FROM korisnici_clanarine
        INNER JOIN clanarine ON korisnici_clanarine.clanarine_id = clanarine.id
        WHERE korisnici_clanarine.vazi_do >= '$date'";
$result = $conn->query($sql);

while($row = $result->fetch_assoc()) {
  array_push($clanarine['korisnici_id'], $row['korisnici_id']);
  array_push($clanarine['vazi_do'], $row['vazi_do']);
  array_push($clanarine['trajanje'], $row['trajanje']);
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

  <table class="table table-hover table-dark">
    <thead>
      <th scope="col">Ime korisnika</th>
      <th scope="col">Članarina</th>
      <th scope="col">Važi do</th>
      <th scope="col">Trajanje</th>
    </thead>
    <tbody>
      <?php for ($i = 0; $i < sizeof($korisnici['id']); $i++): ?>
        <tr>
          <td><?php echo $korisnici['ime'][$i]?></td>
          <?php $index = array_search($korisnici['id'][$i], $clanarine['korisnici_id']); ?>
          <?php if($index === false): ?>
            <td>Nije uplaćena</td>
            <td>-</td>
            <td><a class="btn btn-primary" href="./korisnici.php?korisnik=<?php echo $korisnici['id'][$i];?>">Uplati?</td>
          <?php else: ?>
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