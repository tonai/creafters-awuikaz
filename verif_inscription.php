<?php
if (isset($_GET['code'])) {
    $query = 'SELECT count(membre_id)
                FROM membres
                WHERE membre_code = "'.intval($_GET['code']).'"';
	$result = mysql_query($query) or dir (mysql_error());
    $codeExiste = mysql_result($result, 0);

    if ($codeExiste != 0)
    {
        $query = 'UPDATE membres
                    SET membre_verif = 1
                    WHERE membre_code = "'.intval($_GET['code']).'"';
        $result = mysql_query($query) or dir (mysql_error());

        $query = 'SELECT membre_email
                    FROM membres
                    WHERE membre_rang = "'.LEVEL_ADMIN.'"';
        $result = mysql_query($query) or dir (mysql_error());

        $to = '';
        for ($i=0; $i<mysql_num_rows($result); $i++)
        {
            $data = mysql_fetch_assoc($result);
            if ($i != 0)
                $to .= ', ';
            $to .= $data['membre_email'];
        }

        $query = 'SELECT membre_pseudo
                    FROM membres
                    WHERE membre_code = "'.intval($_GET['code']).'"';
        $result = mysql_query($query) or dir (mysql_error());
        $data = mysql_fetch_assoc($result);

        $message = $data['membre_pseudo'].' vient de s\'inscrire sur le forum';
        $titre = 'nouvel inscrit sur le forum "Creafters Awuikaz"';

        mail($to, $titre, $message);
        ?>

	<p>
        Merci !<br/>
        Votre inscription est maintenant terminé.<br/>
        <a href="index.php" >Accueil du forum</a>
    </p>

        <?php
    }
    else
    {
        echo '<p>Erreur ! Ce code n\'existe pas !</p>';
    }
}
?>