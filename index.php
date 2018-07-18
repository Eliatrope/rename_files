<?php
//https://developer.mozilla.org/fr/docs/Web/HTML/Element/Input/file
//Voir le dernier exemple pour créer un input upload avec check du bon type etc
//Set up
//Init main loop => get files in directory
    $i = 0;
    $dir = getcwd();
    $filesallowed = array('zip');
    $upload_dir = trim(" / ")."images".trim(" / ");
    $dir_images=$dir."\images";
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
	  <title>Module renommage fichiers</title>
	  <link rel="stylesheet" type="text/css" href="assets/style.css"/>
  </head>
  <body>
  <h1><a style="text-decoration:none;color:black;"href="index.php">Module de renommage de masse de fichiers</a></h1>
<?php
//Init step 1
if(!isset($_POST["submit_values_array"]) && !isset($_POST["submit"])){
?>
  <h2>Étape 1)</h2>
  <p>Merci de télécharger l'archive zip ainsi que de renseigner les valeurs des fichiers à renommer :</p>
    <form enctype="multipart/form-data" method="POST" action="index.php">
      <textarea style="width:600px;height:400px;" id="array_renamed_files" name="array_renamed_files">
        Merci de respecter la syntaxe au maximum ; chaque nom de fichier doit être séparé par un seul et unique espace : aaaaaaaa.jpg bbbbbbbb.jpg cccccccc.jpg
      </textarea>
      <input type="hidden" name="MAX_FILE_SIZE" value="300000000"/>
      <input type="file" name="archive_photos" accept="application/zip, application/x-zip-compressed" />
      <input type="submit" value="Envoyer" name="submit_values_array"/>
    </form>
<?php
}else{
    //if(!isset($_POST["submit"])){
    if(isset($_POST["submit"])){
      $uploadfile = $_POST["uploadfile"];
      $tmp_name_uploadfile = $_POST["tmp_name_uploadfile"];
    }else{
      $tmp_name_uploadfile = $_FILES['archive_photos']['tmp_name'];
      $uploadfile = $dir_images.trim(" \ ").basename($_FILES['archive_photos']['name']);
      //echo $uploadfile;
      $ext_file = pathinfo($uploadfile, PATHINFO_EXTENSION);
    }
    //var_dump($_FILES['archive_photos']);
  //}elseif(isset($_POST["submit_values_array"])){
    if(is_uploaded_file($tmp_name_uploadfile)){
      if (move_uploaded_file($tmp_name_uploadfile, $uploadfile)) {
        if(!in_array($ext_file, $filesallowed)){
          echo "Le fichier uploadé n'est pas au bon format! Seul le format zip est autorisé, merci de réessayer.";
          exit;
        }else{
          $zip = new ZipArchive;
          //var_dump($zip);
          if ($zip->open($uploadfile) === TRUE) {
              $zip->extractTo($dir_images);
              $zip->close();
              //echo 'ok';
              //Delete tous les .zip et les .html
              $fullpath = __DIR__ . trim(" /images/ ");
              array_map('unlink', glob( "$fullpath*.html" ));
              array_map('unlink', glob( "$fullpath*.zip" ));
              //Liste les fichiers
              $files = array_slice(scandir($dir_images), 2);
              sort($files, SORT_NATURAL);
              $count_files = count($files);
          }else{
              echo 'fail, archive introuvable';
          }
        }

      }else{
        echo "move_uploaded_file(error) Fichier incorrect. Recommencez";
        exit;
      }
    }else{
      if(!isset($_POST["submit"])){
        echo "is_uploaded_file(error) Fichier incorrect. Recommencez";
        exit;
      }
    }
  //}
  $files = array_slice(scandir($dir_images), 2);
  sort($files, SORT_NATURAL);
  $count_files = count($files);
    if(isset($_POST["array_renamed_files"]) && !empty($_POST["array_renamed_files"])){
      $renamed_files = explode(" ", trim($_POST["array_renamed_files"]));
      $count_renamed_files = count($renamed_files);

      //var_dump($renamed_files);
    }
?>

<?php
}//endif 1st step

//Init step 2
if((isset($renamed_files) && !empty($renamed_files)) && isset($_POST["submit_values_array"])){
?>
<h2>Étape 2)</h2>
<p>Ci-après, un récapitulatif du traitement qui va être effectué ; merci de vérifier si tout est en ordre. Si tel n'est pas le cas, vous avez la possibilité de remplacer le nom en modifiant la valeur de l'input. N'oubliez pas de mettre le nom complet, extension comprise (xxxxxxxx.jpg). Une fois que tout est en ordre, cliquez sur valider pour renommer les fichiers.</p>
<?php
  if(isset($_POST["submit"])){
    $renamed_files = $_POST["renamed_files"];
  }
?>
  <form method="POST" action="index.php">
    <table>
		<tr>
			<th>N°</th>
			<th>Fichier à renommer</th>
			<th>En :</th>
		</tr>
    <?php
        while($i!=$count_files){

      ?>
      		<tr>
      			<td><?php echo $i+1;?></td>
      			<td style="text-align:center;"><img style="width:100px;" src="images/<?php echo $files[$i];?>" alt="<?php echo $files[$i];?>" /><br /> <?php echo $files[$i];?></td>
      			<td style="font-weight:bold;"><input placeholder="<?php echo $renamed_files[$i];?>" type="text" minlength="12" maxlength="12" value="<?php echo $renamed_files[$i];?>" id="<?php echo $i?>" name="<?php echo "photo_".$i;?>" /></td>
      		</tr>

        <?php
      		$i++;
        }
        ?>
        <tr>
  				<td colspan="3" style="text-align:center; font-weight:bold;">Total : <?php echo $count_files;?></td>
  			</tr>
  		</table>
  		<p style="font-weight:bold; text-transform:uppercase; font-size:24px; color:red;">En cliquant sur le bouton Valider ci-après, le renommage sera effectué et vous ne pourrez plus revenir en arrière.</p>
      <input type="hidden" name="count_renamed_files" value="<?php echo $count_renamed_files;?>" />
      <input type="hidden" name="renamed_files" value="<?php print_r( $renamed_files);?>" />
      <input type="hidden" name="uploadfile" value="<?php echo $uploadfile;?>"/>
      <input type="hidden" name="tmp_name_uploadfile" value="<?php echo $tmp_name_uploadfile;?>"/>
  		<input type="submit" name="submit" id="submit" value="VALIDER"/>
<?php
}//endif 2nd step

//INIT STEP 3
$b=0;
if(isset($_POST["submit"])){
  $uploadfile = $_POST["uploadfile"];
  echo "<h2>Étape 3)</h2>";
  $count_renamed_files = $_POST["count_renamed_files"];
  //echo $count_renamed_files;
  if($count_files==$count_renamed_files){
    while($count_renamed_files!=$b){
      $my_file=$dir_images.trim(" \ ").$files[$b];
      $renamed_files[$b] = $_POST["photo_".$b];
      $renamed_into=$dir_images.trim(" \ ").$renamed_files[$b];
      //echo $my_file;
      //echo $renamed_into;
      rename($my_file, $renamed_into);
      if($b==$count_files-1){
        echo "Le traitement a été effectué ; vérifiez le renommage des fichiers.";
      }
      $b++;
    }
  }else{
    echo "ERREUR : Attention, le nombre de photos contenus dans l'archive (".$count_files.") n'est pas équivalent au nombre d'entrées saisies dans l'input initial (".$count_renamed_files."). Fin du traitement, les fichiers ne peuvent pas être renommés ; vérifiez l'archive et les données saisies.";
    exit;
  }
}
?>
<?php
/*Le but de la feature étant d'automatiser un renommage sur un grand nombre de fichiers selon un pattern bien spécifique.
On a donc un dossier, dans lequel plusieurs fichiers au format jpg sont présents.
Ces fichiers ont été scannés puis découpés dans un ordre bien précis (à terme, ils porteront tous un identifiant à 8 chiffres unique).
La feature doit renommer chaque fichier un par un, avec le bon identifiant, celui associé.
C'est pourquoi le tri et le scan, dans le bon ordre, des photos est primordial.

nomdefichier1.jpg -> 01234567.jpg
nomdefichier2.jpg -> 89012345.jpg

Il faut donc analyser le contenu du dossier cible, stack l'ensemble des noms de fichiers dans un array. Stack également, dans un array, le nouveau nom des fichiers. Puis faire tourner le tout dans une boucle jusqu'à ce que tous les fichiers soient renommés.

Evolution au 17/07/2018
Trois étapes principales :
1) Demander les valeurs de base au user (donc mise en forme à faire lors du traitement php) dans un textarea : xxxxxxxx.jpg xxxxxxxx.jpg etc
2) Dès que les data sont récupérées, faire un récap' de la request dans un tableau avec possibilité de modifier les input
3) Validation du renommage par le user : update du tableau (ou suppression, à voir) puis rename des fichiers avec les valeurs des input.
*/


    //$renamed_files = array('32900988.jpg', '33000051.jpg', '33000859.jpg', '33900622.jpg', '33900718.jpg', '33901671.jpg', '33901783.jpg', '34000049.jpg', '34900112.jpg', '34900188.jpg', '34900501.jpg', '34901085.jpg', '34901484.jpg', '35900540.jpg', '35900723.jpg', '35901880.jpg', '36000482.jpg', '36901557.jpg', '37000003.jpg', '37000281.jpg', '37901367.jpg', '37901773.jpg', '38000205.jpg', '38000206.jpg', '38000208.jpg', '38000216.jpg', '38000241.jpg');

    //You can copy this if u want an example for the textarea pattern
    //$renamed_files = array(32900988.jpg 33000051.jpg 33000859.jpg 33900622.jpg 33900718.jpg 33901671.jpg 33901783.jpg 34000049.jpg 34900112.jpg 34900188.jpg);
    //you'll find an archive with 10 images too
?>
	</form>
  </body>
</html>
