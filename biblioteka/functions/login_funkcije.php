<?php
  function checkUserPassword ($user, $password, $conn) {
    $password = md5($password);
    $res = $conn->query("SELECT * FROM korisnici WHERE korisnicko_ime = '$user' AND lozinka = '$password'");
    return $res->num_rows == 1;
  }

  function checkAdminPassword ($user, $password, $conn) {
    $password = md5($password);
    $res = $conn->query("SELECT * FROM administratori WHERE korisnicko_ime = '$user' AND lozinka = '$password'");
    return $res->num_rows == 1;
  }

  function getUser($user, $conn) {
    $result = $conn->query("SELECT id FROM korisnici WHERE korisnicko_ime = '$user';");

    if ($result->num_rows > 0) {
      while($row = $result->fetch_assoc()) {
       return $row['id'];
      }
    }
  }

  function getAdmin($user, $conn) {
    $result = $conn->query("SELECT id FROM administratori WHERE korisnicko_ime = '$user';");

    if ($result->num_rows > 0) {
      while($row = $result->fetch_assoc()) {
       return $row['id'];
      }
    }
  }

?>