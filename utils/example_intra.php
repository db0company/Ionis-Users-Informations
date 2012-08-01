<?php
  //
  // Made by	db0
  // Contact	db0company@gmail.com
  // Website	http://db0.fr/
  //
  
include_once('conf.php');
include_once($class_absolute_path.'/ionisinfo.class.php');

$iui = new IonisInfo($mysql_login, $mysql_pass, $mysql_dbname,
		     $ionis_login, $ionis_unix_password, $absolute_path_local_files,
		     $afs, $intra_password);
		 
if ($iui->intra_login() === false)
    return;
$notes = $iui->fetch_notes($ionis_login, '11');
$modules = $iui->fetch_modules($ionis_login, '2010-2011');
$gpa = $iui->calc_gpa($modules);

echo "$ionis_login's GPA : ".number_format($gpa, 3);

$users = $iui->fetch_users('epitech_2015', 'paris');
var_dump($users);

?>

