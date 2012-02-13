<?php
  //
  // Made by	db0
  // Contact	db0company@gmail.com
  // Website	http://db0.fr/
  //

include('ionisinfosql.class.php');
$infotech = new IonisInfoSQL('toto', 'password', 'my_database', 'exampl_e', '2q4xfcc3');

echo '
<html>
  <head>
    <title>Ionis User Information with MySQL</title>
  </head>
  <body>
';

if (isset($_POST['login']) && isset($_POST['pass'])
    && $infotech->checkPass($_POST['login'], $_POST['pass']))
  {
    if (empty($_POST['infos']))
      $login = $_POST['login'];
    else
      $login = $_POST['infos'];
    if (!$infotech->isLogin($login))
      echo 'Unknown login.';
    else
      {
	echo '
    <ul>
      <li>Login : '.$login.'</li>
      <li>Uid : '.$infotech->getUid($login).'</li>
      <li>Name : '.ucwords($infotech->getName($login)).'</li>
      <li>Group : '.$infotech->getGroup($login).'</li>
      <li>School : '.ucwords($infotech->getSchool($login)).'</li>
      <li>Promo : '.$infotech->getPromo($login).'</li>
      <li>City : '.$infotech->getCity($login).'</li>
      <li><a href="'.$infotech->getReportUrl($login).
      '">Intranet report</a></li>';
	if (($photo = $infotech->copyPhoto($login, 'photos')) != ''
	    || (($photo = $infotech->getPhotoUrl($login))) != '')
	  echo '<li>Photo : <img src="'.$photo.'" /></li>';
	if (($plan = $infotech->getPlan($login, 'plan')) != '')
	  echo '<li>.Plan : <pre>'.$plan.'</pre></li>';
	echo '    </ul>';
      }
  }
 else
   {
     echo '
    <form method="post">
      <label for="login">Login : </label>
      <input type="text" name="login" value="" /><br />
      <label for="pass">Pass PPP : </label>
      <input type="password" name="pass" value="" /><br />
      <label for="pass">Informations about : </label>
      <input type="text" name="infos" value="" /><br />
      <input type="submit" value="OK" /><br />
    </form>
';
   }
     echo '
  </body>
</html>
';

?>

