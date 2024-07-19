<?php
header("Content-Type: text/plain");
$postId = ((isset($_POST["id"])) ? $_POST["id"] : NULL);
if (!empty($postId))
{
    include("includes/configbdd.php");
    mysql_connect($adresse, $nom, $motdepasse);
    mysql_select_db($database);

    $query = 'SELECT post_texte
                FROM post
                WHERE post_id = "'.$postId.'"';
    $result = mysql_query($query)or die(mysql_error());
    if (mysql_num_rows($result) > 0)
    {
        $data = mysql_fetch_assoc($result);
        echo $data['post_texte'];
    }

    mysql_close();
}
?>
