<?php

include_once("includes/connect.php");

/*********Generate Hero*********/
include_once("hero/hero.php");
$testHero = new Hero();

$testHero->GenerateHero($_REQUEST["level"]); //generate lvl1 Hero

//save adventurer
$testHero->SaveHero();

/***********end generate Hero *********/


//header("Location: index.php");

?>

<a href="index.php">Return</a>