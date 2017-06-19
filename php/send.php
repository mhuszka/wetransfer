<?php

$votreemail = $_POST["votreemail"];
$destemail = $_POST["destemail"];
$message = $_POST["message"];
$error = array();




if ($_SERVER["REQUEST_METHOD"] == "POST") {


    if (empty($_POST["votreemail"])) {

        $email_a = $_POST["votreemail"];

        if (filter_var($email_a, FILTER_VALIDATE_EMAIL)) {
            $error['votreemail'] = true; // vide
        }
    }else{
        $error['votreemail'] = false; //correctement rempli
    }
}


if ($_POST["destemail"]) {

    $mailError = array();

    foreach($_POST['destemail'] as $destemailval){

        if (filter_var($destemailval, FILTER_VALIDATE_EMAIL)) {
            //$error['destemail'] = true; // vide
            array_push($mailError, 1);
        }
        else{
            array_push($mailError, 0);
            //$error['destemail'] = false; //correctement rempli
        }
    }

    if(in_array (0, $mailError)){
        $error['destemail'] = true; //vide ou faux

    }else{
        $error['destemail'] = false; //correctement rempli
    }
}

if(empty($_POST['message'])){


    $error['message'] = true; //vide

}else{

    $error['message'] = false; //correctement rempli
}

if(empty($_FILES['files1'])){

    $error['files1'] = true; //vide

}else{

    $error['files1'] = false; //correctement rempli
}

$dossier = uniqid();
mkdir('../fichiers/'.$dossier);

for($i=0; $i<count($_FILES["fichiers"]["tmp_name"]);$i++){
    $tmp_name = $_FILES["fichiers"]["tmp_name"][$i];
    $name = $_FILES["fichiers"]["name"][$i];
    move_uploaded_file($tmp_name, '../fichiers/'.$dossier.'/'.$name);
}

$zip = new ZipArchive();

if(is_dir('../fichiers/'.$dossier)){
    // On teste si le dossier existe, car sans ça le script risque de provoquer des erreurs.

    if($zip->open('../fichiers/'.$dossier.'.zip', ZipArchive::CREATE) == TRUE)
    {
        // Ouverture de l’archive réussie.

        // Récupération des fichiers.
        $fichiers = scandir('../fichiers/'.$dossier.'/');

        // On enlève . et .. qui représentent le dossier courant et le dossier parent.
        unset($fichiers[0], $fichiers[1]);

        foreach($fichiers as $f)
        {
            // On ajoute chaque fichier à l’archive en spécifiant l’argument optionnel.
            // Pour ne pas créer de dossier dans l’archive.
            if(!$zip->addFile('../fichiers/'.$dossier.'/'.$f, $f))
            {
                echo 'Impossible d&#039;ajouter &quot;'.$f.'&quot;.<br/>';
            }
        }		

        // On ferme l’archive.
        $zip->close();


    } else {
        // Erreur lors de l’ouverture.
        // On peut ajouter du code ici pour gérer les différentes erreurs.
        echo 'Erreur, impossible de créer l&#039;archive.';
    }
} else {
    // Possibilité de créer le dossier avec mkdir().
    echo 'Le dossier &quot;upload/&quot; n&#039;existe pas.';
}


// je suppr l'image non zippée


$path = "/home/bmelissa/wetransfer/fichiers/".$dossier;

function rrmdir($dir) { 
   if (is_dir($dir)) { 
     $objects = scandir($dir); 
     foreach ($objects as $object) { 
       if ($object != "." && $object != "..") { 
         if (is_dir($dir."/".$object))
           rrmdir($dir."/".$object);
         else
           unlink($dir."/".$object); 
       } 
     }
     rmdir($dir); 
   } 
 }

rrmdir($path);


// J'envoie l'email
$url = "http://vesoul.codeur.online/front/bmelissa/wetransfer/fichiers/".$dossier.".zip";

$subject = $votreemail." vous envoie un Paper Plane !";
$message = "
     <html>
      <head>
       <title>".$votreemail."vous envoie un Paper Plane ! </title>
      </head>
      <body>
       <p style='font-size:20px; font-weight:bold'>".$votreemail." vous envoie un Paper Plane !</p> 
       <p style='font-size:16px'>".$message."</p>
       <p><a href=".$url.">Cliquez ici pour télécharger votre Paper plane !</a></p>
      </body>
     </html>
     ";

$headers = 'From:'.$votreemail. "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

foreach($_POST['destemail'] as $destemailval){

    $to = $destemailval;
    mail($to, $subject, $message, $headers);
};


echo json_encode($error);


?>