# PoGoPostcards
Some PHP-Scripts to store your Pokemon Go Postcards in a *AMP-System and show their position and infos on a map

# What you need
A XAMP-System (developed on PHP8.3 and MySQL 8.x) with a database where you have access

# What is provided

An index.php that retrieves the saved Postcards and show their location on map. When you click on the marker you get the info of the Pokestop in a popup

A register.php: here you can add your Postcards, you have fields for Name, Description, the friend who send you the gift, the location and up to 4 images.

An export.php: here you can export your database as a KML-File to import them in another mapping-tool.

# What you have to do
All php-Scripts read username and password from a file called .ht_cred.php that you have to provide in the same directory.
Format is php-Syntax:
<?php
$username="user";
$password="pass";
?>
Enter here the credentials of the mysql-database-user that has the rights to access the table.

# What is todo?
Of course a lot, this a first working draft. For example, export a list of Postcards sorted by distance, name a.s.o.
The responsive design is not perfectly responsive yet.
The performance may be an issue when you have lots of postcards

