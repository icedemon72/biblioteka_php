<?php
// knjige.php -> stranica gde pokazujemo sve knjige koje KORISNIK moze da uzme
// ukoliko admin pristupi ovoj stranici bice poslat na stranicu za uredjivanje knjiga
session_start();

// ukoliko ni korisnik ni admin nisu ulogovani
if (!isset($_SESSION['username']) && !isset($_SESSION['admin'])) {
  header('Location: ./../index.php');
  exit();
}

// ukljucujemo $conn i funkcije
include('./../db.php');
include('./../functions/login_funkcije.php');

// ovo je asocijativni niz u PHP-u, on ide u paru key=>value, tj svaki "kljuc" ima sebi dodeljenu vrednost
// u ovom slucaju, asocijativni niz $knjige, cuva druge nizove $id, $naziv... $broj_primeraka gde cemo smestiti
// informacije o svakoj knjizi
// znamo da pripadaju istoj knjizi na osnovu njihove pozicije, 
// npr knjizi sa id-em $knjige['id'][0] odgovarace naziv sa nazivom $knjige['naziv'][0] itd.
// ==== POGLEDATI LINIJE 217+ ====
$knjige = array(
  'id' => array(),
  'naziv' => array(),
  'autor' => array(),
  'godina' => array(),
  'broj_primeraka' => array()
);

// "IZABERI SVE IZ knjige"
$sql = "SELECT * FROM knjige";

// ovo sluzi za pretragu, ovde menjamo $sql zavisno od toga da li je uneseno nesto u nas Search bar
if(isset($_GET['query'])) {
  // GET request funkcionise na osnovu linka, sve posle ? su promenljive koje se koriste
  // Youtube uzima GET request prilikom ucitavanja videa promenljiva v je ID ili slicno samog videa u linku:
  // www.youtube.com/watch?v=123 <- v = 123, video sa ID-em 123 (na primer)
  // u slucaju ovog sajta, GET promenljiva je query (testirati sa nekim unosom u search bar radi lakseg shvatanja)
  $query = $_GET['query'];
  // IZABERI SVE IZ knjige GDE JE autor KAO '$query' ILI naziv KAO '$query'. %$query% -> oznacava da se trazi slicnost i sa leve i sa desne strane
  // npr ukoliko unesemo "knjiga", izaci ce nam 123knjiga i knjiga123 (isto vazi i za autora)
  $sql = "SELECT * FROM knjige WHERE autor LIKE '%$query%' OR naziv LIKE '%$query%'";
}

// sada u nas $result unosimo vrednosti koje su dobijene prethodnim upitom
// ovde je vazno napomenuti da ukoliko nije unesena pretraga uzece se sve knjige (vidi liniju 30 i 33)
$result = $conn->query($sql);

// ubacujemo u nas asocijativni niz sve vrednosti koje dobijemo iz $sql upita
while ($row = $result->fetch_assoc()) {
  array_push($knjige["id"], $row["id"]);
  array_push($knjige["naziv"], $row["naziv"]);
  array_push($knjige["autor"], $row["autor"]);
  array_push($knjige["godina"], $row["godina"]);
  array_push($knjige["broj_primeraka"], $row["broj_primeraka"]);
}

// sada proveravamo da li postoji sesijska promenljiva 'username'
if (isset($_SESSION['username'])) {
  // na pocetku, pretpostavimo da korisnik nema clanarinu, da nema uzetu knjigu i da nema obavestenje  
  // $done sluzi da se izbaci poruka ukoliko se uzme knjiga
  $imaClanarinu = false; // ne vazi
  $vecImaKnjigu = false;
  $done = false;
  $obavestenje = false;
  
  // uzimamo trenutni datum i formatiramo ga Godina-mesec-dan (02.03.2024 -> 2024-03-02)
  $date = date_format(date_create(), 'Y-m-d');

  // uzimamo ID na osnovu korisnickog imena (pogledati getUser funkciju u login_funkcije.php)
  $userId = getUser($_SESSION['username'], $conn);

  // IZABERI SVE IZ korisnici_clanarine GDE JE vazi_do VECI OD $date I korisnici_id JEDNAK '$userId'"
  // ovo oznacava da se izaberu vazece clanarine (ciji je vazi_do datum veci od danasnjeg) gde je korisnicki ID
  // jednak onom uzetom iz sesije
  $sql = "SELECT * FROM korisnici_clanarine WHERE vazi_do > '$date' AND korisnici_id = '$userId'";
  $result = $conn->query($sql);

  // ukoliko postoji row (red) koji nam se vrati prilikom upita znaci da korisnik ima clanarinu
  if ($result->num_rows == 1) {
    $imaClanarinu = true;
  }

  // IZABERI SVE IZ preuzimanje GDE JE korisnici_id = $userId I vraceno = 0
  // ovde proveravamo da li korisnik ima uzetu knjigu koju nije vratio
  $sql = "SELECT * FROM preuzimanje WHERE korisnici_id = '$userId' AND vraceno = '0'";
  $result = $conn->query($sql);

  // ukoliko ima nevracenu knjigu
  if ($result->num_rows == 1) {
    // podesavamo da je $vecImaKnjigu tacno
    $vecImaKnjigu = true;

    // uzimamo da je $preuizmanjeId jednak 'id' iz naseg upita
    $preuzimanjeId = $result->fetch_assoc()["id"];

    // IZABERI SVE IZ obavestenja GDE JE preuzimanje_id = '$preuzimanjeId' I aktivno = 1
    // ovde proveravamo da li je admin poslao obavestenje na osnovu ID-a preuzimanja
    $sql = "SELECT * FROM obavestenja WHERE preuzimanje_id = '$preuzimanjeId' AND aktivno = 1";
    $result = $conn->query($sql);

    // ukoliko je admin poslao obavestenje $obavestenje ce biti true
    if($result->num_rows == 1) {
      $obavestenje = true;
    }
  }

  // ukoliko se klikne dugme za uzimanje knjige i ukoliko korisnik ima clanarinu i nema vec uzetu knjigu
  if(isset($_GET['knjiga']) && $imaClanarinu && !$vecImaKnjigu) {
    // knjigu uzimamo iz linka pomocu GET metode
    $knjiga = $_GET['knjiga'];
    
    // na pocetku, pretpostavljamo da je broj knjiga jednak nuli
    $brojKnjiga = 0;

    // UBACI U preuzimanje (korisnici_id ...) VREDNOSTI (...)
    // kao rok uzimamo interval [danasnji_datum, danasnji_datum + 14 dana] koji koristimo prilikom odredjivanja roka
    $sql = "INSERT INTO preuzimanje (korisnici_id, knjige_id, rok)
            VALUES ('$userId', '$knjiga', DATE_ADD('$date', INTERVAL 14 DAY))";
    $conn -> query($sql);
    
    // IZABERI broj_primeraka IZ knjige GDE JE id = $knjiga
    $sql = "SELECT broj_primeraka FROM knjige WHERE id = '$knjiga'";
    $result = $conn -> query($sql);

    // uzimamo $brojKnjiga
    while($row = $result -> fetch_assoc()) {
      $brojKnjiga = $row['broj_primeraka'];
    }

    // koji god broj da smo dobili smanjujemo za jedan
    $brojKnjiga--;

    // AZURIRAJ knjige STAVI DA JE broj_primeraka = $brojKnjiga GDE JE id = $knjiga
    // tj. kada uzmemo knjigu, moramo smanjiti broj te knjige u bazi podataka
    $sql = "UPDATE knjige SET broj_primeraka = '$brojKnjiga' WHERE id = '$knjiga'";
    $conn->query($sql);

    // $done stavljamo da je true i refresh-uje se stranica posle 2 sekunde
    $done = true;
    header('Refresh: 2, URL=./knjige.php');
  }
// ovo je else if za if (isset($_SESSION['username'])), tj. ovde proveravamo ako nije ulogovan korisnik
// da li je ulogovan administrator i ako jeste posalji ga na upravljanje.php 
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
          <!-- Ukoliko je poslato obavestenje prikazi ga kao link ka vracanju knjiga -->
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
       <!-- Ovde imamo for petlju koja ide od 0 do velicine 'id' kljuca u asocijativnom nizu 
            Na ovaj nacin mi mozemo da izlistamo sve knjige u bilo kom formatu mi zelimo
            a znamo da je knjiga['id'][0] ID knjige sa nazivom ...['naziv'][0], autorom ['autor'][0] itd.
            pa na osnovu toga mozemo da napravimo petlju koja prolazi kroz sve vrednosti i tako ih responzivno ispisemo
            pomocu echo komande (videti kod ispod), mozemo koristiti i if uslove da prikazemo knjigu koja je uzeta, itd. -->
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