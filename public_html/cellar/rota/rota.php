<?php
include_once ('../include/DB.php');
$DB=new DB;
/*
shifts:
+-----------+--------------+------+-----+---------+----------------+
| Field     | Type         | Null | Key | Default | Extra          |
+-----------+--------------+------+-----+---------+----------------+
| shiftID   | int(8)       | NO   | PRI | NULL    | auto_increment |
| date      | datetime     | YES  |     | NULL    |                |
| startTime | time         | YES  |     | NULL    |                |
| endTime   | time         | YES  |     | NULL    |                |
| staffID   | int(4)       | YES  |     | NULL    |                |
| notes     | varchar(128) | YES  |     | NULL    |                |
+-----------+--------------+------+-----+---------+----------------+

staff:
+----------+-------------+------+-----+---------+----------------+
| Field    | Type        | Null | Key | Default | Extra          |
+----------+-------------+------+-----+---------+----------------+
| staffID  | int(4)      | NO   | PRI | NULL    | auto_increment |
| forename | varchar(64) | YES  |     | NULL    |                |
| surname  | varchar(64) | YES  |     | NULL    |                |
+----------+-------------+------+-----+---------+----------------+

NEED TO ADD IN NO-GO SHIFTS TO STAFF TABLE
backups?

add in change logs.  And 'deleted' column in shifts

move shifts = change staffID attached to shift ID
create new rota (inc check to see if rota for that weekCommencingDate already exists ("SELECT * FROM shifts WHERE date=".weekCommencingDate.")

save button - post location of all shiftIDs - check if shift IDs exist and create if not, else, update all shiftIDs to correct staffID

*/

?>