<?php
if (!$_SESSION['connected'])
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
            elseif ($data['config_nom'] == 'pass_minsize')
                $pass_minsize = $data['config_valeur'];
        }

        $pseudo_erreur1 = NULL;
        $pseudo_erreur2 = NULL;
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
        $image_erreur = NULL;

        //On récupère les variables
        $i = 0;
        $temps = time();
        $signature = mysql_real_escape_string($_POST['signature']);
        $pseudo = mysql_real_escape_string($_POST['pseudo']);
        $email = mysql_real_escape_string($_POST['email']);
        $msn = mysql_real_escape_string($_POST['msn']);
        $website = mysql_real_escape_string($_POST['website']);
        $occupation = mysql_real_escape_string($_POST['occupation']);
        $localisation = mysql_real_escape_string($_POST['localisation']);
        $pass = mysql_real_escape_string($_POST['password']);

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

        //Vérification du pseudo
        $query = 'SELECT COUNT(*)
                    FROM membres
                    WHERE membre_pseudo = "'.$pseudo.'"';
        $result = mysql_query($query) or die (mysql_error());
        $pseudoExiste = mysql_result($result, 0);
        if($pseudoExiste != 0)
        {
            $pseudo_erreur1 = "Votre pseudo est déjà utilisé par un membre";
            $i++;
        }
        if (strlen($pseudo) < $pseudo_minsize || strlen($pseudo) > $pseudo_maxsize)
        {
            $pseudo_erreur2 = "Votre pseudo est soit trop grand, soit trop petit";
            $i++;
        }

        //Vérification de l'adresse email
        //Il faut que l'adresse email n'ait jamais été utilisée
        if (!empty($email))
        {
            $query = 'SELECT COUNT(*)
                        FROM membres
                        WHERE membre_email = "'.$email.'"';
            $result = mysql_query($query) or die (mysql_error());
            $mailExiste = mysql_result($result, 0);

            if ($mailExiste != 0)
            {
                $email_erreur1 = "Votre adresse email est déjà utilisée par un membre";
                $i++;
            }
            //On vérifie la forme maintenant
            if (!preg_match("#^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]{2,}\.[a-z]{2,4}$#", $email) || empty($email))
            {
                $email_erreur2 = "Votre adresse E-Mail n'a pas un format valide";
                $i++;
            }
        }
        else
        {
            $email_erreur3 = "Votre adresse email est vide";
            $i++;
        }

        //Vérification de l'adresse MSN
        if (!preg_match("#^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]{2,}\.[a-z]{2,4}$#", $msn) && !empty($msn))
        {
            $msn_erreur = "Votre adresse MSN n'a pas un format valide";
            $i++;
        }
        //Vérification de la signature
        if (strlen($signature) > $sign_maxl)
        {
            $signature_erreur = "Votre signature est trop longue";
            $i++;
        }

        //Vérification de l'avatar :
        if (!empty($_FILES['avatar']['size']))
        {
            $extensions_valides = array('.jpg', '.jpeg', '.gif', '.png');

            if ($_FILES['avatar']['error'] > 0)
            {
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
                $avatar_erreur2 = "Image trop large ou trop grande :(".$image_sizes[0]."x".$image_sizes[1]." contre ".$avatar_maxl."x".$avatar_maxh.">)";
            }

            $extension_upload = strtolower(strrchr($_FILES['avatar']['name'], '.'));
            if (!in_array($extension_upload,$extensions_valides) )
            {
                $i++;
                $avatar_erreur3 = "Extension de l'avatar incorrecte";
            }
        }

        if(isset($_POST['verif_code']) AND !empty($_POST['verif_code'])) // Le champ du code de confirmation a été remplis
        {
            if($_POST['verif_code']!=$_SESSION['aleat_nbr']) {
                $i++;
                $image_erreur = 'Votre code de confirmation n\'est pas bon ! Merci de réessayer.<br /><a href="#" onclick="history.go(-1);">Retour</a>';
            }
        }
        else
        {
            $i++;
            $image_erreur = 'Vous devez remplir le champ du code de confirmation !';
        }

        if ($i == 0) // Si i est vide, il n'y a pas d'erreur
        {
            echo'<h1>Inscription terminée</h1>';
            //echo'<p>Bienvenue '.stripslashes(htmlspecialchars($_POST['pseudo'])).' vous êtes maintenant inscrit sur le forum</p>';
            echo'<p>Merci. Vous aller recevoir un mail contenant un lien pour valider votre inscription</p>';
            echo'<p>Cliquez <a href="index.php">ici</a> pour revenir à la page d accueil</p>';

            if (!empty($_FILES['avatar']['size']))
            {
                //On déplace l'avatar
                $nomavatar = str_replace(' ','',time()).''.$extension_upload;
                $path = "./images/avatars/".$nomavatar;
                move_uploaded_file($_FILES['avatar']['tmp_name'],$path);
            }
            else
            {
                $nomavatar = '';
            }

            if (isset($_POST['email_visible']))
                $email_visible = 1;
            else
                $email_visible = 0;

			//Code
            $row = array(1);
            while ($row[0] == 1) {
                $code = mt_rand();
                $query = 'SELECT count(membre_id)
                            FROM membres
                            WHERE membre_code = "'.$code.'"';
                $res = mysql_query($query) or die (mysql_error());
                $row = mysql_fetch_array($res);
            }
			
            //On balance le tout dans notre table
            $query = 'INSERT INTO membres (membre_pseudo,
                                            membre_mdp,
                                            membre_email,
                                            membre_msn,
                                            membre_siteweb,
                                            membre_avatar,
                                            membre_signature,
                                            membre_occup,
                                            membre_localisation,
                                            membre_inscrit,
                                            membre_derniere_visite,
                                            membre_email_visible,
											membre_code)
                                    VALUES ("'.$pseudo.'",
                                            "'.$pass.'",
                                            "'.$email.'",
                                            "'.$msn.'",
                                            "'.$website.'",
                                            "'.$nomavatar.'",
                                            "'.$signature.'",
                                            "'.$occupation.'",
                                            "'.$localisation.'",
                                            "'.$temps.'",
                                            "'.$temps.'",
                                            "'.$email_visible.'",
											"'.$code.'")';
            $result = mysql_query($query) or die(mysql_error());
			
            //Message
            $message = "Voici le lien permettant de valider votre inscription :";
            $message .= "creaftersawuikaz.free.fr/index.php?page=vi&code=".$code;
            //Titre
            $titre = "Inscription sur le forum de la guilde dofusienne Creafters Awuikaz !";

            mail($_POST['email'], $titre, $message);
        }
        else
        {
            echo'<h1>Inscription interrompue</h1>';
            echo'<p>'.$i.' erreur(s) se sont produites pendant l\'incription :</p>';
            echo'<p>'.$pseudo_erreur1.'</p>';
            echo'<p>'.$pseudo_erreur2.'</p>';
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
            echo'<p>'.$image_erreur.'</p>';

            echo'<p>Cliquez <a href="?page=register">ici</a> pour recommencer</p>';
        }
    }
    else
    {
        ?>

            <h1>Inscription 1/2</h1>
            <form method="post" action="?page=register" enctype="multipart/form-data">
                <fieldset>
                    <legend>Identifiants</legend>
                    <p>
                        <label for="pseudo">* Pseudo :</label>
                        <input name="pseudo" type="text" /> (le pseudo doit contenir entre 3 et 15 caractères)
                    </p>
                    <p>
                        <label for="password">* Mot de Passe :</label>
                        <input type="password" name="password" />
                    </p>
                    <p>
                        <label for="confirm">* Confirmer le mot de passe :</label>
                        <input type="password" name="confirm" />
                    </p>
                    <p><img src="inscription/verif_code_gen.php" alt="Code de vérification" /></p>
                    <p>
                        <label>* Merci de retaper le code de l'image ci-dessus : </label>
                        <input type="text" name="verif_code" />
                    </p>
                </fieldset>
                <fieldset>
                    <legend>Contacts</legend>
                    <p>
                        <label for="email">* Votre adresse Mail :</label>
                        <input type="text" name="email" />
                    </p>
                    <p>
                        <label for="email">Visibilité de l'adresse Mail :</label>
                        <input type="checkbox" name="email_visible" checked="checked" />
                    </p>
                    <p>
                        <label for="msn">Votre adresse MSN :</label>
                        <input type="text" name="msn" />
                    </p>
                    <p>
                        <label for="website">Votre site web :</label>
                        <input type="text" name="website" />
                    </p>
                </fieldset>
                <fieldset>
                    <legend>Informations supplémentaires</legend>
                    <p>
                        <label for="occupation">Occupation :</label>
                        <input type="text" name="occupation" />
                    </p>
                    <p>
                        <label for="localisation">Localisation :</label>
                        <input type="text" name="localisation" />
                    </p>
                </fieldset>
                <fieldset>
                    <legend>Profil sur le forum</legend>
                    <p>
                        <label for="avatar">Choisissez votre avatar :</label>
                        <input type="file" name="avatar" />
                    </p>
                    <p>
                        <label for="signature">Signature :</label>
                        <textarea cols="40" rows="4" name="signature"></textarea>
                    </p>
                </fieldset>
                <p>Les champs précédés d un * sont obligatoires</p>
                <p>
                    <input type="submit" value="S'inscrire" name="submit" />
                </p>
            </form>
    <?php
    }
}
?>