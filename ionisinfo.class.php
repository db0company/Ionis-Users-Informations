<?php
  //
  // Made by	db0
  // Contact	db0company@gmail.com
  // Website	http://db0.fr/
  //

class			IonisInfo
{
  var $pass_file	= '.ionis_sql_pass';
  var $info_file	= '.ionis_sql_info';
  var $city_file	= '.ionis_sql_city';
  var $pass_dfile	= '/afs/epitech.net/site/etc/ppp.blowfish';
  var $info_dfile	= '/afs/epitech.net/site/etc/passwd';
  var $city_dfile	= '/afs/epitech.net/site/etc/location';
  var $afs;
  var $path_local_files	= '.';
  var $login;
  var $pass;
  var $bdd;
  var $cache;
  var $intra_pass;
  var $intra_is_connected;
  var $intra_fcookie;

  public function	__construct($mysql_login, $mysql_pass, $dbname,
				    $ionis_login = '', $ionis_pass = '',
				    $path_local_files = '.', $afs = false,
				    $ionis_ppp_pass = '')
  {
    if (!(isset($_SESSION['iuicache'])))
      $_SESSION['iuicache'] = array();
    $this->cache = $_SESSION['iuicache'];

    $this->login = $ionis_login;
    $this->pass = $ionis_pass;
    $this->path_local_files = $path_local_files;
    $this->intra_pass = $ionis_ppp_pass;
    $this->intra_is_connected = false;
    try
      {
	$this->bdd = new PDO('mysql:host=localhost;dbname='.$dbname,
			     $mysql_login, $mysql_pass);
      }
    catch (Exception $e)
      {
	echo "MySQL Connection error.\n";
	return ;
      }
    if (!($this->createTable($dbname)))
      return ;

    $this->afs = $afs;
    $this->pass_file = $this->path_local_files.'/'.$this->pass_file;
    $this->info_file = $this->path_local_files.'/'.$this->info_file;
    $this->city_file = $this->path_local_files.'/'.$this->city_file;
    $this->intra_fcookie = $this->path_local_files.'/.cookie_'.$this->login.'.txt';

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
	echo "Login and password not set.\n";
	return (false);
      }
    if (!($connection = @ssh2_connect('ssh.epitech.eu', 22)))
      {
	echo "SSH connection failed.\n";
	return (false);
      }
    if (!(@ssh2_auth_password($connection, $this->login, $this->pass)))
      {
	echo "Authentification failed.\n";
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

  private function	cleanLogin($login)
  {
    return (trim(strtolower($login)));
  }

  public function	getUserByLogin($login)
  {
    $login = $this->cleanLogin($login);
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

  private function	updateRemoteFiles()
  {
    if (!($connection = $this->sshConnect()))
      return (false);
    $error = "Copy failed (file not found or local permission denied).\n";
    if (!(ssh2_scp_recv($connection, $this->pass_dfile, $this->pass_file)))
      {
	echo $error;
	return (false);
      }
    if (!(ssh2_scp_recv($connection, $this->info_dfile, $this->info_file)))
      {
	echo $error;
	return (false);
      }
    if (!(ssh2_scp_recv($connection, $this->city_dfile, $this->city_file)))
      {
	echo $error;
	return (false);
      }
    return (true);
  }

  private function	updateLocalFiles()
  {
    $error = "Copy failed (file not found or local permission denied).\n";
    if (!copy($this->pass_dfile, $this->pass_file))
      {
	echo $error;
	return (false);
      }
    if (!copy($this->info_dfile, $this->info_file))
      {
	echo $error;
	return (false);
      }
    if (!copy($this->city_dfile, $this->city_file))
      {
	echo $error;
	return (false);
      }
    return (true);
  }

  public function	updateFiles()
  {
    if (!($this->afs ? $this->updateLocalFiles() : $this->updateRemoteFiles()))
      return (false);
    return ($this->updateSQL());
  }

  public function	isLogin($login)
  {
    return ($this->getUserByLogin($login) ? true : false);
  }

  public function	checkPass($login, $pass)
  {
    if (empty($login)
	|| empty($pass)
	|| !($user = $this->getUserByLogin($login)))
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

  public function	getSchool($login, $uppercase = true)
  {
    $user = $this->getUserByLogin($login);
    return ($uppercase ? ucwords($user['school']) : $user['school']);
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
    $uid = $this->cleanLogin($uid);
    $req = $this->bdd->prepare('SELECT * FROM ionisusersinformations WHERE uid=?');
    $req->execute(array($uid));
    $user = $req->fetch();
    return ($user['login']);
  }

  public function	getLoginFromId($id)
  {
    $id = $this->cleanLogin($id);
    $req = $this->bdd->prepare('SELECT * FROM ionisusersinformations WHERE id=?');
    $req->execute(array($id));
    $user = $req->fetch();
    return ($user['login']);
  }

  public function	getReportUrl($login)
  {
    $login = $this->cleanLogin($login);
    return ('http://www.epitech.eu/intra/index.php?section=etudiant&page=rapport&login='.$login);
  }

  public function	getPhotoUrl($login)
  {
    $login = $this->cleanLogin($login);
    $default = 'http://www.epitech.eu/intra/photos/no.jpg';
    $school = strtolower($this->getSchool($login));
    if (empty($school))
      return ($default);
    if ($school == 'epita')
      $photo = 'https://www.acu.epita.fr/intra/photo/'.$login;
    else
      $photo = 'http://www.epitech.eu/intra/photos/'.$login.'.jpg';
    if (@fopen($photo, 'r'))
      return ($photo);
    return ($default);
  }

  public function	copyPhoto($login, $directory = '.')
  {
    $login = $this->cleanLogin($login);
    if ($directory[0] != '/')
      $path = $this->path_local_files.'/';
    $path .= $directory.'/'.$login.'.jpg';
    if (file_exists($path))
      return ($path);
    if (!(@copy($this->getPhotoUrl($login), $path)))
      return ('');
    return ($path);
  }

  public function	getPlan($login, $directory = '.')
  {
    $login = $this->cleanLogin($login);
    if ($directory[0] != '/')
      $path = $this->path_local_files.'/';
    $path .= $directory.'/'.$login;
    if (!file_exists($path))
      {
	if (!$this->afs)
	  {
	    if (!($connection = @$this->sshConnect())
		|| (!(@ssh2_scp_recv($connection, '/u/all/'.$login.'/public/.plan', $path))))
	      return ('');
	  }
	elseif (!@copy('/afs/epitech.net/users/all/'.$login.'/public/.plan', $path))
	  return ('');
      }
    $filehand = @file($path);
    $total = count($filehand);
    $plan = '';
    for($i = 0; $i < $total; $i++)
      $plan .= $filehand[$i];
    return ($plan);
  }

  public function	getPhone($login, $directory = '.')
  {
    $plan = $this->getPlan($login, $directory);
    $matches = array();
    preg_match("/((\d){2}[\s.-]*){4,6}/", $plan, $matches);
    return ($matches[0]);
  }

  public function	search($searchString, $maxResults = 0)
  {
    $login = $this->cleanLogin($login);
    $term = '%'.$searchString.'%';
    $req = $this->bdd->prepare('
       SELECT login FROM ionisusersinformations
       WHERE login LIKE ? OR name LIKE ? ORDER BY login'.
			       (!$maxResults ?
				''
				: ('LIMIT '.intval($maxResults))));
    $req->execute(array($term, $term));
    return $req->fetchAll(PDO::FETCH_COLUMN, 0);
  }

  public function	getLogins($school = 0, $promo = 0, $city = 0)
  {
    global $bdd;
    $req = $bdd->prepare('
       SELECT login FROM ionisusersinformations
       WHERE school LIKE ? AND promo LIKE ? AND city LIKE ?');
    $req->execute(array((empty($school) ? "%" : $school),
			(empty($promo) ? "%" : $promo),
			(empty($city) ? "%" : $city)));
    return $req->fetchAll(PDO::FETCH_COLUMN, 0);
  }

  /*
   **********************************
   ** Intra functions
   ************************************
   */
  
  public function	intra_login()
  {
    if (function_exists('curl_init') === false)
      {
	echo "Error : this functionality requires php_curl extension\n";
	return false;
      }
    $this->intra_time = 0;
    if (($this->intra_connect = curl_init()) === false)
      {
	echo "Error : could not init curl connection";
	return false;
      }
    if (empty($this->login) || empty($this->intra_pass))
      {
	echo "Error : login and password must be set for any transaction with intra";
	return false;
      }
    $postvars  = 'action=login';
    $postvars .= '&path=index.php';
    $postvars .= '&login='.$this->login;
    $postvars .= '&passwd='.$this->intra_pass;
    $postvars .= '&qs=/intra/index.php';
    curl_setopt($this->intra_connect, CURLOPT_COOKIEFILE, $this->intra_fcookie);
    curl_setopt($this->intra_connect, CURLOPT_COOKIEJAR, $this->intra_fcookie);  
    curl_setopt($this->intra_connect, CURLOPT_COOKIESESSION, true);
    curl_setopt($this->intra_connect, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($this->intra_connect, CURLOPT_HEADER, false);
    curl_setopt($this->intra_connect, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($this->intra_connect, CURLOPT_URL, INTRA_URL_MAIN);
    curl_setopt($this->intra_connect, CURLOPT_USERAGENT, INTRA_USERAGENT);
    curl_setopt($this->intra_connect, CURLOPT_POSTFIELDS, $postvars);
    curl_setopt($this->intra_connect, CURLOPT_POST, true);
    curl_setopt($this->intra_connect, CURLOPT_SSL_VERIFYPEER, false);
    if ($this->do_transaction($this->intra_connect) === false)
      return false;
    $this->intra_is_connected = true;
    return true;
  }
  
  private function	do_transaction($handler)
  {
    if ($this->intra_time !== 0)
      while ((microtime(true) - $this->intra_time) < INTRA_BETWEEN_TRANSAC);
    $this->intra_time = microtime(true);
    if (($ret = curl_exec($handler)) === false)
      {
	echo "Error : HTTP transaction could not be done\n";
	return false;
      }
    return $ret;
  }
  
  public function fetch_notes($login, $scolaryear)
  {
    $login = $this->cleanLogin($login);
    if ($this->intra_is_connected === false)
      {
	echo "Error : fetching notes requires connection to intra.\n";
	return false;
      }
    $url = INTRA_URL_MAIN."?section=etudiant&page=rapport&login=$login&open_div=1&scolaryear_notes=$scolaryear";
    curl_setopt($this->intra_connect, CURLOPT_URL, $url);
    curl_setopt($this->intra_connect, CURLOPT_POST, false);
    if (($ret = $this->do_transaction($this->intra_connect)) === false)
      return false;
    $html = new DOMDocument();
    @$html->loadHTML($ret);
    $xpath = new DOMXPath($html);
    $divs = $xpath->query('//div');
    foreach ($divs as $div)
      {
	if ($div->getAttribute("id") == "div1")
	  {
	    $elements = $div->getElementsByTagName("tr");
	    foreach ($elements as $element)
	      {
		if ($element->firstChild->getAttribute("class") == "default")
		  continue;
		$childs = $element->childNodes;
		$note = Array();
		foreach ($childs as $child)
		  {
		    if ($child->nodeName == "td")
		      $note[] = trim($child->nodeValue);
		  }
		$notes[] = $note;
	      }
	  }
      }
    return $notes;
  }
  
  public function fetch_modules($login, $scolaryear)
  {
    $login = $this->cleanLogin($login);
    $url = INTRA_URL_MAIN."?section=etudiant&page=rapport&login=$login&open_div=9&scolaryear=$scolaryear";
    curl_setopt($this->intra_connect, CURLOPT_URL, $url);
    curl_setopt($this->intra_connect, CURLOPT_POST, false);
    if (($ret = $this->do_transaction($this->intra_connect)) === false)
      return false;
    $html = new DOMDocument();
    @$html->loadHTML($ret);
    $xpath = new DOMXPath($html);
    $divs = $xpath->query('//div');
    foreach ($divs as $div)
      {
	if ($div->getAttribute("id") == "div9")
	  {
	    $elements = $div->getElementsByTagName("tr");
	    foreach ($elements as $element)
	      {
		if ($element->firstChild->getAttribute("class") == "default")
		  continue;
		$childs = $element->childNodes;
		$module = Array();
		foreach ($childs as $child)
		  {
		    if ($child->nodeName == "td")
		      $module[] = trim($child->nodeValue);
		  }
		$modules[] = $module;
	      }
	  }
      }
    return $modules;
  }
  
  public function calc_gpa($modules)
  {
    $nb_credits = 0;
    $score = 0;
    foreach ($modules as $module)
      {
	$grade = explode("/", $module[5]);
	$grade = trim($grade[1]);
	if ($grade != "-" && $grade != "Acquis")
	  {
	    $nb_credits += $module[3];
	    $score += $module[3] * ($grade == 'A' ? 4 : ($grade == 'B' ? 3 : ($grade == 'C' ? 2 : ($grade == 'D' ? 1 : 0))));	    
	  }
      }
    return $score / $nb_credits;
  }
	
  public function fetch_users($promo, $ville)
  {
    $url = INTRA_URL_MAIN."?section=all&page=trombi&mfrom=etudiant";
    $postvars = "promo=$promo&ville=$ville&custom_list=&Send=send";
    $users = Array();
    curl_setopt($this->intra_connect, CURLOPT_URL, $url);
    curl_setopt($this->intra_connect, CURLOPT_POSTFIELDS, $postvars);
    if (($ret = $this->do_transaction($this->intra_connect)) === false)
      return false;
    $html = new DOMDocument();
    $html->loadHTML($ret);
    $xpath = new DOMXPath($html);
    $tables = $xpath->query('//table');
    foreach ($tables as $table)
      {
	if ($table->getAttribute("align") == "middle" && $table->getAttribute("width") == "90%")
	  {
	    $elems = $table->getElementsByTagName("a");
	    foreach ($elems as $elem)
	      {
		$user = explode(" ", $elem->getAttribute("title"));
		$user[2] = substr(trim($elem->textContent), 0, strpos(trim($elem->textContent), " "));
		$users[] = $user;
	      }
	  }
      }
    return $users;
  }
}
