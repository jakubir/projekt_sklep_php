<?php
	session_start();
	if(!isset($_SESSION["zalogowany"]) || $_SESSION["zalogowany"] == false){
		$_SESSION["zalogowany"] = false;
		header("refresh: 0; url=logowanie.php");
  }else{
    $link = @mysqli_connect("localhost", "root", "", "sklep_projekt");
    if(!$link){
      echo "Błąd połączenia";
      header("refresh: 20; url=index.php");
    }
    $id = $_SESSION['id_u'];
    $query = "SELECT `autoryzacja`, `prosba` FROM `uzytkownicy` WHERE `id` LIKE '$id'";
    $result = mysqli_query($link, $query);
    $wynik = mysqli_fetch_row($result);
    if($wynik[0] == 'nie'){
      header("refresh: 0; url=autoryzacja.php");
    }elseif($wynik[1] != NULL){
      header("refresh: 0; url=prosba.php");
    }
    require_once("czyszczenie.php");
  }
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>POL&ROLLS</title>
    <link rel="stylesheet" href="style.css">
  </head>
  <body>

    <nav>
      <div class="container">
        <div class="logo">
          <a href="index.php">POL & ROLLS</a>
        </div>
        <div class="menu">
          <ul>
            <li><img src="user.png" alt="Konto">
              <div class="menu-plus menu-u">
                <?php
                  echo "
                    Witaj, ".$_SESSION["imie_i_nazwisko"]."<br>";
                    if(isset($_SESSION["data_ost_log"]))
                      echo "Ostatnie logowanie:<br>".$_SESSION["data_ost_log"];
                    echo "<hr>
                    <form action='panelUzytkownika.php' method='post'><input type='submit' name='submit' value='Twoje konto'></form>
                    <form action='process.php' method='post'><input type='submit' name='submitW' value='Wyloguj się'></form>";
                    $id = $_SESSION["id_u"];
                    $query = "SELECT `prosba` FROM `uzytkownicy` WHERE `id` LIKE '$id'";
                    $result = mysqli_query($link, $query);
                    $wynik = mysqli_fetch_row($result);
                    if($wynik[0] == NULL){
						          echo "<form action='process.php' method='post'><input type='submit' name='submitUK'value='Usuń konto'></form>";
                    }else{
                      echo "<form action='process.php' method='post'><input class='usun-konto' type='submit' name='submitNUK'value='Anuluj usunięcie konta'></form>";
                    }
                ?>
              </div>
            </li>
            <div class="kreska"></div>
            <li><img src="shopping-cart.png" alt="Koszyk">
            <div class="menu-plus menu-k">
                <?php
                  if(isset($_SESSION["admin"]) && $_SESSION["admin"] == true){
                    echo "<div>Nie można korzystać<br>z koszyka,<br>zalogowano jako administrator</div>";
                  }else{
                    echo "Do zapłaty:<br><strong>";
                    if(isset($_COOKIE["idz"])){
                      $id_z = $_COOKIE["idz"];
                      $czas = time()+60*15;
                      setcookie("idz", $id_z, $czas, "/");
                      $czas2 = date("Y-m-d H:i:s", $czas);
                      $query = "UPDATE `zamowienia` SET `data_przedawnienia` ='$czas2' WHERE `id` = '$id_z'";
                      $result = mysqli_query($link, $query);
                      $query = "SELECT SUM(ROUND(zp.`ilosc` * (p.`cena` * ((p.`marza` / 100) + 1)), 2)) 'Suma' FROM zamowienia z JOIN zamowione_produkty zp ON z.`id` = zp.`id_zamowienia` JOIN produkty p ON zp.`id_produktu` = p.`id` JOIN uzytkownicy u ON z.`id_uzytkownika` = u.`id` WHERE u.`id` = '$id' AND z.`id` = '$id_z' AND z.`stan` = 'koszyk' GROUP BY z.`id`";
                      $result = mysqli_query($link, $query);
                      if(mysqli_num_rows($result) > 0){
                        $wynik = mysqli_fetch_row($result);
                        echo $wynik[0]." zł";
                      }else 
                        echo "0 zł";
                    }else 
                    echo "0 zł";
                    echo "</strong>";
                  }
                ?>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <main>
      
      <div class="centered">
        <h2>Koszyk 

      <?php

        if(isset($_COOKIE["idz"])){
          $query = $query = "SELECT SUM(ROUND(zp.`ilosc` * (p.`cena` * ((p.`marza` / 100) + 1)), 2)) 'Suma' FROM zamowienia z JOIN zamowione_produkty zp ON z.`id` = zp.`id_zamowienia` JOIN produkty p ON zp.`id_produktu` = p.`id` JOIN uzytkownicy u ON z.`id_uzytkownika` = u.`id` WHERE u.`id` = '$id' AND z.`id` = '$id_z' AND z.`stan` = 'koszyk' GROUP BY z.`id`";
          $result = mysqli_query($link, $query);
          if(mysqli_num_rows($result) > 0){
            $wynik = mysqli_fetch_row($result);
            echo " - ".$wynik[0]." zł";
          }else 
            echo " - 0 zł";
          echo "</h2>";
          $query = "SELECT z.`id`, p.`nazwa`, zp.`ilosc`, p.`cena`, p.`marza`, ROUND(zp.`ilosc` * (p.`cena` * ((p.`marza` / 100) + 1)), 2) 'Suma', p.`ilosc`, p.`id` FROM zamowienia z JOIN zamowione_produkty zp ON z.`id` = zp.`id_zamowienia` JOIN produkty p ON zp.`id_produktu` = p.`id` JOIN uzytkownicy u ON z.`id_uzytkownika` = u.`id` WHERE u.`id` = '$id' AND z.`id` = '$id_z' AND z.`stan` = 'koszyk'";
          $result = mysqli_query($link, $query);
          while($wynik = mysqli_fetch_row($result)){
            echo "<div class='produkt p2'>
              <img src='img/".str_replace(" ", "", $wynik[1]).".png'>
              <div class='title t2'>".$wynik[1]."</div>
              <div class='price'>".str_replace('.', ',', round((($wynik[3] * ($wynik[4] / 100)) + $wynik[3]), 2))." zł</div>
              <form action='process.php' method='post' class='f2'>
                <select name='ilosc' id='ilosc'>";
                  $wynik[6] = $wynik[6] + $wynik[2];
                  if($wynik[6] > 10)
                    $k = 10;
                  elseif($wynik[2] < $wynik[6])
                    $k = $wynik[6];
                  else
                    $k = $wynik[2];
                  for ($i=1; $i <= $k; $i++){ 
                    echo "<option ";
                    if($i == $wynik[2])
                      echo "selected";
                    echo " value='$i'>$i</option>";
                  }
                echo "</select>
                <input type='text' name='id' value='".$wynik[7]."' style='display: none;'>
                <input type='submit' class='edytuj-koszyk' title='Zmień ilość produktu w koszyku' name='submitEPK' value='Zmień'>
              </form>
              <div class='price suma'> Suma: ".$wynik[5]." zł</div>
              <form action='process.php' method='POST'><input type='text' name='id' value='".$wynik[7]."' style='display: none;'><input class='usun-koszyk' type='submit' title='Usuń produkt z koszyka' name='submitUP' value='Usuń'></form>
            </div>";
          }

          echo "</div>
          <form  class='kup' action='process.php' method='POST'><input type='submit' name='submitKUP' value='Potwierdź zamówienie'></form>";
        }else{
          echo "</h2>Brak produktów w koszyku";
        }
      
        ?>
      </div>
      
    </main>

  </body>
</html>