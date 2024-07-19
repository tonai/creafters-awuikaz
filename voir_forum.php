<?php
$action = ((isset($_GET['action']))? stripslashes(htmlspecialchars($_GET['action'])): '');

switch($action)
{
    case "nouveau_topic":
        $forum = ((isset($_GET['f']))? intval($_GET['f']): 0);
        if (isset($_POST['submit']))
        {
            $query = 'SELECT auth_topic,
                            auth_annonce
                                FROM forum
                                WHERE forum_id = "'.$forum.'"';
            $result = mysqli_query($mysqli, $query)or die(mysqli_error());
            if (mysqli_num_rows($result) != 0)
            {
                $data = mysqli_fetch_array($result);
                $message = mysqli_real_escape_string($_POST['message']);
                $mess = mysqli_real_escape_string($_POST['mess']);
                $titre = mysqli_real_escape_string($_POST['titre']);
                $description = mysqli_real_escape_string($_POST['description']);
                $temps = time();

                if (($mess == 'Message' && verif_auth($data['auth_topic']) && isset($_POST['submit'])) || ($mess == 'PostIt' && verif_auth($data['auth_annonce']) && isset($_POST['submit'])))
                {
                    if (!empty($message) && !empty($titre))
                    {

                        //On entre le topic dans la base de donnée en laissant
                        //le champ topic_last_post à 0
                        $query = 'INSERT INTO topic (forum_id,
                                                    topic_titre,
                                                    topic_desc,
                                                    topic_createur,
                                                    topic_vu,
                                                    topic_time,
                                                    topic_genre,
                                                    topic_last_post,
                                                    topic_post)
                                            VALUES("'.$forum.'",
                                                    "'.$titre.'",
                                                    "'.$description.'",
                                                    "'.intval($_SESSION['id']).'",
                                                    "1",
                                                    "'.$temps.'",
                                                    "'.$mess.'",
                                                    "0",
                                                    "0")';
                        $result = mysqli_query($mysqli, $query) or die ("Un problème est survenu lors de l'envoi du message");

                        $nouveautopic = mysqli_insert_id();
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
                                                    "'.$nouveautopic.'",
                                                    "'.$forum.'")';
                        $result = mysqli_query($mysqli, $query) or die ('Un problème est survenu lors de la création du topic');

                        $nouveaupost = mysqli_insert_id();
                        //Ici on update comme prévu la valeur de topic_last_post et de topic_first_post
                        $query = 'UPDATE topic
                                    SET topic_last_post = "'.$nouveaupost.'",
                                        topic_first_post = "'.$nouveaupost.'"
                                    WHERE topic_id = "'.$nouveautopic.'"';
                        $result = mysqli_query($mysqli, $query) or die ('Un problème est survenu lors de la création du topic');

                        //Enfin on met à jour les tables forum et membres
                        $query = 'UPDATE forum
                                    SET forum_post = forum_post + 1 ,
                                        forum_topic = forum_topic + 1,
                                        forum_last_post_id = "'.$nouveaupost.'"
                                    WHERE forum_id = "'.$forum.'"';
                        $result = mysqli_query($mysqli, $query) or die ('Un problème est survenu lors de la création du topic');

                        $query = 'UPDATE membres
                                    SET membre_post = membre_post + 1
                                    WHERE membre_id = "'.intval($_SESSION['id']).'"';
                        $result = mysqli_query($mysqli, $query) or die ("Un problème est survenu lors de l'envoi du message");

                        //On ajoute une ligne dans la table forum_topic_view
                        $query = 'INSERT INTO topic_view (tv_id,
                                                                tv_topic_id,
                                                                tv_forum_id,
                                                                tv_post_id,
                                                                tv_poste)
                                                        VALUES("'.intval($_SESSION['id']).'",
                                                                "'.$nouveautopic.'",
                                                                "'.$forum.'",
                                                                "'.$nouveaupost.'",
                                                                "1")';
                        $result = mysqli_query($mysqli, $query);

                        //Et un petit message
                        echo '<p>Votre topic a bien été créé!<br/>';
                        echo 'Cliquez <a href="index.php">ici</a> pour revenir à l\'index du forum<br/>';
                        echo 'Cliquez <a href="?page=vt&t='.$nouveautopic.'">ici</a> pour voir votre topic</p>';
                    }
                    else
                    {
                         echo'<p>Votre message ou votre titre est vide, cliquez <a href="?page=vt&action=nouveau_topic&f='.$forum.'">ici</a> pour recommencer</p>';
                    }
                }
                else
                {
                    echo '<p>Vous ne pouvez pas créer de topic</p>';
                }
            }
            else
            {
                echo '<p>Erreur! Le forum dans lequel vous essayez de créer un topic n\'existe pas</p>';
            }
        }
        else
        {
            $query = 'SELECT forum_name,
                            auth_view,
                            auth_post,
                            auth_topic,
                            auth_annonce,
                            auth_modo
                                FROM forum
                                WHERE forum_id ="'.$forum.'"';
            $result = mysqli_query($mysqli, $query) or die(mysqli_error());

            if (mysqli_num_rows($result) != 0)
            {
                $data = mysqli_fetch_array($result);
                if (verif_auth($data['auth_topic']))
                {
                    ?>

                        <form method="post" action="?page=vf&action=nouveau_topic&f=<?php echo $forum ?>" name="formulaire" id="text_editor" >
                            <p>Titre : <input type="text" name="titre" class="w100" /></p>
                            <p>description : <input type="text" name="description" class="w100" /></p>
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

                        echo '<p class="center" >';
                        echo '<select name="mess" >';
                        echo '<option value="Message" selected="selected" >Message</option>';
                        echo '<option value="PostIt" >Post-it</option>';
                        echo '</select>';
                    }
                    else
                    {
                        echo '<p class="center" >';
                        echo '<input type="radio" name="mess" value="Message" checked="checked" style="display: none;" />';
                    }
                    ?>

                                <input type="submit" name="submit" value="Envoyer" />
                                <input type="reset" name = "Effacer" value = "Effacer"/>
                            </p>
                        </form>

                    <?php
                }
                else
                {
                    echo '<p>Vous ne pouvez pas créer de topic</p>';
                }
            }
            else
            {
                echo '<p>Erreur! Le forum dans lequel vous essayez de créer un topic n\'existe pas</p>';
            }
        }
        break;

    case "nouveau_sondage":
        $forum = ((isset($_GET['f']))? intval($_GET['f']): 0);
        if (isset($_POST['submit']))
        {
            $query = 'SELECT auth_sondage
                        FROM forum
                        WHERE forum_id = "'.$forum.'"';
            $result = mysqli_query($mysqli, $query)or die(mysqli_error());
            if (mysqli_num_rows($result) != 0)
            {
                $data = mysqli_fetch_array($result);;
                $titre = mysqli_real_escape_string($_POST['titre']);
                $description = mysqli_real_escape_string($_POST['description']);
                $message = mysqli_real_escape_string($_POST['message']);
                $temps = time();

                if (verif_auth($data['auth_sondage']))
                {
                    $count = 0;
                    foreach ($_POST['option'] as $option)
                    {
                        if (!empty($option))
                            $count++;
                    }

                    if (!empty($titre) && $count > 1)
                    {
                        //On entre le topic dans la base de donnée en laissant
                        //le champ topic_last_post à 0
                        $query = 'INSERT INTO topic (forum_id,
                                                    topic_titre,
                                                    topic_desc,
                                                    topic_createur,
                                                    topic_vu,
                                                    topic_time,
                                                    topic_genre,
                                                    topic_last_post,
                                                    topic_post)
                                            VALUES("'.$forum.'",
                                                    "'.$titre.'",
                                                    "'.$description.'",
                                                    "'.intval($_SESSION['id']).'",
                                                    "1",
                                                    "'.$temps.'",
                                                    "Sondage",
                                                    "0",
                                                    "0")';
                        $result = mysqli_query($mysqli, $query) or die ("Un problème est survenu lors de l'envoi du message");

                        $nouveautopic = mysqli_insert_id();

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
                                                    "'.$nouveautopic.'",
                                                    "'.$forum.'")';
                        $result = mysqli_query($mysqli, $query) or die ('Un problème est survenu lors de la création du topic');

                        $nouveaupost = mysqli_insert_id();
                        
                        //On ajoute les options du sondage
                        for ($i=0; $i<$count; $i++)
                        {
                            $query = 'INSERT INTO sondage_option (option_id,
                                                                option_post_id,
                                                                option_texte)
                                                            VALUES("",
                                                                "'.$nouveaupost.'",
                                                                "'.mysqli_real_escape_string($_POST['option'][$i]).'")';
                            $result = mysqli_query($mysqli, $query) or die ('Un problème est survenu lors de la création du topic');
                        }
                        
                        //Ici on update comme prévu la valeur de topic_last_post et de topic_first_post
                        $query = 'UPDATE topic
                                    SET topic_last_post = "'.$nouveaupost.'",
                                        topic_first_post = "'.$nouveaupost.'"
                                    WHERE topic_id = "'.$nouveautopic.'"';
                        $result = mysqli_query($mysqli, $query) or die ('Un problème est survenu lors de la création du topic');

                        //Enfin on met à jour les tables forum et membres
                        $query = 'UPDATE forum
                                    SET forum_post = forum_post + 1 ,
                                        forum_topic = forum_topic + 1,
                                        forum_last_post_id = "'.$nouveaupost.'"
                                    WHERE forum_id = "'.$forum.'"';
                        $result = mysqli_query($mysqli, $query) or die ('Un problème est survenu lors de la création du topic');

                        $query = 'UPDATE membres
                                    SET membre_post = membre_post + 1
                                    WHERE membre_id = "'.intval($_SESSION['id']).'"';
                        $result = mysqli_query($mysqli, $query) or die ("Un problème est survenu lors de l'envoi du message");

                        //On ajoute une ligne dans la table forum_topic_view
                        $query = 'INSERT INTO topic_view (tv_id,
                                                                tv_topic_id,
                                                                tv_forum_id,
                                                                tv_post_id,
                                                                tv_poste)
                                                        VALUES("'.intval($_SESSION['id']).'",
                                                                "'.$nouveautopic.'",
                                                                "'.$forum.'",
                                                                "'.$nouveaupost.'",
                                                                "1")';
                        $result = mysqli_query($mysqli, $query);

                        //Et un petit message
                        echo '<p>Votre sondage a bien été créé!<br/>';
                        echo 'Cliquez <a href="index.php">ici</a> pour revenir à l\'index du forum<br/>';
                        echo 'Cliquez <a href="?page=vt&t='.$nouveautopic.'">ici</a> pour voir votre sondage</p>';
                    }
                    else
                    {
                         echo'<p>votre titre est vide ou il n\'y a pas au moins 2 options, cliquez <a href="?page=vt&action=nouveau_sondage&f='.$forum.'">ici</a> pour recommencer</p>';
                    }
                }
                else
                {
                    echo '<p>Vous ne pouvez pas créer de sondage</p>';
                }
            }
            else
            {
                echo '<p>Erreur! Le forum dans lequel vous essayez de créer un topic n\'existe pas</p>';
            }
        }
        else
        {
            $query = 'SELECT auth_sondage,
                            auth_modo
                                FROM forum
                                WHERE forum_id = "'.$forum.'"';
            $result = mysqli_query($mysqli, $query) or die(mysqli_error());

            if (mysqli_num_rows($result) != 0)
            {
                $data = mysqli_fetch_array($result);
                if (verif_auth($data['auth_sondage']))
                {
                    ?>

                        <form method="post" action="?page=vf&action=nouveau_sondage&f=<?php echo $forum ?>" name="formulaire" id="text_editor" >
                            <p>Titre : <input type="text" name="titre" class="w100" /></p>
                            <p>description : <input type="text" name="description" class="w100" /></p>
                            <div id="sondage_options" >

                    <?php
                    for ($i=0; $i<NB_OPTIONS_SONDAGE; $i++) {
                        echo '<p><label>option '.($i+1).' : </label><input type="text" name="option['.$i.']" class="w50" /></p>';
                    }
                    ?>

                            </div>
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
                                <p><textarea name="message"></textarea></p>
                            </div>

                    <?php
                    if (verif_auth($data['auth_modo']))
                    {
                        echo '<div id="message_modo" ><ul>';
                        echo '<li><img src="./images/smileys/arrow.gif" title="arrow" alt="arrow" onClick="smilies(\'->\');return(false)" /></li>';
                        echo '<li><img src="./images/smileys/info.gif" title="info" alt="info" onClick="smilies(\':i:\');return(false)" /></li>';
                        echo '<li><img src="./images/smileys/warn.gif" title="warn" alt="warn" onClick="smilies(\':!:\');return(false)" /></li>';
                        echo '</ul></div>';

                        echo '<p class="center" >';
                    }
                    else
                    {
                        echo '<p class="center" >';
                        echo '<input type="radio" name="mess" value="Message" checked="checked" style="display: none;" />';
                    }
                    ?>
                    
                                <input type="submit" name="submit" value="Envoyer" />
                                <input type="reset" name = "Effacer" value = "Effacer"/>
                            </p>
                        </form>

                    <?php
                }
                else
                {
                    echo '<p>Vous ne pouvez pas créer de sondage</p>';
                }
            }
            else
            {
                echo '<p>Erreur! Le forum dans lequel vous essayez de créer un topic n\'existe pas</p>';
            }
        }
        break;

    case 'marquer':
        $forum = ((isset($_GET['f']))? intval($_GET['f']): 0);
        if ($_SESSION['connected'])
        {
            $query = 'SELECT topic_id,
                            topic_last_post
                                FROM topic
                                WHERE forum_id = "'.$forum.'"';
            $result = mysqli_query($mysqli, $query) or die (mysqli_error());
            while ($data = mysqli_fetch_array($result))
            {
                //Topic déjà consulté ?
                $query = 'SELECT COUNT(*)
                            FROM topic_view
                            WHERE tv_topic_id = "'.$data['topic_id'].'"
                            AND tv_id = "'.intval($_SESSION['id']).'"';
                $result2 = mysqli_query($mysqli, $query) or die (mysqli_error());
                $nbr_vu = mysqli_data_seek($result2, 0);

                if ($nbr_vu == 0) //Si c'est la première fois on insère une ligne entière
                {
                    $query = 'INSERT INTO topic_view (tv_id,
                                                    tv_topic_id,
                                                    tv_forum_id,
                                                    tv_post_id)
                                            VALUES ("'.intval($_SESSION['id']).'",
                                                    "'.$data['topic_id'].'",
                                                    "'.$forum.'",
                                                    "'.$data['topic_last_post'].'")';
                    $result2 = mysqli_query($mysqli, $query) or die (mysqli_error());
                }
                else //Sinon, on met simplement à jour
                {
                    $query = 'UPDATE topic_view
                                SET tv_post_id = "'.$data['topic_last_post'].'"
                                WHERE tv_topic_id = "'.$data['topic_id'].'"
                                AND tv_id = "'.intval($_SESSION['id']).'"';
                    $result2 = mysqli_query($mysqli, $query) or die (mysqli_error());
                }
            }
        }
    default:
        $forum = ((isset($_GET['f']))? intval($_GET['f']): 0);

        //A partir d'ici, on va compter le nombre de messages
        //pour n'afficher que les 25 premiers
        $query = 'SELECT forum_name,
                        forum_topic,
                        auth_view,
                        auth_topic,
                        auth_sondage
                            FROM forum
                            WHERE forum_id = "'.$forum.'"';
        $result = mysqli_query($mysqli, $query) or die (mysqli_error());

        if (mysqli_num_rows($result) != 0)
        {
            $data = mysqli_fetch_assoc($result);
            if (verif_auth($data['auth_view']))
            {
                $query = 'SELECT *
                            FROM config';
                $config = mysqli_query($mysqli, $query) or die(mysqli_error());
                while ($dataConfig = mysqli_fetch_assoc($config))
                {
                    if ($dataConfig['config_nom'] == 'topic_par_page')
                        $messageParPage = $dataConfig['config_valeur'];
                }

                if (!isset($_GET['index']))
                    $message=0;
                else
                    $message=$messageParPage*($_GET['index']-1);

                // boutons poster et sondage
                if (verif_auth($data['auth_topic']))
                    echo '<a href="?page=vf&action=nouveau_topic&f='.$forum.'"><img src="./images/poster.png" alt="Nouveau topic" title="Poster un nouveau topic"></a> ';
                if (verif_auth($data['auth_sondage']))
                    echo ' <a href="?page=vf&action=nouveau_sondage&f='.$forum.'"><img src="./images/sondage.png" alt="Nouveau sondage" title="Poster un nouveau sondage"></a>';


                $select = '';
                $join = '';
                if ($_SESSION['connected'])
                {
                    $select = ',tv_id,
                                tv_post_id,
                                tv_poste';
                    $join = 'LEFT JOIN topic_view ON topic.topic_id = tv_topic_id AND tv_id = "'.intval($_SESSION['id']).'"';
                }

                // récupère les annonces (post-it)
                $query = 'SELECT topic.topic_id,
                                topic_titre,
                                topic_desc,
                                topic_createur,
                                topic_vu,
                                topic_post,
                                topic_time,
                                topic_last_post,
                                topic_genre,
                                Mb.membre_pseudo AS membre_pseudo_createur,
                                post_createur,
                                post_time,
                                post_id,
                                Ma.membre_pseudo AS membre_pseudo_last_posteur
                                '.$select.'
                                    FROM topic
                                    LEFT JOIN membres Mb ON Mb.membre_id = topic.topic_createur
                                    LEFT JOIN post ON topic.topic_last_post = post.post_id
                                    LEFT JOIN membres Ma ON Ma.membre_id = post.post_createuR
                                    '.$join.'
                                    WHERE topic.forum_id = "'.$forum.'"
                                    AND topic_genre = "PostIt"
                                    ORDER BY topic_last_post DESC
                                    LIMIT '.$message.', '.$messageParPage.'';

                $result = mysqli_query($mysqli, $query) or die (mysqli_error());
                $nbPostIt = mysqli_num_rows($result);

                $nbPost = 0;
                if ($nbPostIt < $messageParPage)
                {
                    // récupère les topics et sondages
                    $query = 'SELECT topic.topic_id,
                                    topic_titre,
                                    topic_desc,
                                    topic_createur,
                                    topic_vu,
                                    topic_post,
                                    topic_time,
                                    topic_last_post,
                                    topic_genre,
                                    topic_locked,
                                    Mb.membre_pseudo AS membre_pseudo_createur,
                                    post_createur,
                                    post_time,
                                    post_id,
                                    Ma.membre_pseudo AS membre_pseudo_last_posteur
                                    '.$select.'
                                        FROM topic
                                        LEFT JOIN membres Mb ON Mb.membre_id = topic.topic_createur
                                        LEFT JOIN post ON topic.topic_last_post = post.post_id
                                        LEFT JOIN membres Ma ON Ma.membre_id = post.post_createuR
                                        '.$join.'
                                        WHERE topic.forum_id = "'.$forum.'"
                                        AND topic_genre = "Message"
                                        OR topic.forum_id = "'.$forum.'"
                                        AND topic_genre = "Sondage"
                                        ORDER BY topic_last_post DESC
                                        LIMIT '.$message.', '.($messageParPage-$nbPostIt).'';

                    $result2 = mysqli_query($mysqli, $query) or die (mysqli_error());
                    $nbPost = mysqli_num_rows($result2);
                }

                if (($nbPostIt+$nbPost) > 0)
                {
                    ?>
                        <table>
                            <tr>
                                <th><?php echo stripslashes(htmlspecialchars($data['forum_name'])); ?></th>
                                <th class="auteur" >Auteur</th>
                                <th class="nombrereponses" >Réponses</th>
                                <th class="nombrevues" >Vues</th>
                                <th class="derniermessage" >Dernier message</th>
                            </tr>

                    <?php
                    // affichage des annonces
                    while ($data = mysqli_fetch_assoc($result))
                    {
                        echo '<tr>';
                        echo '<td class="forum" ><img src="./images/postit.png" alt="Post-It" class="left"/>';
                        echo '<div><a href="?page=vt&t='.$data['topic_id'].'" title="commencé le '.date('d/m/y à H:i',$data['topic_time']).'">'.stripslashes(htmlspecialchars($data['topic_titre'])).'</a><br/>';
                        echo stripslashes(htmlspecialchars($data['topic_desc'])).'</div></td>';
                        echo '<td class="auteur" ><a href="?page=vp&m='.$data['topic_createur'].'&action=consulter">'.stripslashes(htmlspecialchars($data['membre_pseudo_createur'])).'</a></td>';
                        echo '<td class="nombrereponses" >'.$data['topic_post'].'</td>';
                        echo '<td class="nombrevues" >'.$data['topic_vu'].'</td>';

                        $nbr_post = $data['topic_post'] +1;
                        $page = ceil($nbr_post / $messageParPage);
                        
                        echo '<td class="derniermessage" >'.date('d/m/Y H:i',$data['post_time']).'<br/>';
                        echo '<a href="?page=vp&m='.$data['post_createur'].'&action=consulter">'.stripslashes(htmlspecialchars($data['membre_pseudo_last_posteur'])).'</a>';
                        echo '<a href="?page=vt&t='.$data['topic_id'].'&index='.$page.'"><img src="./images/go.png" alt="go" /></a></td>';
                        echo '</tr>';
                    }


                    // affichage des posts
                    if ($nbPost > 0)
                    {
                        while ($data2 = mysqli_fetch_assoc($result2))
                        {
                            //Gestion de l'image à afficher
                            if ($_SESSION['connected']) // Si le membre est connecté
                            {
                                if ($data2['tv_id'] == $_SESSION['id']) //S'il a lu le topic
                                {
                                    if ($data2['tv_poste'] == '0') // S'il n'a pas posté
                                    {
                                        if ($data2['tv_post_id'] == $data2['topic_last_post']) //S'il n'y a pas de nouveau message
                                        {
                                            $ico_mess = 'message.png';
                                        }
                                        else
                                        {
                                            $ico_mess = 'messagec_non_lus.png'; //S'il y a un nouveau message
                                        }
                                    }
                                    else // S'il a  posté
                                    {
                                        if ($data2['tv_post_id'] == $data2['topic_last_post']) //S'il n'y a pas de nouveau message
                                        {
                                            $ico_mess = 'messagep_lu.png';
                                        }
                                        else //S'il y a un nouveau message
                                        {
                                            $ico_mess = 'messagep_non_lu.png';
                                        }
                                    }
                                }
                                else //S'il n'a pas lu le topic
                                {
                                    $ico_mess = 'message_non_lu.png';
                                }
                            } //S'il n'est pas connecté
                            else
                            {
                                $ico_mess = 'message.png';
                            }

                            if ($data2['topic_locked'] == 1)
                            {
                                $ico_mess = 'locked.png';
                            }

                            echo '<tr>';
                            echo '<td class="forum" ><img src="./images/'.$ico_mess.'" alt="./images/'.$ico_mess.'" class="left" />';
                            echo '<div><a href="?page=vt&t='.$data2['topic_id'].'" title="commencé le '.date('d/m/y à H:i',$data2['topic_time']).'">'.stripslashes(htmlspecialchars($data2['topic_titre'])).'</a><br/>';
                            echo '<span class="petit" >'.stripslashes(htmlspecialchars($data2['topic_desc'])).'&nbsp;</span></div></td>';
                            echo '<td class="auteur" ><a href="?page=vp&m='.$data2['topic_createur'].'&action=consulter">'.stripslashes(htmlspecialchars($data2['membre_pseudo_createur'])).'</a></td>';
                            echo '<td class="nombrereponses" >'.$data2['topic_post'].'</td>';
                            echo '<td class="nombrevues" >'.$data2['topic_vu'].'</td>';

                            $nbr_post = $data2['topic_post'] +1;
                            $page = ceil($nbr_post / $messageParPage);

                            echo '<td class="derniermessage" >'.date('d/m/Y H:i',$data2['post_time']).'<br/>';
                            echo '<a href="?page=vp&m='.$data2['post_createur'].'&action=consulter">'.stripslashes(htmlspecialchars($data2['membre_pseudo_last_posteur'])).'</a>';
                            echo '<a href="?page=vt&t='.$data2['topic_id'].'&index='.$page.'"><img src="./images/go.png" alt="go" /></a></td>';
                            echo '</tr>';
                        }
                    }


                    echo '</table>';
                    if ($_SESSION['connected'])
                    {
                        echo '<p class="paragraphe" ><a href="?page=vf&f='.$forum.'&action=marquer" >Marquer tous les topics de ce forum comme lus</a></p>';
                    }
                }
                else //S'il n'y a pas de message
                {
                    echo'<p>Ce forum ne contient aucun sujet actuellement</p>';
                }
            }
            else
            {
                echo 'Vous n\'avez pas accès à ce forum !';
            }
        }
        else
        {
            echo '<p>Erreur! Le forum que vous essayez de visiter n\'existe pas</p>';
        }
        break;
}
?>
