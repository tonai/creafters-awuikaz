<?php
// On crée la session avant tout
session_start();

// On défini la configuration :
if(!IsSet($_GET['nbr_chiffres'])) {
     $nbr_chiffres = 6; // Nombre de chiffres qui formerons le nombre par défaut
}
else {
     $nbr_chiffres = $_GET['nbr_chiffres']; // Si l'on met dans l'adresse un ?nbr_chiffres=X
}

// Là, on défini le header de la page pour la transformer en image
header ("Content-type: image/png");
// Là, on crée notre image
$_img = imagecreatefrompng('fond_verif_img.png');
// On défini maintenant les couleurs
// Couleur de fond :
$arriere_plan = imagecolorallocate($_img, 0, 0, 0); // Au cas où on utiliserai pas d'image de fond, on utilise cette couleur là.
// Autres couleurs :
$avant_plan = imagecolorallocate($_img, 255, 255, 255); // Couleur des chiffres
##### Ici on crée la variable qui contiendra le nombre aléatoire #####
$i = 0;
while($i < $nbr_chiffres) {
        $chiffre = mt_rand(0, 9); // On génère le nombre aléatoire
        $chiffres[$i] = $chiffre;
        $i++;
}
$nombre = null;
// On explore le tableau $chiffres afin d'y afficher toutes les entrées qu'y s'y trouvent
foreach ($chiffres as $caractere) {
        $nombre .= $caractere;
}
##### On as fini de créer le nombre aléatoire, on le rentre maintenant dans une variable de session #####
$_SESSION['aleat_nbr'] = $nombre;
// On détruit les variables inutiles :
unset($chiffre);
unset($i);
unset($caractere);
unset($chiffres);
imagestring($_img, 5, 18, 8, $nombre, $avant_plan);

imagepng($_img);
?>
