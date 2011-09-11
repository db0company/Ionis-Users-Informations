<?php
  //
  // Made by	db0
  // Contact	db0company@gmail.com
  // Website	http://db0.fr/
  //

class			IonisInfo
{
  var $pass_file	= '.ionis_pass';
  var $info_file	= '.ionis_info';
  var $city_file	= '.ionis_city';
  var $pass_dfile	= '/usr/site/etc/ppp.blowfish';
  var $info_dfile	= '/usr/site/etc/passwd';
  var $city_dfile	= '/afs/epitech.net/site/etc/location';

  var $login;
  var $pass;
  var $student;

  public function	__construct($login = '', $pass = '')
  {
    $this->login = $login;
    $this->pass = $pass;
    if ((!(file_exists($this->pass_file)) || !(file_exists($this->info_file)))
	&& !($this->updateFiles()))
	return ;
    $this->initStudents();
  }

  public function	__destruct()
  {}

  private function	sshConnect()
  {
    if ($this->login == '' || $this->pass == '')
      {
	echo "Login and password not set";
	return (false);
      }
    if (!($connection = ssh2_connect('ssh.epitech.eu', 22)))
    {
      echo 'SSH connection failed.';
      return (false);
    }
    if (!(ssh2_auth_password($connection, $this->login, $this->pass)))
      {
	echo 'Authentification failed.';
	return (false);
      }
    return ($connection);
  }

  public function	updateFiles()
  {
    if (!($connection = $this->sshConnect()))
      return (false);
    if (!(ssh2_scp_recv($connection, $this->pass_dfile, $this->pass_file)))
      {
	echo 'Copy failed (file not found or local permission denied).';
	return (false);
      }
    if (!(ssh2_scp_recv($connection, $this->info_dfile, $this->info_file)))
      {
	echo 'Copy failed (file not found or local permission denied).';
	return (false);
      }
    return (true);
  }

  private function	initStudents()
  {
    if (!($filestream = fopen($this->pass_file, "r")))
      return (false);
    while (!feof($filestream))
      {
    	$tmp = @split(" ", fgets($filestream));
    	$this->student[$tmp[0]]['password'] = chop($tmp[1]);
      }
    fclose($filestream);
    if (!($filestream = fopen($this->info_file, "r")))
      return (false);
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
    $citys['prs'] = 'Paris';
    $citys['lyo'] = 'Lyon';
    $citys['paris'] = 'Paris';
    $citys['ncy'] = 'Nancy';
    $citys['mpl'] = 'Montpellier';
    $citys['tls'] = 'Toulouse';
    $citys['lil'] = 'Lille';
    $citys['stg'] = 'Strasbourg';
    $citys['nts'] = 'Nantes';
    $citys['msl'] = 'Marseille';
    $citys['nce'] = 'Nice';
    $citys['bdx'] = 'Bordeaux';
    $citys['rns'] = 'Rennes';
    if (!($filestream = fopen($this->city_file, "r")))
      return (false);
    $this->promos = array();
    while (!feof($filestream))
      {
    	$city = @split(":", fgets($filestream));
	$this->student[$city[0]]['city'] =
	  $citys[strtolower(trim($city[1]))];
      }
    fclose($filestream);
    return (true);
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

  public function	getCity($login)
  {
    return ($this->student[$login]['city']);
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

  public function	getReportUrl($login)
  {
    return ('http://www.epitech.eu/intra/index.php?section=etudiant&page=rapport&login='.$login);
  }

  public function	getPhotoUrl($login)
  {
    if (@fopen('http://www.epitech.eu/intra/photos/'.$login.'.jpg', 'r'))
      return ('http://www.epitech.eu/intra/photos/'.$login.'.jpg');
    return ('');
  }

  public function	copyPhoto($login, $directory = '.')
  {
    if (file_exists($directory.'/'.$login.'.jpg'))
    return ($directory.'/'.$login.'.jpg');
    if (!(@copy('http://www.epitech.eu/intra/photos/'.$login.'.jpg', $directory.'/'.$login.'.jpg')))
      return ('');
    return ($directory.'/'.$login.'.jpg');
  }

  public function	getPlan($login, $directory = '.')
  {
    if (!file_exists($directory.'/'.$login))
      {
	if (!($connection = $this->sshConnect()))
	  return ('');
	if (!(@ssh2_scp_recv($connection, '/u/all/'.$login.'/public/.plan', $directory.'/'.$login)))
	  return ('');
      }
    $filehand = @file($directory.'/'.$login);
    $total = count($filehand);
    $plan = '';
    for($i = 0; $i < $total; $i++)
      $plan .= $filehand[$i];
    return ($plan);
  }


}

?>
