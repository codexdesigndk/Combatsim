<?php
/**
 * Created by PhpStorm.
 * User: Jesper
 * Date: 28/10/2014
 * Time: 18:19
 */

$host = "localhost";
$user = "root";
$password = "";
$database = "combatsim";


$connection = mysqli_connect($host, $user, $password, $database) or die("Ingen forbindelse til databasen");

$charset = mysqli_set_charset($connection, "utf8");
