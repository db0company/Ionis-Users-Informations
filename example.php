<?php
  //
  // Made by	db0
  // Contact	db0company@gmail.com
  // Website	http://db0.fr/
  //

include_once('conf.php');
include_once($class_absolute_path.'/ionisinfo.class.php');

$iui = new IonisInfo($mysql_login, $mysql_pass, $mysql_dbname,
		     $ionis_login, $ionis_unix_password, $absolute_path_local_files);

echo '
<html>
  <head>
    <title>Ionis Users Informations</title>
  </head>
  <body>
';

if (isset($_POST['login']) && isset($_POST['pass'])
    && $iui->checkPass($_POST['login'], $_POST['pass']))
  {
    if (empty($_POST['infos']))
      $login = $_POST['login'];
    else
      $login = $_POST['infos'];
    if (!$iui->isLogin($login))
      echo 'Unknown login.';
    else
      {
	echo '
    <ul>
      <li>Login : '.$login.'</li>
      <li>Uid : '.$iui->getUid($login).'</li>
      <li>Name : '.ucwords($iui->getName($login)).'</li>
      <li>Group : '.$iui->getGroup($login).'</li>
      <li>School : '.ucwords($iui->getSchool($login)).'</li>
      <li>Promo : '.$iui->getPromo($login).'</li>
      <li>City : '.$iui->getCity($login).'</li>
      <li><a href="'.$iui->getReportUrl($login).
      '">Intranet report</a></li>';
	if (($photo = $iui->copyPhoto($login, 'photos')) != ''
	    || (($photo = $iui->getPhotoUrl($login))) != '')
	  echo '<li>Photo : <img src="'.$photo.'" /></li>';
	if (($plan = $iui->getPlan($login, 'plan')) != '')
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

