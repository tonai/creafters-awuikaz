<?php
header("Content-Type: text/plain");
$postId = ((isset($_POST["id"])) ? $_POST["id"] : NULL);
if (!empty($postId))
{
    include("includes/configbdd.php");
    $mysqli = mysqli_connect($adresse, $nom, $motdepasse);
    mysqli_select_db($mysqli, $database);

    $query = 'SELECT post_texte
                FROM post
                WHERE post_id = "'.$postId.'"';
    $result = mysqli_query($mysqli, $query)or die(mysqli_error());
    if (mysqli_num_rows($result) > 0)
    {
        $data = mysqli_fetch_assoc($result);
        echo $data['post_texte'];
    }

    mysqli_close($mysqli);
}
?>
