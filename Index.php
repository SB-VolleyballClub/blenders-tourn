<?php
session_start();
require_once('./inc/tourn.class.php');
if (isset($_GET['mode']) &&  $_GET['mode'] == 'reset'){
    unset($_SESSION['tourn']);
}
$render = true;
if (isset($_GET['norender'])){
    $render = false;
}
// print "<hr><pre>";
// print_r($_SESSION);
// print "</pre><hr>";

if (isset($_SESSION['tourn'])){
    //print "Unserialize<br>";
    $tourn = unserialize($_SESSION['tourn']);
    $tourn->incrementCounter();
    $newTourn = false;
}
else{
    $tourn = new tourn();
    $newTourn = true;
}

//print ($newTourn) ? "New" : "Session";
$tourn->processGet();

if ($render) print $tourn->render();
$_SESSION['tourn'] = serialize($tourn);
?>
