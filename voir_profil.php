<?php
//On récupère la valeur de nos variables passées par URL
$action = ((isset($_GET['action']))? stripslashes(htmlspecialchars($_GET['action'])): '');

//On regarde la valeur de la variable $action
switch($action)
{
    //Si c'est "consulter"
    case "consulter":
        $membre = ((isset($_GET['m']))? intval($_GET['m']): '');
        //On récupère les infos du membre
        $query = 'SELECT *
                    FROM membres
                    WHERE membre_id = "'.$membre.'"';
        $result = mysql_query($query);
        if ($data = mysql_fetch_assoc($result))
        {
            //On affiche les infos sur le membre
            echo '<h1>Profil de '.stripslashes(htmlspecialchars($data['membre_pseudo'])).'</h1>';
            if (!empty($data['membre_avatar']))
                echo '<img src="./images/avatars/'.$data['membre_avatar'].'" alt="avatar" />';
            else
                echo 'Ce membre n\'a pas d\'avatar';
            echo '<p>';
            if ($_SESSION['connected'])
            {
                echo '<strong>Adresse E-Mail : </strong>';
                if ($data['membre_email_visible'] == 1)
                    echo '<a href="mailto:'.stripslashes(htmlspecialchars($data['membre_email'])).'">'.stripslashes(htmlspecialchars($data['membre_email'])).'</a>';
                echo '<br/>';
                echo '<strong>MSN Messenger : </strong>'.stripslashes(htmlspecialchars($data['membre_msn'])).'<br />';
                echo '<strong>Site Web : </strong><a href="'.stripslashes(htmlspecialchars($data['membre_siteweb'])).'">'.stripslashes(htmlspecialchars($data['membre_siteweb'])).'</a><br/>';
            }
            echo '<strong>Occupation : </strong>'.stripslashes(htmlspecialchars($data['membre_occup'])).'<br/>';
            echo '<strong>Localisation : </strong>'.stripslashes(htmlspecialchars($data['membre_localisation'])).'<br/>';
            echo '<strong>Signature : </strong>'.stripslashes(htmlspecialchars($data['membre_signature'])).'<br/>';
            echo 'Ce membre est inscrit depuis le <strong>'.date('d/m/Y',$data['membre_inscrit']).'</strong><br/>';
            echo 'Ce membre a posté <strong>'.$data['membre_post'].'</strong> messages<br/><br/>';
            echo '</p>';
            if ($_SESSION['connected'])
            {
                echo '<p><a href="?page=mp&action=nouveau&m='.$data['membre_id'].'" >Envoyer un mp</a></p>';
            }
        }
        //Si on ne trouve pas d'info
        else
        {
            echo'<p>Ce membre ne semble pas exister!</p>';
        }
        break;

    //Si on choisit de modifier son profil
    case "modifier":
        if ($_SESSION['connected'])
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
                    elseif ($data['config_nom'] == 'pass_minsize')
                        $pass_minsize = $data['config_valeur'];
                }

                $mdp_erreur1 = NULL;
                $mdp_erreur2 = NULL;
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
                $signature = mysql_real_escape_string($_POST['signature']);
                $email = mysql_real_escape_string($_POST['email']);
                $msn = mysql_real_escape_string($_POST['msn']);
                $website = mysql_real_escape_string($_POST['website']);
                $occupation = mysql_real_escape_string($_POST['occupation']);
                $localisation = mysql_real_escape_string($_POST['localisation']);
                $pass = mysql_real_escape_string($_POST['password']);

                if ($pass != '')
                {
                    if (strlen($pass) >= $pass_minsize && strlen($pass) < 100)
                    {
                        $pass = md5($_POST['password']);
                        $confirm = md5($_POST['confirm']);

                        //Vérification du mdp
                        if ($pass != $confirm)
                        {
                            $mdp_erreur1 = "Votre mot de passe et le mot de passe de confirmation sont diffèrent";
                            $i++;
                        }
                    }
                    elseif (strlen($pass) < $pass_minsize)
                    {
                        $mdp_erreur2 = "Votre mot de passe est trop court";
                        $i++;
                    }
                    elseif (strlen($pass) > 100)
                    {
                        $mdp_erreur2 = "Votre mot de passe est trop log";
                        $i++;
                    }
                }

                //Vérification de l'adresse email
                //Il faut que l'adresse email n'ait jamais été utilisée (sauf si elle n'a pas été modifiée)
                if (!empty($email))
                {
                    $query = 'SELECT COUNT(membre_id)
                                FROM membres
                                WHERE membre_email = "'.$email.'"
                                AND membre_id <> '.$membreId.'';
                    $result = mysql_query($query) or die(mysql_error());
                    $mailExiste = mysql_result($result, 0);
                    if ($mailExiste != 0)
                    {
                        $email_erreur1 = "Votre adresse email est déjà utilisée par un membre";
                        $i++;
                    }

                    //On vérifie la forme maintenant
                    if (!preg_match("#^[a-z0-9A-Z._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$#", $email) || empty($email))
                    {
                        $email_erreur2 = "Votre nouvelle adresse E-Mail n'a pas un format valide";
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
                    $msn_erreur = "Votre nouvelle adresse MSN n'a pas un format valide";
                    $i++;
                }

                //Vérification de la signature
                if (strlen($signature) > $sign_maxl)
                {
                    $signature_erreur = "Votre nouvelle signature est trop longue";
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

                if ($i == 0 && $membreId == $_SESSION['id']) // S'il n'y a pas d'erreur et s'il s'agit bien de la personne
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
                    echo'<h1>Modification terminée</h1>';
                    echo'<p>Votre profil a été modifié avec succès !</p>';
                    echo'<p>Cliquez <a href="index.php">ici</a> pour revenir à la page d accueil</p>';

                    if (isset($_POST['email_visible']))
                        $email_visible = 1;
                    else
                        $email_visible = 0;

                    //On modifie la table
                    if ($pass != '')
                    {
                        $query = 'UPDATE membres
                                    SET membre_mdp ="'.$pass.'",
                                        membre_email = "'.$email.'",
                                        membre_msn = "'.$msn.'",
                                        membre_siteweb = "'.$website.'",
                                        membre_signature = "'.$signature.'",
                                        membre_occup = "'.$occupation.'",
                                        membre_localisation = "'.$localisation.'",
                                        membre_email_visible = "'.$email_visible.'"
                                    WHERE membre_id = "'.$membreId.'"';
                        $result = mysql_query($query) or die (mysql_error());
                    }
                    else
                    {
                        $query = 'UPDATE membres
                                    SET membre_email = "'.$email.'",
                                        membre_msn = "'.$msn.'",
                                        membre_siteweb = "'.$website.'",
                                        membre_signature = "'.$signature.'",
                                        membre_occup = "'.$occupation.'",
                                        membre_localisation = "'.$localisation.'",
                                        membre_email_visible = "'.$email_visible.'"
                                    WHERE membre_id = "'.$membreId.'"';
                        $result = mysql_query($query) or die (mysql_error());
                    }
                }
                else
                {
                    echo'<h1>Modification interrompue</h1>';
                    echo'<p>Une ou plusieurs erreurs se sont produites pendant la modification du profil</p>';
                    echo'<p>'.$i.' erreur(s)</p>';
                    echo'<p>'.$mdp_erreur1.'</p>';
                    echo'<p>'.$mdp_erreur2.'</p>';
                    echo'<p>'.$email_erreur1.'</p>';
                    echo'<p>'.$email_erreur2.'</p>';
                    echo'<p>'.$email_erreur3.'</p>';
                    echo'<p>'.$msn_erreur.'</p>';
                    echo'<p>'.$signature_erreur.'</p>';
                    echo'<p>'.$avatar_erreur.'</p>';
                    echo'<p>'.$avatar_erreur1.'</p>';
                    echo'<p>'.$avatar_erreur2.'</p>';
                    echo'<p>'.$avatar_erreur3.'</p>';
                    echo'<p> Cliquez <a href="?page=vp&action=modifier">ici</a> pour recommencer</p>';
                }
            }
            else
            {
                //On prend les infos du membre
                $query = 'SELECT *
                            FROM membres
                            WHERE membre_id = "'.intval($_SESSION['id']).'"';
                $result = mysql_query($query);
                if ($data = mysql_fetch_assoc($result))
                {
                    if ($data['membre_email_visible'] == 1)
                        $checked = 'checked="checked"';
                    else
                        $checked = '';
                    ?>

                    <form method="post" action="?page=vp&action=modifier" enctype="multipart/form-data" id="profil" >
                    <fieldset>
                        <legend>Identifiants</legend>
                        <p>
                            <label>Pseudo :</label>
                            <?php echo stripslashes(htmlspecialchars($data['membre_pseudo'])); ?>
                        </p>
                        <p>
                            <label for="password">Nouveau mot de Passe :</label>
                            <input type="password" name="password" class="w50" />
                        </p>
                        <p>
                            <label for="confirm">Confirmer le mot de passe :</label>
                            <input type="password" name="confirm" class="w50" />
                        </p>
                    </fieldset>
                    <fieldset>
                        <legend>Contacts</legend>
                        <p>
                            <label for="email">Votre adresse E_Mail :</label>
                            <input type="text" name="email" class="w50" value="<?php echo stripslashes(htmlspecialchars($data['membre_email'])); ?>" />
                        </p>
                        <p>
                            <label for="email">Visibilité de l'adresse Mail :</label>
                            <input type="checkbox" name="email_visible" <?php echo $checked; ?> />
                        </p>
                        <p>
                            <label for="msn">Votre adresse MSN :</label>
                            <input type="text" name="msn" class="w50" value="<?php echo stripslashes(htmlspecialchars($data['membre_msn'])); ?>" />
                        </p>
                        <p>
                            <label for="website">Votre site web :</label>
                            <input type="text" name="website" class="w50" value="<?php echo stripslashes(htmlspecialchars($data['membre_siteweb'])); ?>" />
                        </p>
                    </fieldset>
                    <fieldset>
                        <legend>Informations supplémentaire</legend>
                        <p>
                            <label for="occupation" >Occupation :</label>
                            <input type="text" name="occupation" class="w50" value="<?php echo stripslashes(htmlspecialchars($data['membre_occup'])); ?>" />
                        </p>
                        <p>
                            <label for="localisation" >Localisation :</label>
                            <input type="text" name="localisation" class="w50" value="<?php echo stripslashes(htmlspecialchars($data['membre_localisation'])); ?>" />
                        </p>
                    </fieldset>
                    <fieldset>
                        <legend>Profil sur le forum</legend>
                        <p>
                            <label for="avatar">Changer votre avatar :</label>
                            <input type="file" name="avatar" class="w50" />
                        </p>
                        <p>
                            <label>Supprimer l'avatar :</label>
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
                            <label for="signature" >Signature :</label>
                            <textarea name="signature" ><?php echo stripslashes(htmlspecialchars($data['membre_signature'])); ?></textarea>
                        </p>
                    </fieldset>
                    <p class="center" >
                        <input type="hidden" name="id" value="<?php echo $data['membre_id']; ?>" />
                        <input type="submit" value="Modifier son profil" name="submit" />
                    </p>
                </form>
                    <?php
                }
                else
                    echo'<p>Une erreur s est produite, veuillez réessayer</p>';
            }
        }
        break;

    default;
        echo'<p>Cette action est impossible</p>';
}
?>