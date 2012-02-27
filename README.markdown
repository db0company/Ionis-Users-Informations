
Ionis Users Informations PHP Class
==================================

A PHP Class to get informations about students in Ionis group :
Isbp, Epitech, Etna, Ipsa, Epita, e-art, Web@cademie, ...

Requirement
===========

* PHP >= 5.3
* libssh2-php
* MySQL (mysql-server, php5-mysql)

Usage
=====

Initialisation
--------------

     IonisInfo::__construct(string $mysql_login, string $mysql_pass,
     			    string $database_name
			    [, string $iui_login, string $iui_unix_pass,
			       string $path_local_files,
			       bool $afs]);

where $mysql_login is the **username** used by MySQL to connect
and   $mysql_pass is the **password** corresponding to the username
and   $database_name is the **name of the MySQL database** (must exist, will not be created)
and   $iui_login is your **Ionis login**
and   $iui_pass is your **Ionis unix password** (the one that open your unix sessions)
and   $path_local_files is the **path** where files will be copied (default value = '.').
and   $afs is true if the AFS is mounted on your server, false otherwise.

If $afs is false and $iui_login and $iui_pass are not specified, it will work only if
the database has already been filled and the following functions will
fail, so you must not use them :

* IonisInfo::getPlan
* IonisInfo::updateFiles

Functions
---------

### Do some checking...

###### isLogin - Check if a login exist

     bool	IonisInfo::isLogin(string $login);

where $login is the **login** you are checking.
and   return value is true if the login exists, false otherwise.

###### getLoginFromUid - Get a login from the uid

     string	IonisInfo::getLoginFromUid(int $uid);

where $uid is the **uid** you are looking for
and   return value is the login corresponding to the uid.

### Get informations!

###### Get uid, name, group, school, promo, city from the login

     int	IonisInfo::getUid(string $login);
     string	IonisInfo::getName(string $login[, bool $uppercase]);
     string	IonisInfo::getGroup(string $login);
     string	IonisInfo::getSchool(string $login[, bool $uppercase]);
     int	IonisInfo::getPromo(string $login);
     string	IonisInfo::getCity(string $login);

where $login is the **login** you are looking for
and   $uppercase is true if you want result to start with a capital
      letter (default value), false otherwise
and   return value is the result of your request
      or 0 or an empty string on failure.

###### getUserByLogin - Get all informations from the login in an array

     Array	IonisInfo::getUserByLogin(string $login);

where $login is the **login** you are looking for
and   return value is an **array** containing informations below.

###### getReportUrl - Get Report Url

     string	IonisInfo::getReportUrl(string $login);

where $login is the **login** you are looking for
and   return value is the **url** of the epitech intranet page about this login,
      it never fail, even if the login is invalid.

It is only guaranteed to work with Epitech students.

###### Get photos

     string	IonisInfo::getPhotoUrl(string $login);

where $login is the **login** you are looking for
and   return value is the **url** of the student photo
      or an empty string on failure.

It work with Epitech and Epita students. It is not currently guaranteed to
work with other schools.

     string IonisInfo::copyPhoto(string $login, string $directory);

where $login is the **login** you are looking for
and   $directory is the **path** where you want the photo to be copied
      (it can be an absolute path or a relative path that will be
      concatenate with the path given in the constructor)
and   return value is the **photo path** or an empty string on failure.

###### getPlan - Get .plan file on the afs

     string	IonisInfo::getPlan(string $login[, string $directory]);

where $login is the **login** you are looking for
and   $directory is the path where you want the .plan file to be copied
      (it can be an absolute path or a relative path that will be
      concatenate with the path given in the constructor)
and   return value is the content of the **.plan file** on the AFS
      or an empty string on failure.

This function copy the .plan public file (afs) locally.

###### checkPass - Check the PPP password
     bool	IonisInfo::checkPass(string $login, string $pass);

where $login is the **login** of the user trying to authenticate
and   $pass is the **PPP password** corresponding to the user
and   return value is true if the password match for the login,
      false otherwise.

###### Update

     bool	IonisInfo::updateSQL(void);

where the return value is true on success, false otherwise.

This function update the MySQL database using local files.
It is **NOT** recommanded to use this function.
Use the following function otherwise.

     bool	IonisInfo::updateFiles(void);

where the return value is true on success, false otherwise.

This function **update** the informations files and the MySQL database.

**Good idea to put it in a crontab!**
See the update.php example file.

Example of crontab line :
     @daily php5 /path/to/file/update.php

### Get MySQL table Id

###### Get login from the id

      string IonisInfo::getLoginFromId(int $id)

where $id is the **id key** in the mySQL database
and   return value is the **login** corresponding to the id.

###### Get the id from the login :

      int	IonisInfo::getId(string $login);

where $login is the **login** you are looking for
and   return value is the **id** corresponding to the login.

### Search

      Array	IonisInfo::search(string $searchString[, int $maxResults]);

where $searchString is all or part of a login or name
and   $maxResults is the limit of result you want
and   return value is an **array of string** containing the logins matching
      the query in their names or login.

Example
-------

   See the **example.php** file for an example of a web page using this class.

Copyright/License
=================

               DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
                       Version 2, December 2004
    
    Copyright (C) 2012 Barbara Lepage <db0company@gmail.com>
    
    Everyone is permitted to copy and distribute verbatim or modified
    copies of this license document, and changing it is allowed as long
    as the name is changed.
    
               DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
      TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION
    
     0. You just DO WHAT THE FUCK YOU WANT TO.
    

Author
======

Made by		db0
Contact		db0company@gmail.com
Website		http://db0.fr/


Versions
========

 /!\ Latest version is on GitHub :
     https://github.com/db0company/Ionis-Users-Informations

