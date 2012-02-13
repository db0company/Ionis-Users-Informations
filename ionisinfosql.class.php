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
  var $city_file	= '.ionis_sql_city';
  var $pass_dfile	= '/usr/site/etc/ppp.blowfish';
  var $info_dfile	= '/usr/site/etc/passwd';
  var $city_dfile	= '/afs/epitech.net/site/etc/location';

  var $login;
  var $pass;
  var $bdd;
  var $cache;

  public function	__construct($mysql_login, $mysql_pass, $dbname,
				    $ionis_login = '', $ionis_pass = '',
				    $path_local_files = '.')
  {
    if (!(isset($_SESSION['iuicache'])))
      $_SESSION['iuicache'] = array();
    $this->cache = $_SESSION['iuicache'];

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
    if (!($this->createTable($dbname)))
      return ;

    $this->pass_file = $path_local_files.'/'.$this->pass_file;
    $this->info_file = $path_local_files.'/'.$this->info_file;
    $this->city_file = $path_local_files.'/'.$this->city_file;

    if ((!(file_exists($this->pass_file)) ||
	 !(file_exists($this->info_file)) ||
	 !(file_exists($this->city_file)))
	&& !($this->updateFiles()))
	return ;
  }

  public function	__destruct()
  {
    $_SESSION['iuicache'] = $this->cache;
  }

  private function	createTable($dbname)
  {
    $req = $this->bdd->prepare('SELECT uid FROM ionisusersinformations');
    if (!$req->execute())
      {
	$req = $this->bdd->prepare('CREATE TABLE `'.$dbname.'`.`ionisusersinformations`
  (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `login` VARCHAR( 128 ) NOT NULL ,
    `uid` INT NOT NULL ,
    `promo` INT NOT NULL ,
    `pass` VARCHAR( 255 ) NOT NULL ,
    `school` VARCHAR( 255 ) NOT NULL ,
    `groupe` VARCHAR( 255 ) NOT NULL ,
    `name` VARCHAR( 255 ) NOT NULL ,
    `city` VARCHAR( 128 ) NOT NULL ,
    UNIQUE
      (
        `login`
      )
  );
');
	if (!($req->execute(array())))
	  {
	    $err = $req->errorInfo();
	    echo $err[2];
	    return (false);
	  }
      }
    return (true);
  }

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

  public function	updateSQL()
  {
    if (!($filestream = fopen($this->pass_file, "r")))
      return (false);
    $i = 0;
    while (!feof($filestream))
      {
    	$tmp = @split(" ", fgets($filestream));
    	$req = $this->bdd->prepare('INSERT INTO ionisusersinformations(login, pass) VALUES(?, ?) ON DUPLICATE KEY UPDATE pass=?');
    	$req->execute(array($tmp[0], chop($tmp[1]), chop($tmp[1])));
	++$i;
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
    $citys['prs'] = 'Paris';
    $citys['lyo'] = 'Lyon';
    $citys['lyn'] = 'Lyon';
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
	$req = $this->bdd->prepare('UPDATE ionisusersinformations SET city=? WHERE login=?');
	$req->execute(array($citys[@strtolower(@trim($city[1]))], $city[0]));
      }
    fclose($filestream);
    return ($i);    
  }

  public function	getUserByLogin($login)
  {
    if (!isset($this->cache[$login]))
      {
	$req = $this->bdd->prepare('SELECT * FROM ionisusersinformations WHERE login=?');
	if (!($req->execute(array($login))) ||
	    !($user = $req->fetch()))
	  return (false);
	$this->cache[$login] = $user;
      }
    return ($this->cache[$login]);
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
    if (!(ssh2_scp_recv($connection, $this->city_dfile, $this->city_file)))
      {
	echo 'Copy failed (file not found or local permission denied).';
	return (false);
      }
    return ($this->updateSQL());
  }

  public function	isLogin($login)
  {
    return ($this->getUserByLogin($login) ? true : false);
  }

  public function	checkPass($login, $pass)
  {
    if (!($user = $this->getUserByLogin($login)))
      return (false);
    return ((strcmp(crypt(stripslashes($pass),
			  $user['pass']),
		    $user['pass'])) == 0);
  }

  public function	getName($login, $uppercase = true)
  {
    $user = $this->getUserByLogin($login);
    return ($uppercase ? ucwords($user['name']) : $user['name']);
  }

  public function	getUid($login)
  {
    $user = $this->getUserByLogin($login);
    return ($user['uid']);
  }

  public function	getId($login)
  {
    $user = $this->getUserByLogin($login);
    return ($user['id']);
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

  public function	getCity($login)
  {
    $user = $this->getUserByLogin($login);
    return ($user['city']);
  }

  public function	getLoginFromUid($uid)
  {
    $req = $this->bdd->prepare('SELECT * FROM ionisusersinformations WHERE uid=?');
    $req->execute(array($uid));
    $user = $req->fetch();
    return ($user['login']);
  }

  public function	getLoginFromId($id)
  {
    $req = $this->bdd->prepare('SELECT * FROM ionisusersinformations WHERE id=?');
    $req->execute(array($id));
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
