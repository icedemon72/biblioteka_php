<?php
session_start();

if (!isset($_SESSION['username']) && !isset($_SESSION['admin'])) {
  header('Location: ./../index.php');
  exit();
}

include('./../db.php');
include('./../functions/login_funkcije.php');

$knjige = array(
  'id' => array(),
  'naziv' => array(),
  'autor' => array(),
  'godina' => array(),
  'broj_primeraka' => array()
);

$sql = "SELECT * FROM knjige";

if(isset($_GET['query'])) {
  $query = $_GET['query'];
  $sql = "SELECT * FROM knjige WHERE autor LIKE '%$query%' OR naziv LIKE '%$query%'";
}

$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
  array_push($knjige["id"], $row["id"]);
  array_push($knjige["naziv"], $row["naziv"]);
  array_push($knjige["autor"], $row["autor"]);
  array_push($knjige["godina"], $row["godina"]);
  array_push($knjige["broj_primeraka"], $row["broj_primeraka"]);
}

if (isset($_SESSION['username'])) {
  $imaClanarinu = false; // ne vazi
  $vecImaKnjigu = false;
  $done = false;
  $obavestenje = false;
  
  $date = date_format(date_create(), 'Y-m-d');
  $userId = getUser($_SESSION['username'], $conn);

  $sql = "SELECT * FROM korisnici_clanarine WHERE vazi_do > '$date' AND korisnici_id = '$userId'";
  $result = $conn->query($sql);

  if ($result->num_rows == 1) {
    $imaClanarinu = true;
  }

  $sql = "SELECT * FROM preuzimanje WHERE korisnici_id = '$userId' AND vraceno = '0'";
  $result = $conn->query($sql);

  if ($result->num_rows == 1) {
    $vecImaKnjigu = true;
    $preuzimanjeId = $result->fetch_assoc()["id"];
    $sql = "SELECT * FROM obavestenja WHERE preuzimanje_id = '$preuzimanjeId' AND aktivno = 1";
    $result = $conn->query($sql);

    if($result->num_rows == 1) {
      $obavestenje = true;
    }
  }

  if(isset($_GET['knjiga']) && $imaClanarinu && !$vecImaKnjigu) {
    $knjiga = $_GET['knjiga'];
    $brojKnjiga = 0;

    $sql = "INSERT INTO preuzimanje (korisnici_id, knjige_id, rok)
            VALUES ('$userId', '$knjiga', DATE_ADD('$date', INTERVAL 14 DAY))";
    $conn -> query($sql);
    
    $sql = "SELECT broj_primeraka FROM knjige WHERE id = '$knjiga'";
    $result = $conn -> query($sql);
    while($row = $result -> fetch_assoc()) {
      $brojKnjiga = $row['broj_primeraka'];
    }

    $brojKnjiga--;
    $sql = "UPDATE knjige SET broj_primeraka = '$brojKnjiga' WHERE id = '$knjiga'";
    $conn->query($sql);

    $done = true;
    header('Refresh: 2, URL=./knjige.php');
  }

} else if (isset($_SESSION['admin'])) {
  header('Location: ./admin/upravljanje.php');
  exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="./../style/bootstrap.min.css">
  <link rel="stylesheet" href="./../style/index.css">
  <title>Biblioteka</title>
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
              <li><a class="dropdown-item" href="./uzete_knjige.php">Uzete knjige</a></li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li><a class="dropdown-item" href="#">Članarina: <?php echo ($imaClanarinu) ? 'aktivna' : 'neaktivna'; ?></a></li>
            </ul>
          </li>
          <?php if($obavestenje): ?>
            <li class="nav-item notification">
              <a class="nav-link" href="./uzete_knjige.php">Obaveštenje: Vrati knjigu!</a>
            </li>
          <?php endif; ?>
        </ul>
        <div class="d-flex">
          <a class="btn btn-light" href="./../logout.php">Izloguj se!</a>
        </div>
      </div>
    </div>
  </nav> 

  <div class="d-flex justify-content-end mt-2 me-2">
    <div class="w-50 md-w-100">
      <form method="get">
        <div class="input-group">
          <input type="search" name="query" class="form-control form-control-md rounded" 
          value="<?php echo (isset($_GET['query'])) ? $_GET['query'] : '' ?>"placeholder="Unesi naziv knjige ili autora..." aria-label="Search" aria-describedby="search-addon" />
          <button type="submit" class="btn btn-primary" data-mdb-ripple-init>Pretraži</button>
        </div>
      </form>
    </div>
  </div>
  
  <div class="container d-flex justify-content-center">
    <section class="row w-100 d-flex justify-content-start">
  
      <?php for ($i = 0; $i < sizeof($knjige['id']); $i++): ?>

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
                    <?php echo $knjige['autor'][$i]; ?> <br />
                    Na stanju: <?php echo $knjige['broj_primeraka'][$i]; ?>
                  </p>
                </li>
              </ul>
              <hr class="my-4" />
              <?php if($imaClanarinu): ?>
                <?php if($knjige['broj_primeraka'][$i] > 0): ?>
                  <?php if(!$vecImaKnjigu): ?>
                    <a href="?knjiga=<?php echo $knjige['id'][$i]; ?>">
                      <button class="btn btn-primary btn-lg w-100">Uzmi knjigu!</button>
                    </a>
                  <?php else: ?>
                    <p class="p-1 text-center">Već ste uzeli knjigu!</p>
                  <?php endif; ?>
                <?php else: ?>
                  <p class="p-1 text-center">Nema na stanju :(</p>
                <?php endif; ?>
              <?php else: ?>
                <p class="p-1 text-center">Nemate uplaćenu članarinu...</p>
              <?php endif; ?>

              <?php if($done && $_GET['knjiga'] === $knjige['id'][$i]): ?>
                <p class="text-center bg-success text-light p-1 mt-1">Uspešno ste uzeli knjigu!</p>
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