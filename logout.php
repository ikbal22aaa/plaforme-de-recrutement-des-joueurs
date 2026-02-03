<?php
session_start();

// Détruire toutes les données de session
session_destroy();

// Rediriger vers la page d'accueil
header('Location: index.php');
exit();
?>

