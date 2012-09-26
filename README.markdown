
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

     string	IonisInfo::getPhotoUrl(string $login[, bool $https]);

where $login is the **login** you are looking for
and   $https is false (default) for http, true for https
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

###### getPhone - Get the phone number using the .plan file

     string	IonisInfo::getPhone(string $login[, string $directory]);

where $login is the **login** you are looking for
and   $directory is the path where you want the .plan file to be copied
      (it can be an absolute path or a relative path that will be
      concatenate with the path given in the constructor)
and   return value is the phone number in the .plan file
      or an empty string on failure.

This function copy the .plan public file (afs) locally.

###### Get global informations

     Array	IonisInfo::getSchools([bool $from_database]);

where $from_database is false (default) if you want to be sure that these
      schools exists and are real, true if you want to get them from the
      database (generated automatically so can be a fake school like "tmp",
      "old", "guest", "prof-adm"...)
and   return value is an array containing schools.

     Array	IonisInfo::getCities([string $school]);

where $school is an optionnal parameter to select only cities where the
      given school is
and   return value is an array containing cities.

     Array	IonisInfo::getPromos([string $school, bool $from_database]);

This function return an array of **current promos** for the given school (epitech
by default). If the $from_database optionnal parameter is true (false by default),
you will get all promos available in the database (even if students in this promo
have finished school).

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

###### Search by login/name

      Array	IonisInfo::search(string $searchString[, int $maxResults]);

where $searchString is all or part of a login or name
and   $maxResults is the limit of result you want
and   return value is an **array of string** containing the logins matching
      the query in their names or login.

###### Search logins by school/promo/city

      Array	IonisInfo::getLogins([string $school, int $promo, string $city]);

where $school is the requested school or 0 for all schools
and   $promo is the requested or 0 for all promos
and   $city is the requested city or 0 for all cities
and   return value is an **array of string** containing the logins.

By default, the three values are 0.

### Get informations from intranet

###### login to the intranet

	bool	IonisInfo::intra_login();

This function tries to connect to the intranet using the login and the password
provided in the configuration file. It returns true if the connection succeed
or false if it fails.

###### fetch marks

       Array	IonisInfo::fetch_notes(string $login, string $scolaryear);

where $login is the login we want to obtain the marks
and   $scolaryear is the year which we want the marks
and   return value is an **array of string** containing the marks of the login

###### fetch modules

       Array	IonisInfo::fetch_modules(string $login, string $scolaryear);

where $login is the login we want to obtain the modules
and   $scolaryear is the year which we want the modules
and   return value is an **array of array** containing multiples informations
      on modules such as grade or number of credits

###### fetch students

       Array	IonisInfo::fetch_users(string $promo, string $ville);

where $promo is the promotion you want the students list
and   $ville is the town of a specific promotion
and   return value is an **array of array** in which each case contains
      the first name, the last name, and the login of the student

###### calculate GPA
       
       float	IonisInfo::calc_gpa($modules);

where $modules is an **array** of modules you want to calculate the GPA
and   return value is a float number representing the value of the GPA

Example
-------

   See the **example.php** file for an example of a web page using this class.

Copyright/License
=================

    Copyright 2012 Barbara Lepage
   
    Licensed under the Apache License, Version 2.0 (the "License");
    you may not use this file except in compliance with the License.
    You may obtain a copy of the License at
   
        http://www.apache.org/licenses/LICENSE-2.0
   
    Unless required by applicable law or agreed to in writing, software
    distributed under the License is distributed on an "AS IS" BASIS,
    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
    See the License for the specific language governing permissions and
    limitations under the License.    
   
Author
======

* Made by		db0
* Contact		db0company@gmail.com
* Website		http://db0.fr/


Versions
========

 /!\ Latest version is on GitHub :
* https://github.com/db0company/Ionis-Users-Informations

