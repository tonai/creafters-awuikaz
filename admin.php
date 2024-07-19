<?php
if ($_SESSION['connected'])
{
    if (verif_auth(LEVEL_MODO))
    {
        echo '<div id="administration" >';
        $cat = ((isset($_GET['cat']))? stripslashes(htmlspecialchars($_GET['cat'])): ''); //on récupère dans l'url la variable cat
        switch($cat) //1er switch
        {
            case "config": //ici configuration;
                if (verif_auth(LEVEL_ADMIN))
                {
                    echo'<h1>Configuration générale</h1>';
                    if (isset($_POST['submit']))
                    {
                        $query = 'SELECT config_nom,
                                        config_valeur
                                            FROM config';
                        $resultat= mysql_query($query);
                        //Avec cette boucle, on va pouvoir contrôler le résultat pour voir s'il a changé
                        while($data = mysql_fetch_assoc($resultat))
                        {
                            if ($data['config_valeur'] != $_POST[$data['config_nom']])
                            {
                                //On met ensuite à jour
                                $valeur = mysql_real_escape_string($_POST[$data['config_nom']]);
                                $query = 'UPDATE config
                                            SET config_valeur = "'.$valeur.'"
                                            WHERE config_nom = "'.$data['config_nom'].'"';
                                $result = mysql_query($query) or die(mysql_error());
                            }
                        }
                        echo'<p>Les nouvelles configurations ont été mises à jour !</p>';
                    }
                    else
                    {
                        echo '<form method="post" action="?page=admin&cat=config">';

                        //Le tableau associatif
                        $config_name = array(
                            "avatar_maxsize" => 'Taille maximale de l\'avatar',
                            "avatar_maxh" => 'Hauteur maximale de l\'avatar',
                            "avatar_maxl" => 'Largeur maximale de l\'avatar',
                            "sign_maxl" => 'Taille maximale de la signature',
                            "auth_bbcode_sign" => 'Autoriser le bbcode dans la signature',
                            "pseudo_maxsize" => 'Taille maximale du pseudo',
                            "pseudo_minsize" => 'Taille minimale du pseudo',
                            "pass_minsize" => 'Taille minimale du mot de passe',
                            "topic_par_page" => 'Nombre de topics par page',
                            "post_par_page" => 'Nombre de posts par page');
                        $query = 'SELECT config_nom,
                                        config_valeur
                                            FROM config';
                        $result = mysql_query($query) or die (mysql_error());
                        while($data = mysql_fetch_assoc($result))
                        {
                            echo '<p><label for='.stripslashes(htmlspecialchars($data['config_nom'])).'>'.stripslashes(htmlspecialchars($config_name[$data['config_nom']])).' : </label>';
                            echo '<input type="text" id="'.stripslashes(htmlspecialchars($data['config_nom'])).'" value="'.stripslashes(htmlspecialchars($data['config_valeur'])).'" name="'.stripslashes(htmlspecialchars($data['config_nom'])).'"></p>';
                        }
                        echo '<p><input type="submit" name="submit" value="Envoyer" /></p>';
                        echo '</form>';
                    }
                    echo '<p><a href="?page=admin&" >Revenir au menu admimistration</a><br/>';
                    echo '<a href="index.php" >Revenir au forum</a></p>';
                }
                break;

            case "creer_forum": //Création d'un forum
                if (verif_auth(LEVEL_ADMIN))
                {
                    echo '<h1>Créer un forum</h1>';
                    if (isset($_POST['submit']))
                    {
                        $titre = mysql_real_escape_string($_POST['nom']);
                        $desc = mysql_real_escape_string($_POST['desc']);
                        $cat = intval($_POST['cat']);
                        $query = 'INSERT INTO forum (forum_cat_id,
                                                    forum_name,
                                                    forum_desc)
                                            VALUES ("'.$cat.'",
                                                    "'.$titre.'",
                                                    "'.$desc.'")';
                        $result = mysql_query($query) or die(mysql_error());
                        echo'<p>Le forum '.$titre.' a été créé !</p>';
                    }
                    else
                    {
                        $query = 'SELECT cat_id,
                                        cat_nom
                                            FROM categorie
                                            ORDER BY cat_ordre DESC';
                        $result = mysql_query($query) or die (mysql_error());

                        if (mysql_num_rows($result) != 0) // Existe-t-il qu moins une catégorie?
                        {
                            ?>

                <form method="post" action="?page=admin&cat=creer_forum">
                    <p>
                        <label for="nom" >Nom :</label>
                        <input type="text" name="nom" />
                    </p>
                    <p>
                        <label for="desc" >Description :</label>
                        <textarea name="desc"></textarea>
                    </p>
                    <p>
                        <label for="cat" >Catégorie : </label>
                        <select name="cat">

                            <?php
                            while($data = mysql_fetch_assoc($result))
                            {
                                echo'<option value="'.$data['cat_id'].'">'.$data['cat_nom'].'</option>';
                            }
                            ?>

                        </select>
                    </p>
                    <p><input type="submit" name="submit" value="Envoyer" ></p>
                </form>

                            <?php
                        }
                        else
                        {
                            echo '<p>Aucune catégorie n\' a été créée !</p>';
                            echo '<p>Veuillez créer au moins <a href="?page=admin&cat=creer&c=c" >une categorie</a> avant de continuer.</p>';
                        }
                    }
                    echo '<p><a href="?page=admin" >Revenir au menu admimistration</a><br/>';
                    echo '<a href="index.php" >Revenir au forum</a></p>';
                }
                break;

            case "creer_categorie": //Création d'une catégorie
                if (verif_auth(LEVEL_ADMIN))
                {
                    echo '<h1>Créer une catégorie</h1>';
                    if (isset($_POST['submit']))
                    {
                        $titre = mysql_real_escape_string($_POST['nom']);
                        $query = 'INSERT INTO categorie (cat_nom)
                                                VALUES ("'.$titre.'")';
                        $result = mysql_query($query) or die(mysql_error());
                        echo'<p>La catégorie '.$titre.' a été créée !<br/>';
                    }
                    else
                    {
                        ?>

                <form method="post" action="?page=admin&cat=creer_categorie">
                    <p>
                        <label> Indiquez le nom de la catégorie :</label>
                        <input type="text" name="nom" />
                    </p>
                    <p><input type="submit" name="submit" value="Envoyer"></p>
                </form>

                        <?php
                    }
                    echo '<p><a href="?page=admin&" >Revenir au menu admimistration</a><br/>';
                    echo '<a href="index.php" >Revenir au forum</a></p>';
                }
                break;

            case "droits_forum": //Edition d'un forum
                if (verif_auth(LEVEL_ADMIN))
                {
                    echo '<h1>Editer les droits des forums</h1>';

                    $categorie= NULL;

                    if (isset($_POST['submit']))
                    {
                        $query = 'SELECT *
                                    FROM forum';
                        $result = mysql_query($query) or die(mysql_error());

                        foreach ($_POST['nom'] as $forumId => $nom)
                        {
                            $auth_view = intval($_POST['auth_view'][$forumId]);
                            $auth_post = intval($_POST['auth_post'][$forumId]);
                            $auth_topic = intval($_POST['auth_topic'][$forumId]);
                            $auth_sondage = intval($_POST['auth_sondage'][$forumId]);
                            $auth_annonce = intval($_POST['auth_annonce'][$forumId]);
                            $auth_modo = intval($_POST['auth_modo'][$forumId]);

                            mysql_data_seek($result, 0);
                            while ($data = mysql_fetch_assoc($result))
                            {
                                if ($forumId == $data['forum_id'])
                                {
                                    $test = ($auth_annonce != $data['auth_annonce']
                                            || $auth_modo != $data['auth_modo']
                                            || $auth_post != $data['auth_post']
                                            || $auth_sondage != $data['auth_sondage']
                                            || $auth_topic != $data['auth_topic']
                                            || $auth_view != $data['auth_view']);
                                    if ($test)
                                    {
                                        $query = 'UPDATE forum
                                                    SET auth_view = "'.$auth_view.'",
                                                        auth_post = "'.$auth_post.'",
                                                        auth_topic = "'.$auth_topic.'",
                                                        auth_sondage = "'.$auth_sondage.'",
                                                        auth_annonce = "'.$auth_annonce.'",
                                                        auth_modo = "'.$auth_modo.'"
                                                    WHERE forum_id = "'.intval($forumId).'"';
                                        mysql_query($query) or die(mysql_error());
                                        echo'<p>Le forum "'.$data['forum_name'].'" a été modifié !</p>';
                                    }
                                }
                            }
                        }
                    }
                    else
                    {
                        $query = 'SELECT cat_id,
                                        cat_nom,
                                        forum_name,
                                        forum_id,
                                        auth_view,
                                        auth_post,
                                        auth_topic,
                                        auth_annonce,
                                        auth_modo,
                                        auth_sondage
                                            FROM forum
                                            LEFT JOIN categorie ON forum_cat_id = cat_id
                                            ORDER BY cat_ordre, forum_ordre';
                        $result = mysql_query($query) or die (mysql_error());

                        if (mysql_num_rows($result) != 0) // Existe-t-il qu moins un forum?
                        {
                            echo '<form method="post" action="?page=admin&cat=droits_forum">';
                            ?>

                    <table>
                        <tr>
                            <th>Nom</th>
                            <th>Lire</th>
                            <th>Répondre</th>
                            <th>Poster</th>
                            <th>Sondage</th>
                            <th>Post-it</th>
                            <th>Modérer</th>
                        </tr>

                            <?php
                            while ($data = mysql_fetch_assoc($result))
                            {
                                if($categorie != $data['cat_id'])
                                {
                                    $categorie = $data['cat_id'];
                                    ?>

                                        <tr>
                                            <td colspan="7" class="categorie" ><?php echo stripslashes(htmlspecialchars($data['cat_nom'])); ?></td>
                                        </tr>

                                    <?php
                                }

                                echo '<tr>';
                                echo '<td><input type="hidden" name="nom['.$data['forum_id'].']" value="'.stripslashes(htmlspecialchars($data['forum_name'])).'" />'.stripslashes(htmlspecialchars($data['forum_name'])).'</td>';
                                echo '</select></td>';
                                foreach($list_champ as $idChamp => $champ)
                                {
                                    echo'<td><select name="'.$champ.'['.$data['forum_id'].']">';
                                    foreach($list_rang as $idRang => $rang)
                                    {
                                        $selected = (($idRang == $data[$champ])? 'selected="selected"': '');
                                        echo'<option value="'.$idRang.'" '.$selected.' >'.$rang.'</option>';
                                    }
                                    echo'</select></td>';
                                }
                                echo '</tr>';

                            }
                            echo '</table>';
                            echo '<p><input type="hidden" name="forum_id" value="'.$data['forum_id'].'" />';
                            echo '<input type="submit" name="submit" value="Envoyer"></p></form>';
                        }
                        else
                        {
                            echo '<p>Aucun forum n\' a été créé !</p>';
                            echo '<p>Veuillez créer au moins <a href="?page=admin&cat=creer&c=f" >un forum</a> avant de continuer.</p>';
                        }
                    }
                    echo '<p><a href="?page=admin" >Revenir au menu admimistration</a><br/>';
                    echo '<a href="index.php" >Revenir au forum</a></p>';
                }
                break;

            case "edit_forum": //Edition d'un forum
                echo '<h1>Editer des forums</h1>';
                if (isset($_POST['submit']))
                {
                    $query = 'SELECT *
                                FROM forum';
                    $result = mysql_query($query) or die(mysql_error());

                    foreach ($_POST['nom'] as $forumId => $nom)
                    {
                        $titre = mysql_real_escape_string($nom);
                        $desc = mysql_real_escape_string($_POST['desc'][$forumId]);
                        $cat = intval($_POST['cat'][$forumId]);
                        $ordre = intval($_POST['ordre'][$forumId]);
						

                        mysql_data_seek($result, 0);
                        while ($data = mysql_fetch_assoc($result))
                        {
                            if ($forumId == $data['forum_id'])
                            {
                                $test = ($titre != $data['forum_name']
                                        || $desc != $data['forum_desc']
                                        || $cat != $data['forum_cat_id']
                                        || $ordre != $data['forum_ordre']);
                                if ($test)
                                {
                                    $query = 'UPDATE forum
                                                SET forum_cat_id = "'.$cat.'",
                                                    forum_name = "'.$titre.'",
                                                    forum_desc = "'.$desc.'",
                                                    forum_ordre = "'.$ordre.'"
                                                WHERE forum_id = "'.intval($forumId).'"';
                                    mysql_query($query) or die(mysql_error());
                                    echo '<p>Le forum "'.$data['forum_name'].'" a été modifié !</p>';
                                }
                            }
                        }
                    }
                }
                else
                {
                    $query = 'SELECT forum_id,
                                    forum_name,
                                    forum_desc,
                                    forum_cat_id,
                                    forum_ordre
                                        FROM forum
                                        ORDER BY forum_cat_id, forum_ordre';
                    $result = mysql_query($query) or die (mysql_error());

                    $query2 = 'SELECT cat_id,
                                    cat_nom
                                        FROM categorie
                                        ORDER BY cat_ordre DESC';
                    $result2 = mysql_query($query2) or die(mysql_error());

                    if (mysql_num_rows($result) != 0) // Existe-t-il qu moins un forum?
                    {
                        echo '<form method="post" action="?page=admin&cat=edit_forum">';
                        ?>

                <table>
                    <tr>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Catégorie</th>
                        <th>Ordre</th>
                    </tr>

                        <?php
                        while ($data = mysql_fetch_assoc($result))
                        {
                            echo '<tr>';
                            echo '<td><input type="text" name="nom['.$data['forum_id'].']" value="'.stripslashes(htmlspecialchars($data['forum_name'])).'" /></td>';
                            echo '<td><textarea name="desc['.$data['forum_id'].']" >'.stripslashes(htmlspecialchars($data['forum_desc'])).'</textarea></td>';
                            echo '<td><select name="cat['.$data['forum_id'].']">';
                            mysql_data_seek($result2, 0);
                            while($data2 = mysql_fetch_assoc($result2))
                            {
                                $selected = (($data2['cat_id'] == $data['forum_cat_id'])? 'selected="selected"': '');
                                echo'<option value="'.$data2['cat_id'].'" '.$selected.' >'.stripslashes(htmlspecialchars($data2['cat_nom'])).'</option>';
                            }
                            echo '</select></td>';
                            echo '<td class="admin_ordre" ><input type="text" name="ordre['.$data['forum_id'].']" value="'.$data['forum_ordre'].'" /></td>';
                            echo '</tr>';
                        }
                        echo '</table>';
                        echo '<p><input type="hidden" name="forum_id" value="'.$data['forum_id'].'" />';
                        echo '<input type="submit" name="submit" value="Envoyer"></p></form>';
                    }
                    else
                    {
                        echo '<p>Aucun forum n\' a été créé !</p>';
                        echo '<p>Veuillez créer au moins <a href="?page=admin&cat=creer&c=f" >un forum</a> avant de continuer.</p>';
                    }
                }
                echo '<p><a href="?page=admin" >Revenir au menu admimistration</a><br/>';
                echo '<a href="index.php" >Revenir au forum</a></p>';
                break;

            case 'edit_categorie': //Edition d'un forum
                echo '<h1>Editer une catégorie</h1>';
                if (isset($_POST['submit']))
                {
                    $query = 'SELECT *
                                FROM categorie';
                    $result = mysql_query($query) or die(mysql_error());

                    foreach ($_POST['nom'] as $catId => $nom)
                    {
                        $titre = mysql_real_escape_string($nom);
                        $ordre = intval($_POST['ordre'][$catId]);

                        mysql_data_seek($result, 0);
                        while ($data = mysql_fetch_assoc($result))
                        {
                            if ($catId == $data['cat_id'])
                            {
                                $test = ($titre != $data['cat_nom']
                                        || $ordre != $data['cat_ordre']);
                                if ($test)
                                {
                                    $query = 'UPDATE categorie
                                                SET cat_nom = "'.$titre.'",
                                                    cat_ordre = "'.$ordre.'"
                                                WHERE cat_id = "'.intval($catId).'"';
                                    mysql_query($query) or die(mysql_error());
                                    echo'<p>La catégorie '.$data['cat_nom'].' a été modifiée !</p>';
                                }
                            }
                        }
                    }
                }
                else
                {
                    $query = 'SELECT cat_id,
                                    cat_nom,
                                    cat_ordre
                                        FROM categorie
                                        ORDER BY cat_ordre DESC';
                    $result = mysql_query($query) or die(mysql_error());

                    if (mysql_num_rows($result) != 0) // Existe-t-il qu moins une catégorie?
                    {
                        echo '<form method="post" action="?page=admin&cat=edit_categorie">';
                        ?>

                <table id="edit_categorie" >
                    <tr>
                        <th>Nom</th>
                        <th>Ordre</th>
                    </tr>

                    <?php
                        while($data = mysql_fetch_assoc($result))
                        {
                            echo '<tr>';
                            echo '<td><input type="text" name="nom['.$data['cat_id'].']" value="'.stripslashes(htmlspecialchars($data['cat_nom'])).'" /></td>';
                            echo '<td><input type="text" name="ordre['.$data['cat_id'].']" value="'.$data['cat_ordre'].'" /></td>';
                            echo '</tr>';
                        }
                        echo '</table>';
                        echo '<p><input type="submit" name="submit" value="Envoyer"></p>';
                        echo '</form>';
                    }
                    else
                    {
                        echo '<p>Aucune catégorie n\' a été créée !</p>';
                        echo '<p>Veuillez créer au moins <a href="?page=admin&cat=creer&c=c" >une catégorie</a> avant de continuer.</p>';
                    }
                }
                echo '<p><a href="?page=admin" >Revenir au menu admimistration</a><br/>';
                echo '<a href="index.php" >Revenir au forum</a></p>';
                break;

            case "membres":
                $membre_id = ((isset($_GET['membre']))? intval($_GET['membre']): null);
                if (!empty($membre_id) && verif_auth(4))
                {
                    if (isset($_POST['submit']))
                    {
                        $query = 'SELECT *
                                    FROM config';
                        $result = mysql_query($query) or die(mysql_error());
                        while ($data = mysql_fetch_assoc($result))
                        {
                            if ($data['config_nom'] == 'avatar_maxsize')
                                $avatar_maxsize = $data['config_valeur'];
                            elseif ($data['config_nom'] == 'avatar_maxh')
                                $avatar_maxh = $data['config_valeur'];
                            elseif ($data['config_nom'] == 'avatar_maxl')
                                $avatar_maxl = $data['config_valeur'];
                            elseif ($data['config_nom'] == 'sign_maxl')
                                $sign_maxl = $data['config_valeur'];
                            elseif ($data['config_nom'] == 'pseudo_maxsize')
                                $pseudo_maxsize = $data['config_valeur'];
                            elseif ($data['config_nom'] == 'pseudo_minsize')
                                $pseudo_minsize = $data['config_valeur'];
                        }

                        $pseudo_erreur1 = NULL;
                        $pseudo_erreur2 = NULL;
                        $email_erreur1 = NULL;
                        $email_erreur2 = NULL;
                        $email_erreur3 = NULL;
                        $msn_erreur = NULL;
                        $signature_erreur = NULL;
                        $avatar_erreur = NULL;
                        $avatar_erreur1 = NULL;
                        $avatar_erreur2 = NULL;
                        $avatar_erreur3 = NULL;

                        $i = 0; // compteur d'erreurs
                        $temps = time();
                        $membreId = intval($_POST['id']);
                        $pseudo = mysql_real_escape_string($_POST['pseudo']);
                        $signature = mysql_real_escape_string($_POST['signature']);
                        $email = mysql_real_escape_string($_POST['email']);
                        $msn = mysql_real_escape_string($_POST['msn']);
                        $website = mysql_real_escape_string($_POST['website']);
                        $occupation = mysql_real_escape_string($_POST['occupation']);
                        $localisation = mysql_real_escape_string($_POST['localisation']);

                        $query = 'SELECT count(*)
                                    FROM membres
                                    WHERE membre_id = "'.$membreId.'"';
                        $result = mysql_query($query) or die(mysql_error());
                        $membreExiste = mysql_result($result, 0);

                        if ($membreExiste != 0)
                        {
                            //Le pseudo doit être unique !
                            //Il faut donc vérifier s'il a été modifié, si c'est le cas, on vérifie bien
                            //l'unicité
                            $query = 'SELECT coount(*)
                                        FROM membres
                                        WHERE membre_pseudo = "'.$pseudo.'"
                                        AND membre_id <> "'.$membreId.'"';
                            $result = mysql_query($query) or die(mysql_error());
                            $pseudoExiste = mysql_result($result, 0);
                            if ($membreExiste != 0)
                            {
                                $pseudo_erreur1 = "Ce pseudo est déjà utilisé par un membre";
                                $i++;
                            }

                            if (strlen($pseudo) < $pseudo_minsize || strlen($pseudo) > $pseudo_maxsize)
                            {
                                $pseudo_erreur2 = "Ce pseudo est soit trop grand, soit trop petit";
                                $i++;
                            }

                            //Vérification de l'adresse email
                            //Il faut que l'adresse email n'ait jamais été utilisée (sauf si elle n'a pas été modifiée)
                            if (!empty($email))
                            {
                                $query = 'SELECT COUNT(*)
                                            FROM membres
                                            WHERE membre_email = "'.$email.'"
                                            AND membre_id <> "'.$membreId.'"';
                                $reslut = mysql_query($query, 0) or die(mysql_error());
                                $mailExiste = mysql_result($result, 0);
                                if ($mailExiste != 0)
                                {
                                    $email_erreur1 = "Cette adresse email est déjà utilisée par un membre";
                                    $i++;
                                }

                                //On vérifie la forme maintenant
                                if (!preg_match("#^[a-z0-9A-Z._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$#", $email) || empty($email))
                                {
                                    $email_erreur2 = "Cette adresse E-Mail n'a pas un format valide";
                                    $i++;
                                }
                            }
                            else
                            {
                                $email_erreur3 = "Votre adresse email est vide";
                                $i++;
                            }

                            //Vérification de l'adrese msn
                            if (!preg_match("#^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$#", $msn) && !empty($msn))
                            {
                                $msn_erreur = "Cette adresse MSN n'a pas un format valide";
                                $i++;
                            }

                            //Vérification de la signature
                            if (strlen($signature) > $sign_maxl)
                            {
                                $signature_erreur = "Cette signature est trop longue";
                                $i++;
                            }

                            //Vérification de l'avatar
                            if (!empty($_FILES['avatar']['size']))
                            {
                                $extensions_valides = array('.jpg', '.jpeg', '.gif', '.png');

                                if ($_FILES['avatar']['error'] > 0)
                                {
                                    $i++;
                                    $avatar_erreur = "Erreur lors du tranfsert de l'avatar : ";
                                }

                                if ($_FILES['avatar']['size'] > $avatar_maxsize)
                                {
                                    $i++;
                                    $avatar_erreur1 = "Le fichier est trop gros (".$_FILES['avatar']['size']." Octets contre ".$avatar_maxsize." Octets)";
                                }

                                $image_sizes = getimagesize($_FILES['avatar']['tmp_name']);
                                if ($image_sizes[0] > $avatar_maxl OR $image_sizes[1] > $avatar_maxh)
                                {
                                    $i++;
                                    $avatar_erreur2 = "Image trop large ou trop grande :(".$image_sizes[0]."x".$image_sizes[1]." contre ".$avatar_maxl."x".$avatar_maxh.")";
                                }

                                $extension_upload = strtolower(strrchr($_FILES['avatar']['name'], '.'));
                                if (!in_array($extension_upload,$extensions_valides) )
                                {
                                    $i++;
                                    $avatar_erreur3 = "Extension de l'avatar incorrecte";
                                }
                            }
                            if ($i == 0) // Si $i est vide, il n'y a pas d'erreur
                            {
                                if (!empty($_FILES['avatar']['size']))
                                {
                                    // vérification de l'existence d'un avatar auparavant
                                    $query = 'SELECT membre_avatar
                                                FROM membres
                                                WHERE membre_id = '.$membreId.'';
                                    $result = mysql_query($query) or die(mysql_error());
                                    $data = mysql_fetch_array($result);

                                    if (!empty($data['membre_avatar']))
                                        unlink("./images/avatars/".$data['membre_avatar']);

                                    //On déplace l'avatar
                                    $nomavatar = str_replace(' ','',time()).''.$extension_upload;
                                    $path = "./images/avatars/".$nomavatar;
                                    move_uploaded_file($_FILES['avatar']['tmp_name'],$path);

                                    $query = 'UPDATE membres
                                                SET membre_avatar = "'.$nomavatar.'"
                                                WHERE membre_id = "'.$membreId.'"';
                                    $result = mysql_query($query) or die(mysql_error());
                                }

                                //Une nouveauté ici : on peut choisisr de supprimer l'avatar
                                if (isset($_POST['delete']))
                                {
                                    // vérification de l'existence d'un avatar auparavant
                                    $query = 'SELECT membre_avatar
                                                FROM membres
                                                WHERE membre_id = '.$membreId.'';
                                    $result = mysql_query($query) or die(mysql_error());
                                    $data = mysql_fetch_array($result);

                                    if (!empty($data['membre_avatar']))
                                        unlink("./images/avatars/".$data['membre_avatar']);

                                    $query = 'UPDATE membres
                                                SET membre_avatar = ""
                                                WHERE membre_id = '.$membreId.'';
                                    $result = mysql_query($query);
                                }

                                if (isset($_POST['email_visible']))
                                    $email_visible = 1;
                                else
                                    $email_visible = 0;

                                //On modifie la table
                                $query = 'UPDATE membres
                                            SET membre_pseudo = "'.$pseudo.'"
                                                membre_email = "'.$email.'",
                                                membre_msn = "'.$msn.'",
                                                membre_siteweb = "'.$website.'",
                                                membre_signature = "'.$signature.'",
                                                membre_occup = "'.$occupation.'",
                                                membre_localisation = "'.$localisation.'",
                                                membre_email_visible = "'.$email_visible.'"
                                            WHERE membre_id = "'.$membreId.'"';
                                $result = mysql_query($query) or die (mysql_error());

                                echo'<h1>Modification terminée</h1>';
                                echo'<p>Le profil a été modifié avec succès !</p>';
                            }
                            else
                            {
                                echo'<h1>Modification interrompue</h1>';
                                echo'<p>Une ou plusieurs erreurs se sont produites pendant la modification du profil</p>';
                                echo'<p>'.$i.' erreur(s)</p>';
                                echo'<p>'.$pseudo_erreur1.'</p>';
                                echo'<p>'.$pseudo_erreur2.'</p>';
                                echo'<p>'.$email_erreur1.'</p>';
                                echo'<p>'.$email_erreur2.'</p>';
                                echo'<p>'.$email_erreur3.'</p>';
                                echo'<p>'.$msn_erreur.'</p>';
                                echo'<p>'.$signature_erreur.'</p>';
                                echo'<p>'.$avatar_erreur.'</p>';
                                echo'<p>'.$avatar_erreur1.'</p>';
                                echo'<p>'.$avatar_erreur2.'</p>';
                                echo'<p>'.$avatar_erreur3.'</p>';
                            }
                        }
                    }
                    else
                    {
                        //Requête qui ramène des info sur le membre à
                        //Partir de son pseudo
                        $query = 'SELECT *
                                    FROM membres
                                    WHERE membre_id = "'.$membre_id.'"';
                        $result = mysql_query($query) or die(mysql_error());

                        //Si la requête retourne un truc, le membre existe
                        if ($data = mysql_fetch_assoc($result))
                        {
                            if ($data['membre_email_visible'] == 1)
                                $checked = 'checked="checked"';
                            else
                                $checked = '';

                            echo'<h1>Profil de '.stripslashes(htmlspecialchars($data['membre_pseudo'])).'</h1>';
                            echo '<form method="post" action="?page=admin&cat=membres&membre='.$membre_id.'" enctype="multipart/form-data" id="profil" >';
                            ?>

                <fieldset>
                    <legend>Identifiants</legend>
                    <p>
                        <label for="pseudo">Pseudo :</label>
                        <input type="text" name="pseudo" class="w50" value="<?php echo stripslashes(htmlspecialchars($data['membre_pseudo'])); ?>" />
                    </p>
                </fieldset>
                <fieldset>
                    <legend>Contacts</legend>
                    <p>
                        <label for="email">Adresse E_Mail :</label>
                        <input type = "text" name="email" class="w50" value="<?php echo stripslashes(htmlspecialchars($data['membre_email'])); ?>" />
                    </p>
                    <p>
                        <label for="email">Visibilité de l'adresse Mail :</label>
                        <input type="checkbox" name="email_visible" checked="checked" />
                    </p>
                    <p>
                        <label for="msn">Adresse MSN :</label>
                        <input type = "text" name="msn" class="w50" value="<?php echo stripslashes(htmlspecialchars($data['membre_msn'])); ?>" />
                    </p>
                    <p>
                        <label for="website">Site web :</label>
                        <input type = "text" name="website" class="w50" value="<?php echo stripslashes(htmlspecialchars($data['membre_siteweb'])); ?>"/>
                    </p>
                </fieldset>
                <fieldset>
                    <legend>Informations supplémentaire</legend>
                    <p>
                        <label for="occupation">Occupation :</label>
                        <input type = "text" name="occupation" class="w50" value="<?php echo stripslashes(htmlspecialchars($data['membre_occup'])); ?>" />
                    </p>
                    <p>
                        <label for="localisation">Localisation :</label>
                        <input type = "text" name="localisation" class="w50" value="<?php echo stripslashes(htmlspecialchars($data['membre_localisation'])); ?>" />
                    </p>
                </fieldset>
                <fieldset>
                    <legend>Profil sur le forum</legend>
                    <p>
                        <label for="avatar">Changer l'avatar :</label>
                        <input type="file" name="avatar" class="w50" />
                    </p>
                    <p>
                        <label> Supprimer l'avatar</label>
                        <input type="checkbox" name="delete" value="delete" />
                    </p>
                    <p>
                        <label>Avatar actuel :</label>

                            <?php
                            if (!empty($data['membre_avatar']))
                                echo '<img src="./images/avatars/'.$data['membre_avatar'].'" alt="avatar" />';
                            else
                                echo 'Vous n\'avez pas d\'avatar';
                            ?>

                    </p>
                    <p>
                        <label for="signature">Signature :</label>
                        <textarea name="signature" ><?php echo stripslashes(htmlspecialchars($data['membre_signature'])) ?></textarea>
                    </p>
                </fieldset>

                            <?php
                            echo '<p class="center" ><input type="hidden" name="id" value="'.intval($data['membre_id']).'">';
                            echo '<input type="submit" value="Modifier le profil" /></p>';
                            echo '</form>';
                        }
                        else
                        {
                            echo' <p>Erreur, Ce membre n existe  !</p>';
                        }
                    }
                    echo '<p><a href="?page=admin&cat=membres" >Revenir à la gestion des membres</a><br/>';
                    echo '<a href="?page=admin" >Revenir au menu admimistration</a><br/>';
                    echo '<a href="index.php" >Revenir au forum</a></p>';
                }
                else
                {
                    echo'<h1>Gestion par membre</h1>';
                    if (isset($_POST['submit']))
                    {
                        $query = 'SELECT membre_id,
                                        membre_pseudo,
                                        membre_rang
                                            FROM membres';
                        $result = mysql_query($query) or die(mysql_error());

                        foreach ($_POST['rang'] as $idRang => $rang)
                        {
                            mysql_data_seek($result, 0);
                            while ($data = mysql_fetch_assoc($result))
                            {
                                if ($idRang == $data['membre_id'])
                                {
                                    if ($rang != $data['membre_rang'])
                                    {
                                        $query = 'UPDATE membres
                                                    SET membre_rang = "'.intval($rang).'"
                                                    WHERE membre_id = "'.intval($membreId).'"';
                                        mysql_query($query) or die(mysql_error());
                                        if ($rang == 0)
                                            echo'<p>Le membre '.$data['membre_pseudo'].' a été banni !</p>';
                                        elseif ($data['membre_id'] == 0)
                                            echo'<p>Le membre '.$data['membre_pseudo'].' n\'est plus banni !</p>';
                                        else
                                            echo'<p>Le niveau du membre '.$data['membre_pseudo'].' a été modifié !</p>';

                                    }
                                }
                            }
                        }
                    }
                    else
                    {
                        $query = 'SELECT membre_id,
                                        membre_pseudo,
                                        membre_rang
                                            FROM membres';
                        $result = mysql_query($query) or die(mysql_error());

                        echo '<form method="post" action="?page=admin&cat=membres">';
                        ?>

                <table>
                    <tr>
                        <th>Pseudo</th>
                        <th>Rang</th>
                    </tr>

                        <?php
                        while ($data = mysql_fetch_assoc($result))
                        {
                            echo '<tr>';
                            if (verif_auth(LEVEL_ADMIN))
                                echo '<td><a href="?page=admin&cat=membres&membre='.$data['membre_id'].'" >'.$data['membre_pseudo'].'</a></td>';
                            else
                                echo '<td>'.$data['membre_pseudo'].'</td>';

                            if ($data['membre_rang'] < $_SESSION['level'] || verif_auth(LEVEL_ADMIN))
                            {
                                echo'<td><select name="rang['.$data['membre_id'].']">';
                                foreach($list_rang as $idRang => $rang)
                                {
                                    if ($idRang < $_SESSION['level'] || verif_auth(LEVEL_ADMIN))
                                    {
                                        $selected = (($idRang == $data['membre_rang'])? 'selected="selected"': '');
                                        echo'<option value="'.$idRang.'" '.$selected.' >'.$rang.'</option>';
                                    }
                                }
                                echo'</select></td>';
                            }
                            else
                                echo '<td>'.$list_rang[$data['membre_rang']].'</td>';

                            echo '</tr>';
                        }
                        echo '</table>';
                        echo '<p><input type="submit" name="submit" value="Envoyer"></p>';
                        echo '</form>';
                    }
                    echo '<p><a href="?page=admin" >Revenir au menu admimistration</a><br/>';
                    echo '<a href="index.php" >Revenir au forum</a></p>';
                }
                break;

            default:
                if (verif_auth(LEVEL_ADMIN))
                {
                    ?>

                        <p>
                            <a href="?page=admin&cat=config" >Configuration générale</a><br/>
                            <a href="?page=admin&cat=creer_forum" >Créer un forum</a><br/>
                            <a href="?page=admin&cat=creer_categorie" >Créer une catégorie</a><br/>
                            <a href="?page=admin&cat=edit_forum" >Editer un forum</a><br/>
                            <a href="?page=admin&cat=droits_forum" >Editer les droits d'un forum</a><br/>
                            <a href="?page=admin&cat=edit_categorie" >Editer une catégorie</a><br/>
                            <a href="?page=admin&cat=membres" >Gestion des membres</a><br/>
                        </p>
                        <p>
                            <a href="index.php" >Revenir au forum</a>
                        </p>

                    <?php
                }
                else
                {
                    ?>

                        <p>
                            <a href="?page=admin&cat=edit_forum" >Editer un forum</a><br/>
                            <a href="?page=admin&cat=edit_categorie" >Editer une catégorie</a><br/>
                            <a href="?page=admin&cat=membres" >Gestion des membres</a><br/>
                        </p>
                        <p>
                            <a href="index.php" >Revenir au forum</a>
                        </p>

                    <?php
                }
                break;
        }
        echo '</div>';
    }
    else
    {
        echo '<p>Vous n\'avez pas l\'autorisation d\'acceder à cette page</p>';
    }
}
?>
