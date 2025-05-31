<?php
session_start();

// Détruit toutes les variables de session
$_SESSION = [];



// Enfin on détruit la session
session_destroy();

// On redirige vers la page d’accueil
header("Location: homeress.php");
exit();
