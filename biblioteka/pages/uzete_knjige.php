<?php
session_start();
if (!isset($_SESSION["username"])) {
  header("Location: ./../index.php");
  exit();
}

include('./../db.php');
include('./../functions/login_funkcije.php');

$knjige = array(
  'naziv' => array(),
  'autor' => array(),
  'datum_uzeto' => array(),
  'datum_vraceno' => array(),
  'rok' => array()
);

$date = date_format(date_create(), 'Y-m-d');
$imaClanarinu = false;

$userId = getUser($_SESSION['username'], $conn);

$sql = "SELECT * FROM korisnici_clanarine WHERE vazi_do > '$date' AND korisnici_id = '$userId'";
$result = $conn->query($sql);

if ($result->num_rows == 1) {
  $imaClanarinu = true;
}

$sql = "SELECT knjige.id, knjige.naziv, knjige.autor, knjige.broj_primeraka,
          preuzimanje.datum_uzeto, preuzimanje.datum_vraceno, preuzimanje.rok
        FROM knjige 
        INNER JOIN preuzimanje ON knjige.id = preuzimanje.knjige_id
        WHERE preuzimanje.korisnici_id = '$userId' 
        ORDER BY preuzimanje.id DESC";
$result = $conn -> query($sql);

$idKnjige;
$ukupnoKnjiga;

while ($row = $result->fetch_assoc()) {
  array_push($knjige['naziv'], $row['naziv']);
  array_push($knjige['autor'], $row['autor']);
  array_push($knjige['datum_uzeto'], $row['datum_uzeto']);
  array_push($knjige['datum_vraceno'], $row['datum_vraceno']);
  array_push($knjige['rok'], $row['rok']);

  if(!$row['datum_vraceno']) {
    $idKnjige = $row['id'];
    $ukupnoKnjiga = $row['broj_primeraka'];
  }

}

if(isset($_POST['submit'])) {
  $sql = "SELECT * FROM preuzimanje WHERE korisnici_id = '$userId' AND vraceno = 0";
  $result = $conn->query($sql);
  $preuzimanjeId;
  if($result -> num_rows == 1) {
    $preuzimanjeId = $result -> fetch_assoc()["id"];
    $sql = "UPDATE preuzimanje SET vraceno = 1, datum_vraceno = '$date' 
            WHERE id = '$preuzimanjeId'";
    $conn->query($sql);
  
    $ukupnoKnjiga++;
    $sql = "UPDATE knjige SET broj_primeraka = '$ukupnoKnjiga' WHERE id='$idKnjige'";
    $conn->query($sql);
    
    $sql = "UPDATE obavestenja SET aktivno = 0 WHERE preuzimanje_id = '$preuzimanjeId'";
    $conn->query($sql); 

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