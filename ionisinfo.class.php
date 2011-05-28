<?php
  //
  // Made by	db0
  // Contact	db0company@gmail.com
  // Website	http://db0.fr/
  //

class			IonisInfo
{
  var $pass_file	= 'pass.txt';
  var $info_file	= 'info.txt';
  var $pass_dfile	= '/usr/site/etc/ppp.blowfish';
  var $info_dfile	= '/usr/site/etc/passwd';
  var $login;
  var $pass;
  var $student;

  public function	__construct($login = '', $pass = '')
  {
    $this->login = $login;
    $this->pass = $pass;
    if (!(file_exists($this->pass_file)) || !(file_exists($this->info_file)))
      $this->updateFiles();
    $this->initStudents();
  }

  public function	__destruct()
  {}

  public function	updateFiles()
  {
    if ($this->login == '' || $this->pass == '')
      die("Login and password not set");
    $connection = ssh2_connect('ssh.epitech.eu', 22)
      or die('SSH connexion failed.');
    ssh2_auth_password($connection, $this->login, $this->pass)
      or die('Authentification failed.');
    ssh2_scp_recv($connection, $this->pass_dfile, $this->pass_file)
      or die('Copy failed (file not found or local permission denied).');
    ssh2_scp_recv($connection, $this->info_dfile, $this->info_file)
      or die('Copy failed (file not found or local permission denied).');
  }

  private function	initStudents()
  {
    $filestream = fopen($this->pass_file, "r")
      or die('Cannot open file.');
    while (!feof($filestream))
      {
    	$tmp = @split(" ", fgets($filestream));
    	$this->student[$tmp[0]]['password'] = chop($tmp[1]);
      }
    fclose($filestream);
    $filestream = fopen($this->info_file, "r")
      or die('Cannot open file.');
    $this->promos = array();
    while (!feof($filestream))
      {
    	$info = @split(":", fgets($filestream));
	$tmp = @split("/", $info[5]);
    	$group = $tmp[2];
	$info_promo = @split("_", $group);

    	$this->student[$info[0]]['uid'] = $info[2];
    	$this->student[$info[0]]['name'] = $info[4];
    	$this->student[$info[0]]['group'] = $group;
	$this->student[$info[0]]['school'] = $info_promo[0];
	$this->student[$info[0]]['promo'] = $info_promo[1];
      }
    fclose($filestream);
  }

  public function	checkPass($login, $pass)
  {
    return ((strcmp(crypt(stripslashes($pass),
			  $this->student[$login]['password']),
		    $this->student[$login]['password'])) == 0);
  }

  public function	getName($login)
  {
    return ($this->student[$login]['name']);
  }

  public function	getUid($login)
  {
    return ($this->student[$login]['uid']);
  }

  public function	getPromo($login)
  {
    return ($this->student[$login]['promo']);
  }

  public function	getSchool($login)
  {
    return ($this->student[$login]['school']);
  }

  public function	getGroup($login)
  {
    return ($this->student[$login]['group']);
  }

  public function	getLogin($uid)
  {
    foreach ($this->student as $key => $value)
      {
	if ($value['uid'] == $uid)
	  return ($key);
      }
    return ('');
  }

}

?>
