<?php
$action = ((isset($_GET['action']))? stripslashes(htmlspecialchars($_GET['action'])): '');

switch($action)
{
    case "repondre": //Premier cas on souhaite répondre
        $topic = ((isset($_GET['t']))? intval($_GET['t']): 0);
        if (isset($_POST['submit']))
        {
            $query = 'SELECT topic_locked,
                            topic_genre,
                            auth_post,
                            auth_annonce
                                FROM topic
                                LEFT JOIN forum ON forum.forum_id = topic.forum_id
                                WHERE topic_id = "'.$topic.'"';
            $result = mysqli_query($mysqli, $query)or die(mysqli_error());
            if (mysqli_num_rows($result) != 0)
            {
                $data = mysqli_fetch_assoc($result);
                $message = mysqli_real_escape_string($_POST['message']);
                $temps = time();
                if ((verif_auth($data['auth_post']) && $data['topic_genre'] != 'Annonce' && $data['topic_locked'] == 0) || (verif_auth($data['auth_annonce']) && $data['topic_genre']=='PostIt'))
                {
                    if (!empty($message))
                    {
                        //On récupère l'id du forum
                        $query = 'SELECT forum_id,
                                        topic_post
                                            FROM topic
                                            WHERE topic_id = "'.$topic.'"';
                        $result = mysqli_query($mysqli, $query);
                        $data = mysqli_fetch_assoc($result) or die ("Une erreur semble être survenue lors de l'envoi du message");
                        $forum = $data['forum_id'];

                        //Puis on entre le message
                        $query = 'INSERT INTO post (post_id,
                                                    post_createur,
                                                    post_texte,
                                                    post_time,
                                                    topic_id,
                                                    post_forum_id)
                                            VALUES("",
                                                    "'.intval($_SESSION['id']).'",
                                                    "'.$message.'",
                                                    "'.$temps.'",
                                                    "'.$topic.'",
                                                    "'.$forum.'")';
                        $result = mysqli_query($mysqli, $query) or die ("Une erreur semble avoir survenu lors de l'envoi du message");

                        $nouveaupost = mysqli_insert_id();
                        //On change un peu la table topic
                        $query = 'UPDATE topic
                                    SET topic_post = topic_post + 1,
                                        topic_last_post = "'.$nouveaupost.'"
                                    WHERE topic_id ="'.$topic.'"';
                        $result = mysqli_query($mysqli, $query) or die ("Une erreur semble avoir survenu lors de l'envoi du message");

                        //Puis même combat sur les 2 autres tables
                        $query = 'UPDATE forum
                                    SET forum_post = forum_post + 1 ,
                                        forum_last_post_id = "'.$nouveaupost.'"
                                    WHERE forum_id = "'.$forum.'"';
                        $result = mysqli_query($mysqli, $query) or die ("Une erreur semble avoir survenu lors de l'envoi du message");

                        $query = 'UPDATE membres
                                    SET membre_post = membre_post + 1
                                    WHERE membre_id = "'.intval($_SESSION['id']).'"';
                        $result = mysqli_query($mysqli, $query) or die ("Une erreur semble avoir survenu lors de l'envoi du message");

                        //Et un petit message
                        $query = 'SELECT *
                                    FROM config';
                        $config = mysqli_query($mysqli, $query) or die(mysqli_error());
                        while ($dataConfig = mysqli_fetch_assoc($config))
                        {
                            if ($dataConfig['config_nom'] == 'post_par_page')
                                $messageParPage = $dataConfig['config_valeur'];
                        }
                        $nbr_post = $data['topic_post'] +1;
                        $page = ceil($nbr_post / $messageParPage);

                        //On update la table forum_topic_view
                        $query = 'UPDATE topic_view
                                    SET tv_post_id = "'.$nouveaupost.'",
                                    tv_poste = "1"
                                    WHERE tv_id = "'.intval($_SESSION['id']).'"
                                    AND tv_topic_id = "'.$topic.'"';
                        $result = mysqli_query($mysqli, $query);

                        echo'<p>Votre message a bien été ajouté!<br/><br/>';
                        echo 'Cliquez <a href="index.php">ici</a> pour revenir à l index du forum<br/>';
                        echo 'Cliquez <a href="?page=vt&t='.$topic.'&index='.$page.'">ici</a> pour le voir</p>';
                    }
                    else
                    {
                        echo'<p>Votre message est vide, cliquez <a href="?page=vt&action=repondre&t='.$topic.'">ici</a> pour recommencer</p>';
                    }

                }
                elseif ($data['topic_locked'] != 0)
                {
                    echo '<p>Le topic est verrouillé!</p>';
                    echo '<p>Cliquez <a href="index.php">ici</a> pour revenir à l index du forum</p>';
                    echo '<p>Cliquez <a href="?page=vt&t='.$topic.'">ici</a> pour retourner au topic</p>';
                }
                else
                {
                    echo '<p>Vous ne pouvez pas répondre à ce topic</p>';
                }
            }
            else
            {
                echo '<p>Erreur! Le topic auquel vous essayez de répondre n\'existe pas</p>';
            }
        }
        break;

    case "edit": //Si on veut éditer le post
        $post = ((isset($_GET['p']))? intval($_GET['p']): 0);
        $topic = ((isset($_GET['t']))? intval($_GET['t']): 0);

        if (isset($_POST['submit']))
        {
            $query = 'SELECT post_createur,
                            post_texte,
                            post_time,
                            post.topic_id,
                            auth_modo,
                            topic_titre,
                            topic_desc,
                            topic_post
                                FROM post
                                LEFT JOIN forum ON post.post_forum_id = forum.forum_id
                                LEFT JOIN topic ON topic.topic_id = post.topic_id
                                WHERE post_id="'.$post.'"';
            $result = mysqli_query($mysqli, $query) or die (mysqli_error());

            if (mysqli_num_rows($result) != 0)
            {
                $data = mysqli_fetch_assoc($result);
                $message = mysqli_real_escape_string($_POST['message']);

                if (verif_auth($data['auth_modo']) || $data['post_createur'] == $_SESSION['id'])
                {
                    if (!empty($message))
                    {
                        $topic = $data['topic_id'];

                        // si le titre est modifié
                        if (isset($_POST['titre']))
                        {
                            $titre = mysqli_real_escape_string($_POST['titre']);
                            $description = mysqli_real_escape_string($_POST['description']);
                            
                            if (!empty($titre))
                            {
                                if ($data['topic_titre'] != $titre || $data['topic_desc'] != $description)
                                {
                                    $query = 'UPDATE topic
                                                SET topic_titre = "'.$titre.'",
                                                    topic_desc = "'.$description.'"
                                                WHERE topic_id = "'.$topic.'"';
                                    $result = mysqli_query($mysqli, $query) or die (mysqli_error());
                                }
                            }
                            else
                            {
                                echo'<p>Votre titre est vide, modification du titre impossible</p>';
                            }
                        }

                        // si les options de sondage sont modifiés
                        if (isset($_POST['option']))
                        {
                            $count = 0;
                            foreach ($_POST['option'] as $option)
                            {
                                if (!empty($option))
                                    $count++;
                            }

                            if ($count > 1)
                            {
                                $query = 'SELECT option_id,
                                                option_texte
                                                    FROM sondage_option
                                                    WHERE option_post_id = "'.$post.'"';
                                $result = mysqli_query($mysqli, $query) or die (mysqli_error());

                                for ($i=0; $i<NB_OPTIONS_SONDAGE; $i++)
                                {
                                    $data2 = mysqli_fetch_array($result);
                                    if (!empty($data2) && $i <= mysqli_num_rows($result))
                                    {
                                        if ($_POST['option'][$i] != '') // UPDATE
                                        {
                                            $query = 'UPDATE sondage_option
                                                        SET option_texte = "'.mysqli_real_escape_string($_POST['option'][$i]).'"
                                                        WHERE option_id = "'.$data2['option_id'].'"';
                                            mysqli_query($mysqli, $query) or die (mysqli_error());
                                        }
                                        else // DELETE
                                        {
                                            $query = 'DELETE FROM sondage_option
                                                        WHERE option_id = "'.$data2['option_id'].'"';
                                            mysqli_query($mysqli, $query) or die (mysqli_error());

                                            $query = 'DELETE FROM reponse_sondage
                                                        WHERE sondage_option_id = "'.$data2['option_id'].'"';
                                            mysqli_query($mysqli, $query) or die (mysqli_error());
                                        }
                                    }
                                    else // INSERT
                                    {
                                        $query = 'INSERT INTO sondage_option (option_id,
                                                                            option_post_id,
                                                                            option_texte)
                                                                        VALUES("",
                                                                            "'.$post.'",
                                                                            "'.mysqli_real_escape_string($_POST['option'][$i]).'")';
                                        mysqli_query($mysqli, $query) or die (mysqli_error());
                                    }
                                }
                            }
                            else
                            {
                                echo'<p>Il n\'y a pas au options 2 options, modification des options impossible</p>';
                            }
                        }

                        $query = 'UPDATE post
                                    SET post_texte = "'.$message.'"
                                    WHERE post_id = "'.$post.'"';
                        $result = mysqli_query($mysqli, $query) or die (mysqli_error());

                        $query = 'SELECT *
                                FROM config';
                        $config = mysqli_query($mysqli, $query) or die(mysqli_error());
                        while ($dataConfig = mysqli_fetch_assoc($config))
                        {
                            if ($dataConfig['config_nom'] == 'post_par_page')
                                $messageParPage = $dataConfig['config_valeur'];
                        }
                        $nbr_post = $data['topic_post'] +1;
                        $page = ceil($nbr_post / $messageParPage);

                        echo'<p>Votre message a bien été édité!<br/><br/>';
                        echo 'Cliquez <a href="index.php">ici</a> pour revenir à l index du forum<br/>';
                        echo 'Cliquez <a href="?page=vt&t='.$topic.'&index='.$page.'">ici</a> pour le voir</p>';
                    }
                    else
                    {
                        echo'<p>Votre message est vide, modification impossible</p>';
                    }
                }
                else
                {
                    echo '<p>Vous ne pouvez pas modifier ce post</p>';
                }
            }
            else
            {
                echo '<p>Erreur! Le post que vous essayer d\'éditer n\'existe pas</p>';
            }
        }
        else
        {
            $query = 'SELECT post_createur,
                            post_texte,
                            post.topic_id,
                            topic_titre,
                            topic_desc,
                            topic.forum_id,
                            topic_genre,
                            topic_first_post,
                            forum_name,
                            auth_view,
                            auth_post,
                            auth_topic,
                            auth_annonce,
                            auth_modo
                                FROM post
                                LEFT JOIN topic ON topic.topic_id = post.topic_id
                                LEFT JOIN forum ON forum.forum_id = topic.forum_id
                                WHERE post.post_id ="'.$post.'"';
            $result = mysqli_query($mysqli, $query) or die(mysqli_error());

            if (mysqli_num_rows($result) != 0)
            {
                $data = mysqli_fetch_array($result);
                if (verif_auth($data['auth_modo']) || $data['post_createur'] == $_SESSION['id'])
                {
                    ?>

                        <form method="post" action="?page=vt&action=edit&t=<?php echo $data['topic_id'] ?>&p=<?php echo $post ?>" name="formulaire" id="text_editor" >

                    <?php
                    if ($data['topic_first_post'] == $post) //Si le message est le premier
                    {
                        echo '<p><label for="titre" >Titre : </label><input type="text" name="titre" class="w100" value="'.$data['topic_titre'].'" /></p>';
                        echo '<p><label for="description" >Description : </label><input type="text" name="description" class="w100" value="'.$data['topic_desc'].'" /></p>';

                        if ($data['topic_genre'] == 'Sondage') //Si le topic est un sondage
                        {
                            echo '<div id="sondage_options" >';
                            $query = 'SELECT option_id,
                                            option_texte
                                                FROM sondage_option
                                                WHERE option_post_id = "'.$post.'"';
                            $result2 = mysqli_query($mysqli, $query)or die(mysqli_error());

                            for ($i=0; $i<NB_OPTIONS_SONDAGE; $i++) {
                                $data2 = mysqli_fetch_array($result2);
                                $value = ((!empty($data2) && $i <= mysqli_num_rows($result2))? $data2['option_texte']: '');
                                echo '<p><label>option '.($i+1).' : </label><input type="text" name="option['.$i.']" class="w50" value="'.$value.'" /></p>';
                            }

                            echo '</div>';
                        }
                    }
                    ?>

                            <div id="bb_options" >
                                <div class="left" >
                                    <ul>
                                        <li><input type="button" name="gras" value="Gras" onClick="bbcode('[g]', '[/g]');return(false)" /></li>
                                        <li><input type="button" name="image" value="image" onClick="bbcode('[img]', '[/img]');return(false)" /></li>
                                    </ul>
                                    <ul>
                                        <li><input type="button" name="souligné" value="Souligné" onClick="bbcode('[s]', '[/s]');return(false)" /></li>
                                        <li><input type="button" name="lien" value="Lien" onClick="bbcode('[url=]', '[/url]');return(false)" /></li>
                                    </ul>
                                    <ul>
                                        <li><input type="button" name="italic" value="Italic" onClick="bbcode('[i]', '[/i]');return(false)" /></li>
                                        <li><input type="button" name="citation" value="citation" onClick="bbcode('[quote auteur=]', '[/quote]');return(false)" /></li>
                                    </ul>
                                </div>
                                <div class="right" >
                                    <ul>
                                        <li><img src="./images/smileys/smile.gif" title=":)" alt=":)" onClick="smilies(':)');return(false)" /></li>
                                        <li><img src="./images/smileys/^^.gif" title="^^" alt="^^" onClick="smilies('^^');return(false)" /></li>
                                        <li><img src="./images/smileys/biggrin.gif" title=":D" alt=":D" onClick="smilies(':D');return(false)" /></li>
                                        <li><img src="./images/smileys/mdr.gif" title="XD" alt="XD" onClick="smilies('XD');return(false)" /></li>
                                        <li><img src="./images/smileys/he.gif" title="he" alt="he" onClick="smilies(':he:');return(false)" /></li>
                                        <li><img src="./images/smileys/intello.gif" title="intello" alt="intello" onClick="smilies(':intello:');return(false)" /></li>
                                        <li><img src="./images/smileys/wink2.gif" title=";)" alt=";)" onClick="smilies(';)');return(false)" /></li>
                                        <li><img src="./images/smileys/tongue.gif" title=":p" alt=":p" onClick="smilies(':p');return(false)" /></li>
                                        <li><img src="./images/smileys/winktongue.gif" title=";p" alt=";p" onClick="smilies(';p');return(false)" /></li>
                                        <li><img src="./images/smileys/oh.gif" title="O_o" alt="O_o" onClick="smilies('O_o');return(false)" /></li>
                                        <li><img src="./images/smileys/eek.gif" title=":eek:" alt=":eek:" onClick="smilies(':eek:');return(false)" /></li>
                                        <li><img src="./images/smileys/shocked.gif" title=":o" alt=":o" onClick="smilies(':o');return(false)" /></li>
                                    </ul>
                                    <ul>
                                        <li><img src="./images/smileys/ouch.gif" title=":s" alt=":s" onClick="smilies(':s');return(false)" /></li>
                                        <li><img src="./images/smileys/erf.gif" title=":|" alt=":|" onClick="smilies(':|');return(false)" /></li>
                                        <li><img src="./images/smileys/frown.gif" title=":(" alt=":(" onClick="smilies(':(');return(false)" /></li>
                                        <li><img src="./images/smileys/aww.gif" title="aww" alt="aww" onClick="smilies(':aww:');return(false)" /></li>
                                        <li><img src="./images/smileys/bad.gif" title="bad" alt="bad" onClick="smilies(':bad:');return(false)" /></li>
                                        <li><img src="./images/smileys/clown.gif" title="clowms" alt="clowm" onClick="smilies(':clowm:');return(false)" /></li>
                                        <li><img src="./images/smileys/kiss.gif" title="kiss" alt="kiss" onClick="smilies(':kiss:');return(false)" /></li>
                                        <li><img src="./images/smileys/mad.gif" title="è_é" alt="è_é" onClick="smilies('è_é');return(false)" /></li>
                                        <li><img src="./images/smileys/money.gif" title="$_$" alt="$_$" onClick="smilies('$_$');return(false)" /></li>
                                        <li><img src="./images/smileys/glasses.gif" title="B)" alt="B)" onClick="smilies('B)');return(false)" /></li>
                                        <li><img src="./images/smileys/zzz.gif" title="zzz" alt="zzz" onClick="smilies(':zzz:');return(false)" /></li>
                                        <li><img src="./images/smileys/cry.gif" title=":'(" alt=":'(" onClick="smilies(':\'(');return(false)" /></li>
                                    </ul>
                                    <ul>
                                        <li><img src="./images/smileys/love.gif" title="love" alt="love" onClick="smilies(':love:');return(false)" /></li>
                                        <li><img src="./images/smileys/happy.gif" title="happy" alt="happy" onClick="smilies(':happy:');return(false)" /></li>
                                        <li><img src="./images/smileys/arf.gif" title=":/" alt=":/" onClick="smilies(':/ ');return(false)" /></li>
                                        <li><img src="./images/smileys/jap.gif" title="jap" alt="jap" onClick="smilies(':jap:');return(false)" /></li>
                                        <li><img src="./images/smileys/666.gif" title="666" alt="666" onClick="smilies(':666:');return(false)" /></li>
                                        <li><img src="./images/smileys/note.gif" title="note" alt="note" onClick="smilies(':note:');return(false)" /></li>
                                        <li><img src="./images/smileys/star.gif" title="star" alt="star" onClick="smilies(':star:');return(false)" /></li>
                                        <li><img src="./images/smileys/present.gif" title="kado" alt="kado" onClick="smilies(':kado:');return(false)" /></li>
                                        <li><img src="./images/smileys/heart.gif" title="<3" alt="<3" onClick="smilies('<3');return(false)" /></li>
                                        <li><img src="./images/smileys/unlove.gif" title="</3" alt="</3" onClick="smilies('</3');return(false)" /></li>
                                        <li><img src="./images/smileys/idea.gif" title="idee" alt="idee" onClick="smilies(':idee:');return(false)" /></li>
                                        <li><img src="./images/smileys/user.gif" title="user" alt="user" onClick="smilies(':user:');return(false)" /></li>
                                    </ul>
                                </div>
                            </div>
                            <div id="message_area" >
                                <p><textarea name="message"><?php echo stripslashes(htmlspecialchars($data['post_texte'])); ?></textarea></p>
                            </div>

                     <?php
                    if (verif_auth($data['auth_annonce']))
                    {
                        echo '<div class="options_modo right" ><ul>';
                        echo '<li><img src="./images/smileys/arrow.gif" title="arrow" alt="arrow" onClick="smilies(\'->\');return(false)" /></li>';
                        echo '<li><img src="./images/smileys/info.gif" title="info" alt="info" onClick="smilies(\':i:\');return(false)" /></li>';
                        echo '<li><img src="./images/smileys/warn.gif" title="warn" alt="warn" onClick="smilies(\':!:\');return(false)" /></li>';
                        echo '</ul></div>';
                        echo '<div class="options_modo left" >';
                        echo '<input type="button" name="color" value="Color" onClick="bbcode(\'[color=]\', \'[/color]\');return(false)" />';
                        echo '</div>';
                    }
                    ?>

                            <p class="center" >
                                <input type="submit" name="submit" value="Envoyer" />
                                <input type="reset" name = "Effacer" value = "Effacer"/>
                            </p>
                        </form>

                    <?php
                }
                else
                {
                    echo '<p>Vous ne pouvez pas modifier ce post</p>';
                }
            }
            else
            {
                echo '<p>Erreur! Le post que vous essayer d\'éditer n\'existe pas</p>';
            }
        }
        break;

    case "delete": //Si on veut supprimer le post
        $post = ((isset($_GET['p']))? intval($_GET['p']): 0);
        $topic = ((isset($_GET['t']))? intval($_GET['t']): 0);

        echo'<h1>Suppression d\'un post</h1>';
        $query = 'SELECT post_createur,
                        auth_modo
                            FROM post
                            LEFT JOIN forum ON post.post_forum_id = forum.forum_id
                            WHERE post_id='.$post.'';
        $result = mysqli_query($mysqli, $query) or die(mysqli_error());

        if (mysqli_num_rows($result) != 0)
        {
            $data = mysqli_fetch_assoc($result);
            if (verif_auth($data['auth_modo']) || $data['post_createur'] == $_SESSION['id'])
            {
                echo'<p>Êtes vous certains de vouloir supprimer ce post ?</p>';
                echo'<p><a href="?page=vt&action=delete_message&t='.$topic.'&p='.$post.'">Oui</a> ou <a href="?page=vt&t='.$topic.'">Non</a></p>';
            }
            else
            {
                echo '<p>Vous ne pouvez pas supprimer ce post</p>';
            }
        }
        else
        {
            echo '<p>Erreur! Le post que vous essayer de supprimer n\'existe pas</p>';
        }
        break;

    case "delete_message": //Si on veut supprimer le post
        $post = ((isset($_GET['p']))? intval($_GET['p']): 0);
        $topic = ((isset($_GET['t']))? intval($_GET['t']): 0);
        $query = 'SELECT post_createur,
                        post_texte,
                        forum_id,
                        topic_id,
                        auth_modo
                            FROM post
                            LEFT JOIN forum ON post.post_forum_id = forum.forum_id
                            WHERE post_id="'.$post.'"';
        $result = mysqli_query($mysqli, $query) or die (mysqli_error());

        if (mysqli_num_rows($result) != 0)
        {
            $data = mysqli_fetch_assoc($result);
            $topic = $data['topic_id'];
            $forum = $data['forum_id'];

            //Ensuite on vérifie que le membre a le droit d'être ici
            //(soit le créateur soit un modo/admin)
            if (verif_auth($data['auth_modo']) || $data['post_createur'] == $_SESSION['id'])
            {
                //Ici on vérifie plusieurs choses :
                //est-ce un premier post ? Dernier post ou post classique ?
                $query = 'SELECT COUNT(*) AS first_post
                            FROM topic
                            WHERE topic_first_post = "'.$post.'"';
                $requete_first_post = mysqli_query($mysqli, $query) or die (mysqli_error());

                $query = 'SELECT COUNT(*) AS last_post
                            FROM topic
                            WHERE topic_last_post = "'.$post.'"';
                $requete_last_post = mysqli_query($mysqli, $query) or die (mysqli_error());

                $first_post = mysqli_fetch_assoc($requete_first_post);
                $last_post = mysqli_fetch_assoc($requete_last_post);


                //On distingue maintenant les cas
                if ($first_post['first_post'] != 0) //Si le message est le premier
                {
                    //Les autorisations ont changé !
                    if (verif_auth($data['auth_modo']))
                    {
                        echo '<p>Vous avez choisi de supprimer un post. Cependant ce post est le premier du topic. Voulez vous supprimer le topic ? <br/>';
                        echo '<a href="?page=vt&action=deletetopic&t='.$topic.'">oui</a> - <a href="?page=vt&t='.$topic.'">non</a></p>';
                    }
                    else
                    {
                        echo '<p>Vous ne pouvez pas supprimer ce post car c\'est le premier du topic.<br/>';
                        echo 'Demandez à un modérateur !</p>';
                    }
                }
                elseif ($last_post['last_post'] != 0) //Si le message est le dernier
                {
                    //On supprime le post
                    $query = 'DELETE FROM post
                                WHERE post_id = "'.$post.'"';
                    $result = mysqli_query($mysqli, $query) or die (mysqli_error());

                    //On modifie la valeur de topic_last_post pour cela on
                    //récupère l'id du plus récent  message de ce topic
                    $query = 'SELECT post_id
                                FROM post
                                WHERE topic_id = "'.$topic.'"
                                ORDER BY post_id DESC
                                LIMIT 0,1';
                    $result = mysqli_query($mysqli, $query);
                    $data2 = mysqli_fetch_assoc($result) or die (mysqli_error());

                    //On fait de même pour forum_last_post_id
                    $query = 'SELECT post_id
                                FROM post
                                WHERE post_forum_id = "'.$forum.'"
                                ORDER BY post_id DESC
                                LIMIT 0,1';
                    $result = mysqli_query($mysqli, $query);
                    $data3 = mysqli_fetch_assoc($result) or die (mysqli_error());

                    //On met à jour la valeur de topic_last_post
                    $query = 'UPDATE topic
                                SET topic_last_post = "'.$data2['post_id'].'"
                                WHERE topic_last_post = "'.$post.'"';
                    $result = mysqli_query($mysqli, $query) or die (mysqli_error());

                    //On enlève 1 au nombre de messages du forum et on met à
                    //jour forum_last_post
                    $query = 'UPDATE forum
                                SET forum_post = forum_post - 1,
                                    forum_last_post_id = "'.$data3['post_id'].'"
                                WHERE forum_id = "'.$forum.'"';
                    $result = mysqli_query($mysqli, $query) or die (mysqli_error());

                    //On enlève 1 au nombre de messages du topic
                    $query = 'UPDATE topic
                                SET  topic_post = topic_post - 1
                                WHERE topic_id = "'.$topic.'"';
                    $result = mysqli_query($mysqli, $query) or die (mysqli_error());

                    //On enlève 1 au nombre de messages du membre
                    $query = 'UPDATE membres
                                SET  membre_post = membre_post - 1
                                WHERE membre_id = "'.$data['post_createur'].'"';
                    $result = mysqli_query($mysqli, $query) or die (mysqli_error());

                    //Enfin le message
                    echo'<p>Le message a bien été supprimé !<br/>';
                    echo 'Cliquez <a href="?page=vt&t='.$topic.'">ici</a> pour retourner au topic<br/>';
                    echo 'Cliquez <a href="index.php">ici</a> pour revenir à l index du forum</p>';
                }
                elseif ($last_post['last_post'] == 0 && $first_post['first_post'] == 0) // Si c'est un post classique
                {
                    //On supprime le post
                    $query = 'DELETE FROM post
                                WHERE post_id = "'.$post.'"';
                    $result = mysqli_query($mysqli, $query) or die (mysqli_error());

                    //On enlève 1 au nombre de messages du forum
                    $query = 'UPDATE forum
                                SET  forum_post = forum_post - 1
                                WHERE forum_id ="'.$forum.'"';
                    $result = mysqli_query($mysqli, $query) or die (mysqli_error());

                    //On enlève 1 au nombre de messages du topic
                    $query = 'UPDATE topic
                                SET  topic_post = topic_post - 1
                                WHERE topic_id = "'.$topic.'"';
                    $result = mysqli_query($mysqli, $query) or die (mysqli_error());

                    //On enlève 1 au nombre de messages du membre
                    $query = 'UPDATE membres
                                SET  membre_post = membre_post - 1
                                WHERE membre_id = "'.$data['post_createur'].'"';
                    $result = mysqli_query($mysqli, $query) or die (mysqli_error());

                    //Enfin le message
                    echo'<p>Le message a bien été supprimé !<br/>';
                    echo 'Cliquez <a href="?page=vt&t='.$topic.'">ici</a> pour retourner au topic<br/>';
                    echo 'Cliquez <a href="index.php">ici</a> pour revenir à l index du forum</p>';
                }
            }
            else
            {
                echo '<p>Vous ne pouvez pas supprimer ce post</p>';
            }
        }
        else
        {
            echo '<p>Erreur! Le post que vous essayer de supprimer n\'existe pas</p>';
        }
        break;

    case "delete_topic":
        $topic = ((isset($_GET['t']))? intval($_GET['t']): 0);
        $query = 'SELECT topic.forum_id,
                        auth_modo
                            FROM topic
                            LEFT JOIN forum ON topic.forum_id = forum.forum_id
                            WHERE topic_id="'.$topic.'"';
        $result = mysqli_query($mysqli, $query) or die (mysqli_error());

        if (mysqli_num_rows($result) != 0)
        {
            $data = mysqli_fetch_assoc($result);
            $forum = $data['forum_id'];

            //Ensuite on vérifie que le membre a le droit d'être ici
            //c'est-à-dire si c'est un modo / admin
            if (verif_auth($data['auth_modo']))
            {
                //On compte le nombre de post du topic
                $query = 'SELECT COUNT(*) AS nombre_post
                            FROM post
                            WHERE topic_id = "'.$topic.'"';
                $requete_count_post = mysqli_query($mysqli, $query) or die (mysqli_error());
                $data_nombrepost = mysqli_fetch_assoc($requete_count_post);
                $nombrepost = $data_nombrepost['nombre_post'];

                //On supprime le topic
                $query = 'DELETE FROM topic
                            WHERE topic_id = "'.$topic.'"';
                mysqli_query($mysqli, $query);

                //On enlève le nombre de post posté par chaque membre dans le topic
                $query = 'SELECT post_createur,
                                COUNT(*) AS nombre_mess
                                    FROM post
                                    WHERE topic_id = "'.$topic.'"
                                    GROUP BY post_createur';
                $requete_postparmembre = mysqli_query($mysqli, $query) or die (mysqli_error());

                while($data_postparmembre = mysqli_fetch_assoc($requete_postparmembre))
                {
                    $query = 'UPDATE membres
                                SET membre_post = membre_post - '.$data_postparmembre['nombre_mess'].'
                                WHERE membre_id = "'.$data_postparmembre['post_createur'].'"';
                    $result = mysqli_query($mysqli, $query) or die (mysqli_error());
                }

                //Et on supprime les posts !
                $query = 'DELETE FROM post
                            WHERE topic_id = "'.$topic.'"';
                $result = mysqli_query($mysqli, $query) or die (mysqli_error());

                //Dernière chose, on récupère le dernier post du forum
                $query = 'SELECT post_id
                            FROM post
                            WHERE post_forum_id = '.$forum.'
                            ORDER BY post_id DESC
                            LIMIT 0,1';
                $requete_forum = mysqli_query($mysqli, $query) or die (mysqli_error());
                $data_forum = mysqli_fetch_assoc($requete_forum);

                //Ensuite on modifie certaines valeurs :
                $query = 'UPDATE forum
                            SET forum_topic = forum_topic - 1,
                                forum_post = forum_post - '.$nombrepost.',
                                forum_last_post_id = "'.$data_forum['post_id'].'"
                            WHERE forum_id = "'.$forum.'"';
                $result = mysqli_query($mysqli, $query) or die (mysqli_error());


                //Enfin le message
                echo'<p>Le topic a bien été supprimé !<br/>';
                echo 'Cliquez <a href="index.php">ici</a> pour revenir à l index du forum</p>';
            }
            else
            {
                echo '<p>Vous ne pouvez pas supprimer ce topic</p>';
            }
        }
        else
        {
            echo '<p>Erreur! Le topic que vous essayer de supprimer n\'existe pas</p>';
        }
        break;

    case "lock": //Si on veut verrouiller le topic
        //On récupère la valeur de t
        $topic = ((isset($_GET['t']))? intval($_GET['t']): 0);
        $query = 'SELECT topic.forum_id, auth_modo
                    FROM topic
                    LEFT JOIN forum ON forum.forum_id = topic.forum_id
                    WHERE topic_id = "'.$topic.'"';
        $result = mysqli_query($mysqli, $query) or die (mysqli_error());

        if (mysqli_num_rows($result) != 0)
        {
            $data = mysqli_fetch_assoc($result);

            //Ensuite on vérifie que le membre a le droit d'être ici
            if (verif_auth($data['auth_modo']))
            {
                //On met à jour la valeur de topic_locked
                $query = 'UPDATE topic
                            SET topic_locked = "1"
                            WHERE topic_id = "'.$topic.'"';
                $result = mysqli_query($mysqli, $query) or die (mysqli_error());

                echo'<p>Le topic a bien été verrouillé ! <br/>';
                echo 'Cliquez <a href="?page=vt&t='.$topic.'">ici</a> pour retourner au topic<br/>';
                echo 'Cliquez <a href="index.php">ici</a> pour revenir à l index du forum</p>';
            }
            else
            {
                echo '<p>Vous ne pouvez pas vérouiller ce topic</p>';
            }
        }
        else
        {
            echo '<p>Erreur! Le topic que vous essayer de vérouiller n\'existe pas</p>';
        }
        break;

    case "unlock": //Si on veut déverrouiller le topic
        //On récupère la valeur de t
        $topic = ((isset($_GET['t']))? intval($_GET['t']): 0);
        $query = 'SELECT topic.forum_id,
                        auth_modo
                            FROM topic
                            LEFT JOIN forum ON forum.forum_id = topic.forum_id
                            WHERE topic_id = '.$topic.'';
        $result = mysqli_query($mysqli, $query) or die (mysqli_error());

        if (mysqli_num_rows($result) != 0)
        {
            $data = mysqli_fetch_assoc($result);

            //Ensuite on vérifie que le membre a le droit d'être ici
            if (verif_auth($data['auth_modo']))
            {
                //On met à jour la valeur de topic_locked
                $query = 'UPDATE topic
                            SET topic_locked = "0"
                            WHERE topic_id = "'.$topic.'"';
                $result = mysqli_query($mysqli, $query) or die (mysqli_error());

                echo'<p>Le topic a bien été déverrouillé !<br/>';
                echo 'Cliquez <a href="?page=vt&t='.$topic.'">ici</a> pour retourner au topic<br/>';
                echo 'Cliquez <a href="index.php">ici</a> pour revenir à l index du forum</p>';
            }
            else
            {
                echo '<p>Vous ne pouvez pas dévérouiller ce topic</p>';
            }
        }
        else
        {
            echo '<p>Erreur! Le topic que vous essayer de dévérouiller n\'existe pas</p>';
        }
        break;

    case "deplacer":
        if (isset($_POST['submit']))
        {
            $topic = ((isset($_GET['t']))? intval($_GET['t']): 0);
            $query = 'SELECT topic.forum_id, auth_modo
                        FROM topic
                        LEFT JOIN forum ON forum.forum_id = topic.forum_id
                        WHERE topic_id = "'.$topic.'"';
            $result = mysqli_query($mysqli, $query) or die (mysqli_error());
            if (mysqli_num_rows($result) != 0)
            {
                $data = mysqli_fetch_assoc($result);

                if (verif_auth($data['auth_modo']))
                {
                    $destination = (int) $_POST['dest'];
                    $origine = (int ) $_POST['from'];

                    //On déplace le topic
                    $query = 'UPDATE topic
                                SET forum_id = "'.$destination.'"
                                WHERE topic_id = "'.$topic.'"';
                    $result = mysqli_query($mysqli, $query) or die ("Un problème est survenu lors du déplacement");

                    //On déplace les posts
                    $query = 'UPDATE post
                                SET post_forum_id = "'.$destination.'"
                                WHERE topic_id = "'.$topic.'"';
                    $result = mysqli_query($mysqli, $query) or die ("Un problème est survenu lors du déplacement");

                    //On s'occupe d'ajouter / enlever les nombres de post / topic aux
                    //forum d'origine et de destination
                    //Pour cela on compte le nombre de post déplacé
                    $query = 'SELECT COUNT(*) AS nombre_post
                                FROM post
                                WHERE topic_id = "'.$topic.'"';
                    $post_number_requete = mysqli_query($mysqli, $query) or die ("Un problème est survenu lors du déplacement");
                    $data_post_number = mysqli_fetch_assoc($post_number_requete);
                    $nombrepost = $data_post_number['nombre_post'];

                    //Il faut également vérifier qu'on a pas déplacé un post qui été
                    //l'ancien premier post du forum (champ forum_last_post_id)
                    $query = 'SELECT post_id
                                FROM post
                                WHERE post_forum_id = "'.$origine.'"
                                ORDER BY post_id DESC
                                LIMIT 0,1';
                    $result = mysqli_query($mysqli, $query) or die ("Un problème est survenu lors du déplacement");
                    $data = mysqli_fetch_assoc($result);

                    //Puis on met à jour le forum d'origine
                    $query = 'UPDATE forum
                                SET forum_post = forum_post - '.$nombrepost.',
                                    forum_topic = forum_topic - 1,
                                    forum_last_post_id = "'.$data['post_id'].'"
                                WHERE forum_id = "'.$origine.'"';
                    $result = mysqli_query($mysqli, $query) or dir (mysqli_error());

                    //Avant de mettre à jour le forum de destination il faut
                    //vérifier la valeur de forum_last_post_id
                    $query = 'SELECT post_id
                                FROM post WHERE post_forum_id = "'.$destination.'"
                                ORDER BY post_id DESC
                                LIMIT 0,1';
                    $result = mysqli_query($mysqli, $query) or die ("Un problème est survenu lors du déplacement");
                    $data = mysqli_fetch_assoc($result);

                    //Et on met à jour enfin !
                    $query = 'UPDATE forum
                                SET forum_post = forum_post + '.$nombrepost.',
                                    forum_topic = forum_topic + 1,
                                    forum_last_post_id = "'.$data['post_id'].'"
                                WHERE forum_id = "'.$destination.'"';
                    $result = mysqli_query($mysqli, $query) or die ("Un problème est survenu lors du déplacement");

                    //C'est gagné ! On affiche le message
                    echo '<p>Le topic a bien été déplacé <br/>';
                    echo 'Cliquez <a href="?page=vt&t='.$topic.'">ici</a> pour revenir au topic<br/>';
                    echo 'Cliquez <a href="index.php">ici</a> pour revenir à l index du forum</p>';
                }
                else
                {
                    echo '<p>Vous ne pouvez pas déplacer ce topic</p>';
                }
            }
            else
            {
                echo '<p>Erreur! Le topic que vous essayer de déplacer n\'existe pas</p>';
            }
        }
        break;

    case 'sondage':
        $topic = ((isset($_GET['t']))? intval($_GET['t']): 0);

        if (isset($_POST['submit']))
        {

            $query = 'SELECT topic_first_post,
                            auth_view
                                FROM topic
                                LEFT JOIN forum ON forum.forum_id = topic.forum_id
                                WHERE topic_id = "'.$topic.'"';
            $result = mysqli_query($mysqli, $query) or die (mysqli_error());

            if (mysqli_num_rows($result) != 0)
            {
                $data = mysqli_fetch_assoc($result);
                if (verif_auth($data['auth_view']))
                {
                    if (isset($_POST['sondage']))
                    {
                        $query = 'INSERT reponse_sondage (sondage_id,
                                                        sondage_post_id,
                                                        sondage_option_id,
                                                        sondage_membre_id)
                                                VALUES ("",
                                                        "'.$data['topic_first_post'].'",
                                                        "'.$_POST['sondage'].'",
                                                        "'.$_SESSION['id'].'")';
                        $result = mysqli_query($mysqli, $query) or die (mysqli_error());
                    }
                    else
                    {
                        echo '<p>Vous n\'avez choisi aucune option</p>';
                    }
                }
                else
                {
                    echo '<p>Vous ne pouvez pas répondre à ce sondage</p>';
                }
            }
            else
            {
                echo '<p>Erreur! Le sondage auquel vous essayer de r´pondre n\'existe pas</p>';
            }
        }
        break;

    case 'cloturer':
        $topic = ((isset($_GET['t']))? intval($_GET['t']): 0);

        $query = 'SELECT post_createur,
                        auth_modo,
                        topic_genre,
                        topic_cloture
                            FROM post
                            LEFT JOIN forum ON post_forum_id = forum_id
                            LEFT JOIN topic ON topic.topic_id = post.topic_id
                            WHERE topic.topic_id = "'.$topic.'"';
        $result = mysqli_query($mysqli, $query) or die (mysqli_error());

        if (mysqli_num_rows($result) != 0)
        {
            $data = mysqli_fetch_assoc($result);
            if ((intval($_SESSION['id']) == $data['post_createur'] && $i != 0) || verif_auth($data['auth_modo']))
            {
                if ($data['topic_genre'] == 'Sondage' && $data['topic_cloture'] == 0)
                {
                    echo'<p>Êtes vous certains de vouloir clôturer ce sondage ?</p>';
                    echo'<p><a href="?page=vt&action=cloturer_sondage&t='.$topic.'">Oui</a> ou <a href="?page=vt&t='.$topic.'">Non</a></p>';
                }
            }
            else
            {
                echo '<p>Vous ne pouvez pas clôturer ce sondage</p>';
            }
        }
        else
        {
            echo '<p>Le sondage que vous essayez de clôturer n\'éxiste pas</p>';
        }
        break;

    case 'cloturer_sondage':
        $topic = ((isset($_GET['t']))? intval($_GET['t']): 0);

        $query = 'SELECT post_createur,
                        auth_modo,
                        topic_genre,
                        topic_cloture
                            FROM post
                            LEFT JOIN forum ON post_forum_id = forum_id
                            LEFT JOIN topic ON topic.topic_id = post.topic_id
                            WHERE topic.topic_id = "'.$topic.'"';
        $result = mysqli_query($mysqli, $query) or die (mysqli_error());

        if (mysqli_num_rows($result) != 0)
        {
            $data = mysqli_fetch_assoc($result);
            if ((intval($_SESSION['id']) == $data['post_createur'] && $i != 0) || verif_auth($data['auth_modo']))
            {
                if ($data['topic_genre'] == 'Sondage' && $data['topic_cloture'] == 0)
                {
                    $query = 'UPDATE topic
                                SET topic_cloture = "1"
                                WHERE topic_id = "'.$topic.'"';
                    $result = mysqli_query($mysqli, $query) or die (mysqli_error());

                    echo '<p>Le topic a bien était clôturé.<br/>';
                    echo 'Cliquez <a href="?page=vt&t='.$topic.'">ici</a> pour revenir au topic<br/>';
                    echo 'Cliquez <a href="index.php">ici</a> pour revenir à l index du forum</p>';
                }
            }
            else
            {
                echo '<p>Vous ne pouvez pas clôturer ce sondage</p>';
            }
        }
        else
        {
            echo '<p>Le sondage que vous essayez de clôturer n\'éxiste pas</p>';
        }
        break;

    default:
        $topic = ((isset($_GET['t']))? intval($_GET['t']): 0);

        //A partir d'ici, on va compter le nombre de messages pour n'afficher que les 15 premiers
        $query = 'SELECT topic_titre,
                        topic_post,
                        topic.forum_id,
                        topic_last_post,
                        topic_genre,
                        topic_locked,
                        topic_cloture,
                        forum.forum_id,
                        forum_name,
                        auth_view,
                        auth_topic,
                        auth_post,
                        auth_modo,
                        auth_annonce
                            FROM topic
                            LEFT JOIN forum ON topic.forum_id = forum.forum_id
                            WHERE topic_id = "'.$topic.'"';
        $result = mysqli_query($mysqli, $query) or die (mysqli_error());

        if (mysqli_num_rows($result) != 0)
        {
            $data = mysqli_fetch_assoc($result);

            if ($_SESSION['connected'])
            {
                //Topic déjà consulté ?
                $query = 'SELECT COUNT(*)
                            FROM topic_view
                            WHERE tv_topic_id = "'.$topic.'"
                            AND tv_id = "'.intval($_SESSION['id']).'"';
                $result = mysqli_query($mysqli, $query) or die (mysqli_error());
                $nbr_vu = mysqli_data_seek($result, 0);

                if ($nbr_vu == 0) //Si c'est la première fois on insère une ligne entière
                {
                    $query = 'INSERT INTO topic_view (tv_id,
                                                    tv_topic_id,
                                                    tv_forum_id,
                                                    tv_post_id)
                                            VALUES ("'.intval($_SESSION['id']).'",
                                                    "'.$topic.'",
                                                    "'.$data['forum_id'].'",
                                                    "'.$data['topic_last_post'].'")';
                    $result = mysqli_query($mysqli, $query);
                }
                else //Sinon, on met simplement à jour
                {
                    $query = 'UPDATE topic_view
                                SET tv_post_id = "'.$data['topic_last_post'].'"
                                WHERE tv_topic_id = "'.$topic.'"
                                AND tv_id = "'.intval($_SESSION['id']).'"';
                    $result = mysqli_query($mysqli, $query) or die (mysqli_error());
                }
            }

            if (verif_auth($data['auth_view']))
            {
                $query = 'SELECT *
                            FROM config';
                $config = mysqli_query($mysqli, $query) or die(mysqli_error());
                while ($dataConfig = mysqli_fetch_assoc($config))
                {
                    if ($dataConfig['config_nom'] == 'post_par_page')
                        $messageParPage = $dataConfig['config_valeur'];
                    if ($dataConfig['config_nom'] == 'auth_bbcode_sign')
                        $authBbcodeSign = $dataConfig['config_valeur'];
                }

                if (!isset($_GET['index']))
                    $message=0;
                else
                    $message=$messageParPage*($_GET['index']-1);

                $query = 'SELECT post_id,
                                post_createur,
                                post_texte,
                                post_time,
                                membre_id,
                                membre_pseudo,
                                membre_inscrit,
                                membre_avatar,
                                membre_occup,
                                membre_localisation,
                                membre_post,
                                membre_signature,
                                membre_rang,
                                topic_id
                                    FROM post
                                    LEFT JOIN membres ON membre_id = post_createur
                                    WHERE topic_id ="'.$topic.'"
                                    ORDER BY post_id
                                    LIMIT '.$message.', '.$messageParPage.'';
                $result2 = mysqli_query($mysqli, $query)or die(mysqli_error());

                //On vérifie que la requête a bien retourné des messages
                if (mysqli_num_rows($result2) < 1)
                {
                   echo '<p>Il n y a aucun post sur ce topic, vérifiez l\'url et reessayez</p>';
                }
                else
                {
                    ?>

                            <table>
                                <tr>
                                    <th colspan="2" >
                                    
                    <?php
                    if (verif_auth($data['auth_modo']))
                    {
                        $query = 'SELECT forum_id,
                                        forum_name
                                            FROM forum
                                            WHERE forum_id <> "'.$data['forum_id'].'"';
                        $result3 = mysqli_query($mysqli, $query) or die (mysqli_error());

                        echo '<form method="post" action="?page=vt&action=deplacer&t='.$topic.'" class="right" >';
                        if ($data['topic_locked'] == 1) // Topic verrouillé !
                            echo '<a href="?page=vt&action=unlock&t='.$topic.'"><img src="./images/unlock.png" alt="deverrouiller" title="Déverrouiller ce sujet" /></a>';
                        else //Sinon le topic est déverrouillé !
                            echo '<a href="?page=vt&action=lock&t='.$topic.'"><img src="./images/lock.png" alt="verrouiller" title="Verrouiller ce sujet" /></a>';
                        echo '<select name="dest">';
                        while($data3 = mysqli_fetch_assoc($result3))
                        {
                            echo'<option value='.$data3['forum_id'].' id='.$data3['forum_id'].'>'.stripslashes(htmlspecialchars($data3['forum_name'])).'</option>';
                        }
                        echo'</select>';
                        echo '<input type="hidden" name="from" value='.$data['forum_id'].'>';
                        echo '<input type="submit" name="submit" value="Déplacer" />';
                        echo '</form>';
                    }
                    echo stripslashes(htmlspecialchars($data['topic_titre']));
                    ?>

                                    </th>
                                </tr>

                    <?php
                    $i = 0;
                    while ($data2 = mysqli_fetch_assoc($result2))
                    {
                        echo '<tr>';
						
                        echo '<td class="vt_auteur" rowspan="2" >';
                        echo '<a href="?page=vp&m='.$data2['membre_id'].'&action=consulter">'.stripslashes(htmlspecialchars($data2['membre_pseudo'])).'</a><br/>';
                        if (!empty($data2['membre_avatar']))
                            echo '<img src="./images/avatars/'.$data2['membre_avatar'].'" alt="avatar" /><br/>';
                        echo '<div class="petit" >';
                        echo '<span class="valeur_texte"  >'.$list_rang_profil[$data2['membre_rang']].'</span></br>';
                        if (!empty($data2['membre_occup']))
                            echo 'Occupation : '.stripslashes(htmlspecialchars($data2['membre_occup'])).'<br/>';
                        if (!empty($data2['membre_localisation']))
                            echo 'Localisation : '.stripslashes(htmlspecialchars($data2['membre_localisation'])).'<br/>';
                        echo '<br/>Messages : '.$data2['membre_post'].'<br/>';
                        echo '</div>';
                        echo '</td>';


                        echo '<td class="vt_mess" ><p class="left" >Posté à '.date('H\hi \l\e d M y',$data2['post_time']).'</p>';
                        
                        echo '<p class="right" >';
                        if ((intval($_SESSION['id']) == $data2['post_createur']) || verif_auth($data['auth_modo']))
                        {
                            if ($data['topic_genre'] == 'Sondage' && $data['topic_cloture'] == 0)
                            {
                                echo '<a href="?page=vt&t='.$topic.'&action=cloturer" ><img src="./images/cloturer.png" alt="Clôturer" title="Clôturer ce message" /></a>';
                            }
                            echo '<a href="?page=vt&t='.$data2['topic_id'].'&p='.$data2['post_id'].'&action=delete"><img src="./images/x.png" alt="Supprimer" title="Supprimer ce message" /></a>';
                            echo '<a href="?page=vt&t='.$data2['topic_id'].'&p='.$data2['post_id'].'&action=edit"><img src="./images/editer.png" alt="Editer" title="Editer ce message" /></a>';
                        }
                        if ((verif_auth($data['auth_post']) && $data['topic_genre'] != 'Annonce' && $data['topic_locked'] == 0) || (verif_auth($data['auth_annonce']) && $data['topic_genre']=='PostIt') || verif_auth($data['auth_modo']))
                        {
                            echo '<a href="javascript:citer('.$data2['post_id'].', \''.$data2['membre_pseudo'].'\')" ><img src="./images/citer.png" alt="Citer" title="Citer ce message" /></a>';
                        }
                        echo '</p></td>';


                        echo '</tr>';



                        echo '<tr id="'.$data2['post_id'].'" >';
                        echo'<td class="vt_mess" >';
                        echo code(nl2br(stripslashes(htmlspecialchars($data2['post_texte']))));

                        if ($data['topic_genre'] == 'Sondage' && $i == 0)
                        {
                            echo '<hr/>';
                            $reponseExiste = 0;
                            if ($_SESSION['connected'])
                            {
                                $query = 'SELECT count(sondage_id)
                                            FROM reponse_sondage
                                            WHERE sondage_post_id = "'.$data2['post_id'].'"
                                            AND sondage_membre_id = "'.$_SESSION['id'].'"';
                                $result3 = mysqli_query($mysqli, $query)or die(mysqli_error());
                                $reponseExiste = mysqli_data_seek($result3, 0);
                            }


                            if (!verif_auth($data['auth_view']) || $reponseExiste != 0 || $data['topic_cloture'] == 1) // on montre les résultat du sondage
                            {
                                $query = 'SELECT option_texte, count(sondage_id) AS nb_reponse
                                                    FROM sondage_option
                                                    LEFT JOIN reponse_sondage ON sondage_post_id = option_post_id AND sondage_option_id = option_id
                                                    WHERE option_post_id = "'.$data2['post_id'].'"
                                                    GROUP BY option_id';
                                $result3 = mysqli_query($mysqli, $query)or die(mysqli_error());

                                $totalReponse = 0;
                                while ($data3 = mysqli_fetch_assoc($result3))
                                {
                                    $totalReponse += $data3['nb_reponse'];
                                }

                                echo '<table>';

                                mysqli_data_seek($result3, 0);
                                while ($data3 = mysqli_fetch_assoc($result3))
                                {
                                    echo '<tr>';
                                    echo '<td class="option_sondage" >'.$data3['option_texte'].'</td>';
                                    $pourcentage = round($data3['nb_reponse']/$totalReponse*100);
                                    if ($pourcentage == 0)
                                        echo '<td class="pourcentage_sondage" ><div style="width: 1px;" ></div></td>';
                                    else
                                        echo '<td class="pourcentage_sondage" ><div style="width: '.$pourcentage.'%;" ></div></td>';
                                    echo '<td class="center" >'.$pourcentage.'%</td>';
                                    echo '<td class="center">('.$data3['nb_reponse'].' réponse(s))</td>';
                                    echo '</tr>';
                                }
                                echo '</table>';
                                echo '<p class="center" >'.$totalReponse.' Personne(s) a(ont) participé au sondage</p>';
                            }
                            else // sinon on affiche les différents choix
                            {
                                $query = 'SELECT option_id,
                                                option_texte
                                                    FROM sondage_option
                                                    WHERE option_post_id = "'.$data2['post_id'].'"';
                                $result3 = mysqli_query($mysqli, $query)or die(mysqli_error());

                                echo '<form action="?page=vt&t='.$topic.'&action=sondage" method="post" >';
                                while ($data3 = mysqli_fetch_assoc($result3))
                                {
                                    echo '<p><input type="radio" name="sondage" value="'.$data3['option_id'].'" > '.$data3['option_texte'].'</p>';
                                }
                                echo '<p class="center" ><input type="submit" name="submit" value="Répondre" /></p>';
                                echo '</form>';
                            }
                        }

                        echo '<hr/>';
                        if ($authBbcodeSign)
                            echo code(nl2br(stripslashes(htmlspecialchars($data2['membre_signature']))));
                        else
                                echo nl2br(stripslashes(htmlspecialchars($data2['membre_signature'])));
                        echo '</td>';
                        echo '</tr>';
                        $i++;
                    }
                    ?>

                            </table>

                    <?php

                    if ((verif_auth($data['auth_post']) && $data['topic_genre'] != 'Annonce' && $data['topic_locked'] == 0) || (verif_auth($data['auth_annonce']) && $data['topic_genre']=='PostIt') || verif_auth($data['auth_modo']))
                    {
                        ?>

                        <form method="post" action="?page=vt&action=repondre&t=<?php echo $topic ?>" name="formulaire" id="text_editor" >
                            <div id="bb_options" >
                                <div class="left" >
                                    <ul>
                                        <li><input type="button" name="gras" value="Gras" onClick="bbcode('[g]', '[/g]');return(false)" /></li>
                                        <li><input type="button" name="image" value="image" onClick="bbcode('[img]', '[/img]');return(false)" /></li>
                                    </ul>
                                    <ul>
                                        <li><input type="button" name="souligné" value="Souligné" onClick="bbcode('[s]', '[/s]');return(false)" /></li>
                                        <li><input type="button" name="lien" value="Lien" onClick="bbcode('[url=]', '[/url]');return(false)" /></li>
                                    </ul>
                                    <ul>
                                        <li><input type="button" name="italic" value="Italic" onClick="bbcode('[i]', '[/i]');return(false)" /></li>
                                        <li><input type="button" name="citation" value="citation" onClick="bbcode('[quote=]', '[/quote]');return(false)" /></li>
                                    </ul>
                                </div>
                                <div class="right" >
                                    <ul>
                                        <li><img src="./images/smileys/smile.gif" title=":)" alt=":)" onClick="smilies(':)');return(false)" /></li>
                                        <li><img src="./images/smileys/^^.gif" title="^^" alt="^^" onClick="smilies('^^');return(false)" /></li>
                                        <li><img src="./images/smileys/biggrin.gif" title=":D" alt=":D" onClick="smilies(':D');return(false)" /></li>
                                        <li><img src="./images/smileys/mdr.gif" title="XD" alt="XD" onClick="smilies('XD');return(false)" /></li>
                                        <li><img src="./images/smileys/he.gif" title="he" alt="he" onClick="smilies(':he:');return(false)" /></li>
                                        <li><img src="./images/smileys/intello.gif" title="intello" alt="intello" onClick="smilies(':intello:');return(false)" /></li>
                                        <li><img src="./images/smileys/wink2.gif" title=";)" alt=";)" onClick="smilies(';)');return(false)" /></li>
                                        <li><img src="./images/smileys/tongue.gif" title=":p" alt=":p" onClick="smilies(':p');return(false)" /></li>
                                        <li><img src="./images/smileys/winktongue.gif" title=";p" alt=";p" onClick="smilies(';p');return(false)" /></li>
                                        <li><img src="./images/smileys/oh.gif" title="O_o" alt="O_o" onClick="smilies('O_o');return(false)" /></li>
                                        <li><img src="./images/smileys/eek.gif" title=":eek:" alt=":eek:" onClick="smilies(':eek:');return(false)" /></li>
                                        <li><img src="./images/smileys/shocked.gif" title=":o" alt=":o" onClick="smilies(':o');return(false)" /></li>
                                    </ul>
                                    <ul>
                                        <li><img src="./images/smileys/ouch.gif" title=":s" alt=":s" onClick="smilies(':s');return(false)" /></li>
                                        <li><img src="./images/smileys/erf.gif" title=":|" alt=":|" onClick="smilies(':|');return(false)" /></li>
                                        <li><img src="./images/smileys/frown.gif" title=":(" alt=":(" onClick="smilies(':(');return(false)" /></li>
                                        <li><img src="./images/smileys/aww.gif" title="aww" alt="aww" onClick="smilies(':aww:');return(false)" /></li>
                                        <li><img src="./images/smileys/bad.gif" title="bad" alt="bad" onClick="smilies(':bad:');return(false)" /></li>
                                        <li><img src="./images/smileys/clown.gif" title="clowms" alt="clowm" onClick="smilies(':clowm:');return(false)" /></li>
                                        <li><img src="./images/smileys/kiss.gif" title="kiss" alt="kiss" onClick="smilies(':kiss:');return(false)" /></li>
                                        <li><img src="./images/smileys/mad.gif" title="è_é" alt="è_é" onClick="smilies('è_é');return(false)" /></li>
                                        <li><img src="./images/smileys/money.gif" title="$_$" alt="$_$" onClick="smilies('$_$');return(false)" /></li>
                                        <li><img src="./images/smileys/glasses.gif" title="B)" alt="B)" onClick="smilies('B)');return(false)" /></li>
                                        <li><img src="./images/smileys/zzz.gif" title="zzz" alt="zzz" onClick="smilies(':zzz:');return(false)" /></li>
                                        <li><img src="./images/smileys/cry.gif" title=":'(" alt=":'(" onClick="smilies(':\'(');return(false)" /></li>
                                    </ul>
                                    <ul>
                                        <li><img src="./images/smileys/love.gif" title="love" alt="love" onClick="smilies(':love:');return(false)" /></li>
                                        <li><img src="./images/smileys/happy.gif" title="happy" alt="happy" onClick="smilies(':happy:');return(false)" /></li>
                                        <li><img src="./images/smileys/arf.gif" title=":/" alt=":/" onClick="smilies(':/ ');return(false)" /></li>
                                        <li><img src="./images/smileys/jap.gif" title="jap" alt="jap" onClick="smilies(':jap:');return(false)" /></li>
                                        <li><img src="./images/smileys/666.gif" title="666" alt="666" onClick="smilies(':666:');return(false)" /></li>
                                        <li><img src="./images/smileys/note.gif" title="note" alt="note" onClick="smilies(':note:');return(false)" /></li>
                                        <li><img src="./images/smileys/star.gif" title="star" alt="star" onClick="smilies(':star:');return(false)" /></li>
                                        <li><img src="./images/smileys/present.gif" title="kado" alt="kado" onClick="smilies(':kado:');return(false)" /></li>
                                        <li><img src="./images/smileys/heart.gif" title="<3" alt="<3" onClick="smilies('<3');return(false)" /></li>
                                        <li><img src="./images/smileys/unlove.gif" title="</3" alt="</3" onClick="smilies('</3');return(false)" /></li>
                                        <li><img src="./images/smileys/idea.gif" title="idee" alt="idee" onClick="smilies(':idee:');return(false)" /></li>
                                        <li><img src="./images/smileys/user.gif" title="user" alt="user" onClick="smilies(':user:');return(false)" /></li>
                                    </ul>
                                </div>
                            </div>
                            <div id="message_area" >
                                <p><textarea name="message"></textarea></p>
                            </div>
                            
                        <?php
                        if (verif_auth($data['auth_modo']))
                        {
                            echo '<div class="options_modo right" ><ul>';
                            echo '<li><img src="./images/smileys/arrow.gif" title="arrow" alt="arrow" onClick="smilies(\'->\');return(false)" /></li>';
                            echo '<li><img src="./images/smileys/info.gif" title="info" alt="info" onClick="smilies(\':i:\');return(false)" /></li>';
                            echo '<li><img src="./images/smileys/warn.gif" title="warn" alt="warn" onClick="smilies(\':!:\');return(false)" /></li>';
                            echo '</ul></div>';
                            echo '<div class="options_modo left" >';
                            echo '<input type="button" name="color" value="Color" onClick="bbcode(\'[color=]\', \'[/color]\');return(false)" />';
                            echo '</div>';
                        }
                        ?>
                            <p class="center" >
                                <input type="submit" name="submit" value="Envoyer" />
                                <input type="reset" name = "Effacer" value = "Effacer"/>
                            </p>
                        </form>

                        <?php
                    }

                    //On ajoute 1 au nombre de visites de ce topic
                    $query = 'UPDATE topic
                                SET topic_vu = topic_vu + 1
                                WHERE topic_id = "'.$topic.'"';
                    $result = mysqli_query($mysqli, $query) or die (mysqli_error());
                }
            }
            else
            {
                echo '<p>Vous n\'avez pas accès à ce topic</p>';
            }
        }
        else
        {
            echo '<p>Erreur! Le topic que vous essayez de visiter n\'existe pas</p>';
        }
        break;
}
?>
