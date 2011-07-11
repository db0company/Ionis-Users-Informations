<?php
  //
  // Made by	db0
  // Contact	db0company@gmail.com
  // Website	http://db0.fr/
  //

class			IonisInfoSQL
{
  var $pass_file	= '.ionis_sql_pass';
  var $info_file	= '.ionis_sql_info';
  var $pass_dfile	= '/usr/site/etc/ppp.blowfish';
  var $info_dfile	= '/usr/site/etc/passwd';

  var $path_plan	= '/u/all/';

  var $login;
  var $pass;
  var $bdd;

  public function	__construct($mysql_login, $mysql_pass, $dbname,
				    $ionis_login = '', $ionis_pass = '')
  {
    $this->login = $ionis_login;
    $this->pass = $ionis_pass;

    try
      {
	$this->bdd = new PDO('mysql:host=localhost;dbname='.$dbname,
			     $mysql_login, $mysql_pass);
      }
    catch (Exception $e)
      {
	echo 'MySQL Connection error.';
	return ;
      }

    if ((!(file_exists($this->pass_file)) ||
	 !(file_exists($this->info_file)))
	&& !($this->updateFiles()))
	return ;
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
    if (!($connection = @ssh2_connect('ssh.epitech.eu', 22)))
    {
      echo 'SSH connection failed.';
      return (false);
    }
    if (!(@ssh2_auth_password($connection, $this->login, $this->pass)))
      {
	echo 'Authentification failed.';
	return (false);
      }
    return ($connection);
  }

  private function	updateSQL()
  {
    if (!($filestream = fopen($this->pass_file, "r")))
      return (false);
    while (!feof($filestream))
      {
    	$tmp = @split(" ", fgets($filestream));
    	$req = $this->bdd->prepare('INSERT INTO ionisusersinformations(login, pass) VALUES(?, ?) ON DUPLICATE KEY UPDATE pass=?');
    	$req->execute(array($tmp[0], chop($tmp[1]), chop($tmp[1])));
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

	$req = $this->bdd->prepare('UPDATE ionisusersinformations SET uid=?, promo=?, school=?, groupe=?, name=? WHERE login=?');
	$req->execute(array(intval($info[2]),
			    intval($info_promo[1]),
			    $info_promo[0],
			    $group,
			    $info[4],
			    $info[0]));
      }
    fclose($filestream);
    return (true);    
  }

  private function	getUserByLogin($login)
  {
    $req = $this->bdd->prepare('SELECT * FROM ionisusersinformations WHERE login=?');
    $req->execute(array($login));
    return ($req->fetch());
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
    $this->updateSQL();
    return (true);
  }

  public function	checkPass($login, $pass)
  {
    $user = $this->getUserByLogin($login);
    return ((strcmp(crypt(stripslashes($pass),
			  $user['password']),
		    $user['password'])) == 0);
  }

  public function	getName($login)
  {
    $user = $this->getUserByLogin($login);
    return ($user['name']);
  }

  public function	getUid($login)
  {
    $user = $this->getUserByLogin($login);
    return ($user['uid']);
  }

  public function	getPromo($login)
  {
    $user = $this->getUserByLogin($login);
    return ($user['promo']);
  }

  public function	getSchool($login)
  {
    $user = $this->getUserByLogin($login);
    return ($user['school']);
  }

  public function	getGroup($login)
  {
    $user = $this->getUserByLogin($login);
    return ($user['groupe']);
  }

  public function	getLogin($uid)
  {
    $req = $this->bdd->prepare('SELECT * FROM users WHERE uid=?');
    $req->execute(array($login));
    $user = $req->fetch();
    return ($user['login']);
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
