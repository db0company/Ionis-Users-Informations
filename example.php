<?php
  //
  // Made by	db0
  // Contact	db0company@gmail.com
  // Website	http://db0.fr/
  //

include('ionisinfo.class.php');
$infotech = new IonisInfo('exampl_e', '2q4xfcc3');

echo '
<html>
  <head>
    <title>Ionis User Information</title>
  </head>
  <body>
';

if (isset($_POST['login']) && isset($_POST['pass'])
    && $infotech->checkPass($_POST['login'], $_POST['pass']))
  {
    $login = $_POST['login'];
    echo '
    <ul>
      <li>Login : '.$login.'</li>
      <li>Uid : '.$infotech->getUid($login).'</li>
      <li>Name : '.ucwords($infotech->getName($login)).'</li>
      <li>Group : '.$infotech->getGroup($login).'</li>
      <li>School : '.ucwords($infotech->getSchool($login)).'</li>
      <li>Promo : '.$infotech->getPromo($login).'</li>
    </ul>';
  }
 else
   {
     echo '
    <form method="post">
      <label for="login">Login : </label>
      <input type="text" name="login" value="" /><br />
      <label for="pass">Pass PPP : </label>
      <input type="password" name="pass" value="" /><br />
      <input type="submit" value="OK" /><br />
    </form>
';
   }
     echo '
  </body>
</html>
';

?>
