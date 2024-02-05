<?php
session_start();

if (!isset($_SESSION['admin'])) {
  header('Location: ./../../admin_login.php');
  exit();
}

include('./../../db.php');

$knjige = array(
  'id' => array(),
  'naziv' => array(),
  'autor' => array(),
  'godina' => array(),
  'broj_primeraka' => array()
);

$done = false;

if (isset($_GET['knjiga'])) {
  $knjigaId = $_GET['knjiga'];
  $sql = "SELECT * FROM knjige WHERE id = '$knjigaId'";
  $result = $conn->query($sql);

  while ($row = $result->fetch_assoc()) {
    $knjige["id"] = $row["id"];
    $knjige["naziv"] = $row["naziv"];
    $knjige["autor"] = $row["autor"];
    $knjige["godina"] = $row["godina"];
    $knjige["broj_primeraka"] = $row["broj_primeraka"];
  }

  if (isset($_POST['submit']) && $knjige['id'][0]) {
    $name = $_POST['name'];
    $author = $_POST['author'];
    $year = $_POST['year'];
    $number = $_POST['number'];
    $sql = "UPDATE knjige SET 
            naziv = '$name', 
            autor = '$author',
            godina = '$year',
            broj_primeraka = '$number'
            WHERE id = '$knjigaId'";

    $conn->query($sql);
    $done = true;
    header('Refresh: 1, URL=./upravljanje.php');
  }

} else {
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
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="./../../style/bootstrap.min.css">
  <link rel="stylesheet" href="./../../style/index.css">
  <title>Upravljanje knjigama</title>
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
              <li><a class="dropdown-item" href="./dodaj_knjigu.php">Dodaj knjige</a></li>
              <li><a class="dropdown-item active" href="./upravljanje.php">Upravljaj knjigama</a></li>
            </ul>
          </li>
        </ul>
        <div class="d-flex">
          <a class="btn btn-light" href="./../../logout.php">Izloguj se!</a>
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
    <?php if (!isset($_GET['knjiga'])): ?>
      <section class="row w-100 d-flex justify-content-start">

        <?php for ($i = 0; $i < sizeof($knjige['id']); $i++): ?>

          <div class="col-sm-12 col-md-6 col-lg-4 d-flex justify-content-center">
            <div class="card my-2">
              <div class="bg-image hover-overlay ripple" data-mdb-ripple-color="light">
                <img src="./../../images/knjiga.jpg" class="img-fluid rounded" alt="Slika knjige" />
              </div>
              <div class="card-body">
                <h5 class="card-title font-weight-bold w-100">
                  <?php echo $knjige['naziv'][$i]; ?> (<?php echo $knjige['godina'][$i]; ?>)
                </h5>
                <ul class="list-unstyled list-inline mb-0 w-100">
                  <li class="list-inline-item w-100">
                    <p class="text-muted w-100">
                      <?php echo $knjige['autor'][$i]; ?> <br />
                      Na stanju:
                      <?php echo $knjige['broj_primeraka'][$i]; ?>
                    </p>
                  </li>
                </ul>
                <a href="?knjiga=<?php echo $knjige['id'][$i]; ?>">
                  <button class="btn btn-primary btn-md w-100">Izmeni</button>
                </a>
              </div>
            </div>
          </div>

        <?php endfor; ?>
      </section>
    <?php else: ?>
      <form method="post" class="w-100">

        <div class="row d-flex justify-content-center align-items-center h-100">
          <div class="col-xl-9 col-md-10 col-sm-12">
            <h1 class="text-white mb-4">Izmeni knjigu</h1>
            <div class="card rounded">
              <div class="card-body">
                <div class="mb-3">
                  <label for="name" class="form-label">Naziv knjige</label>
                  <input name="name" type="text" class="form-control form-control-lg" id="name"
                    placeholder="Unesite naziv knjige" value="<?php echo $knjige['naziv']; ?>" required>
                </div>
                <div class="mb-3">
                  <label for="author" class="form-label">Autor</label>
                  <input name="author" type="text" class="form-control form-control-lg" id="author"
                    placeholder="Unesite autora knjige" value="<?php echo $knjige['autor']; ?>" required>
                </div>
                <div class="mb-3">
                  <label for="year" class="form-label">Godina izdavanja knjige</label>
                  <input name="year" type="number" max="<?php echo $date; ?>" class="form-control form-control-lg"
                    id="year" placeholder="Unesite godinu izdanja" value="<?php echo $knjige['godina']; ?>" required>
                </div>
                <div class="mb-3">
                  <label for="number" class="form-label">Broj primeraka knjige</label>
                  <input name="number" type="number" min="0" class="form-control form-control-lg" id="number"
                    placeholder="Unesite broj primeraka knjige" value="<?php echo $knjige['broj_primeraka']; ?>" required>
                </div>
                <div class="d-flex justify-content-center">
                  <button type="submit" name="submit" class="btn btn-primary btn-lg w-50 md-w-100 my-2">Izmeni!</button>
                </div>
                <div class="d-flex justify-content-center">
                  <?php if ($done): ?>
                    <p class="text-center bg-success text-light p-2 w-50 md-w-100">Knjiga uspešno izmenjena!</p>
                  <?php endif; ?>
                </div>
              </div>
            </div>

          </div>
        </div>

      </form>
    </div>

    <?php endif; ?>



  <script src="./../../scripts/bootstrap.bundle.min.js"></script>
</body>

</html>