<?php
  // Funkcija koja proverava da li se podudaraju korisnicko ime i lozinka
  function checkUserPassword ($user, $password, $conn) {
    // sama promenljiva $password se hashuje md5 algoritmom
    $password = md5($password);

    // ovo znaci "IZABERI SVE IZ korisnici GDE JE korisnicko_ime = '$user' I lozinka = '$password'"
    $res = $conn->query("SELECT * FROM korisnici WHERE korisnicko_ime = '$user' AND lozinka = '$password'");
    
    // vracamo "DA LI POSTOJI RED IZ NASEG UPITA" ukoliko postoji -> true, u suprotnom -> false
    return $res->num_rows == 1;
  }

  // Isto kao funckija iznad samo sto se selektuje iz administratora
  function checkAdminPassword ($user, $password, $conn) {
    $password = md5($password);
    $res = $conn->query("SELECT * FROM administratori WHERE korisnicko_ime = '$user' AND lozinka = '$password'");
    return $res->num_rows == 1;
  }

  // Funkcija koja uzima ID korisnika na osnovu njegovog korisnickog imena
  function getUser($user, $conn) {
    // IZABERI id IZ korisnici GDE JE korisnicko_ime = '$user'"
    $result = $conn->query("SELECT id FROM korisnici WHERE korisnicko_ime = '$user';");

    // ukoliko postoji korisnik sa tim korisnickim imenom
    if ($result->num_rows > 0) {
      // uzimamo da je $row jednak vrednostima iz upita
      while($row = $result->fetch_assoc()) {
        // vracamo $row['id'], tj. ID korisnika sa korisnickim imenom $user
        return $row['id'];
      }
    }
  }

  // Ista kao funckija iznad samo sto se selektuje iz administratora
  function getAdmin($user, $conn) {
    $result = $conn->query("SELECT id FROM administratori WHERE korisnicko_ime = '$user';");

    if ($result->num_rows > 0) {
      while($row = $result->fetch_assoc()) {
       return $row['id'];
      }
    }
  }

?>