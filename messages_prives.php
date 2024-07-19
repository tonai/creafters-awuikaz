<?php
$action = ((isset($_GET['action']))? stripslashes(htmlspecialchars($_GET['action'])): '');

if ($_SESSION['connected'])
{
    switch($action)
    {
        case "consulter": //Si on veut lire un message
            $mp = ((isset($_GET['mp']))? intval($_GET['mp']): 0); //On récupère la valeur de l'id
            $query = 'SELECT mp_id,
                            mp_expediteur,
                            mp_receveur,
                            mp_titre,
                            mp_time,
                            mp_text,
                            mp_lu,
                            membre_id,
                            membre_pseudo,
                            membre_avatar,
                            membre_localisation,
                            membre_inscrit,
                            membre_post,
                            membre_signature,
                            membre_rang
                                FROM mp
                                LEFT JOIN membres ON membre_id = mp_expediteur
                                WHERE mp_id = "'.$mp.'"';
            $result = mysqli_query($mysqli, $query) or die (mysqli_error());
            if (mysqli_num_rows($result) != 0)
            {
                $data = mysqli_fetch_assoc($result);
                if ($_SESSION['id'] == $data['mp_receveur'])
                {
                    ?>

                <table>
                <tr>
                    <th class="vt_auteur" >Auteur</th>
                    <th class="vt_mess" ><?php echo'<p class="right" ><a href="?page=mp&action=nouveau&mp='.$data['mp_id'].'"><img src="./images/repondre.gif" alt="Répondre" title="Répondre à ce message" /></a></p>'; ?>Message</th>
                </tr>
                <tr>

                    <?php
                    echo '<td class="vt_auteur" rowspan="2" >';
                        echo '<a href="?page=vp&m='.$data['membre_id'].'&action=consulter">'.stripslashes(htmlspecialchars($data['membre_pseudo'])).'</a><br/>';
                        if (!empty($data['membre_avatar']))
                            echo '<img src="./images/avatars/'.$data['membre_avatar'].'" alt="avatar" /><br/>';
                        echo '<div class="petit" >';
                        if ($data['membre_rang'] == 5)
                            echo '<span class="valeur_texte" >Administrateur</span><br/>';
                        elseif ($data['membre_rang'] == 4)
                            echo '<span class="valeur_texte"  >Modérateur</span><br/>';
                        elseif ($data['membre_rang'] == 3)
                            echo '<span class="valeur_texte"  >Membre guilde</span></br>';
                        if (!empty($data['membre_occup']))
                            echo 'Occupation : '.stripslashes(htmlspecialchars($data['membre_occup'])).'<br/>';
                        if (!empty($data['membre_localisation']))
                            echo 'Localisation : '.stripslashes(htmlspecialchars($data['membre_localisation'])).'<br/>';
                            echo '<br/>Messages : '.$data['membre_post'].'<br/>';
                        echo '</div>';
                        echo '</td>';
                    echo '<td class="vt_mess" >Posté à '.date('H\hi \l\e d M Y',$data['mp_time']).'</td>';
                    ?>

                </tr>
                <tr>

                    <?php
                    echo '<td class="vt_mess" >'.code(nl2br(stripslashes(htmlspecialchars($data['mp_text'])))).'</td>';
                    echo '</tr>';
                    echo '</table>';

                    if ($data['mp_lu'] == 0) //Si le message n'a jamais été lu
                    {
                        $query = 'UPDATE mp
                                    SET mp_lu = "1"
                                    WHERE mp_id= "'.$mp.'"';
                        $result = mysqli_query($mysqli, $query) or die (mysqli_error());
                    }
                }
                else
                {
                    echo '<p>Vous n\'avez pas accès à ce mp</p>';
                }
            }
            else
            {
                echo '<p>Erreur! Le message auquel vous essayez de répondre n\'existe pas</p>';
            }
            break;

        case "nouveau": //Nouveau mp
            if (isset($_POST['submit']))
            {
                $message = mysqli_real_escape_string($_POST['message']);
                $titre = mysqli_real_escape_string($_POST['titre']);
                $dest = mysqli_real_escape_string($_POST['to']);
                $temps = time();

                //On récupère la valeur de l'id du destinataire
                //Il faut déja vérifier le nom
                $query = 'SELECT membre_id
                            FROM membres
                            WHERE membre_pseudo = "'.$dest.'"';
                $result = mysqli_query($mysqli, $query) or die (mysqli_error());

                if (mysqli_num_rows($result) != 0)
                {
                    $data = mysqli_fetch_assoc($result);
                    //Enfin on peut envoyer le message
                    $query = 'INSERT INTO mp (mp_id,
                                            mp_expediteur,
                                            mp_receveur,
                                            mp_titre,
                                            mp_text,
                                            mp_time,
                                            mp_lu)
                                    VALUES ("",
                                            "'.intval($_SESSION['id']).'",
                                            "'.$data['membre_id'].'",
                                            "'.$titre.'",
                                            "'.$message.'",
                                            "'.$temps.'",
                                            "0")';
                    $result = mysqli_query($mysqli, $query) or die ("Le message n'a pas pu être envoyé, veuillez réessayer");

                    echo'<p>Votre message a bien été envoyé!<br/>';
                    echo 'Cliquez <a href="index.php">ici</a> pour revenir à l index du forum<br/>';
                    echo 'Cliquez <a href="?page=mp">ici</a> pour retourner à la messagerie</p>';
                }
                //Sinon l'utilisateur n'existe pas !
                else
                {
                    echo'<p>Désolé ce membre n existe pas, veuillez vérifier et réessayez à nouveau.</p>';
                }
            }
            else
            {
                $to = '';
                $titre = '';
                $text = '';
                if (isset($_GET['m']))
                {
                    $membreId = ((isset($_GET['m']))? intval($_GET['m']): '');
                    $query = 'SELECT membre_pseudo
                                FROM membres
                                WHERE membre_id = "'.$membreId.'"';
                    $result = mysqli_query($mysqli, $query) or die (mysqli_error());
                    if ($data = mysqli_fetch_assoc($result))
                        $to = $data['membre_pseudo'];
                }
                elseif (isset($_GET['mp']))
                {
                    $mp = ((isset($_GET['mp']))? intval($_GET['mp']): 0);
                    $query = 'SELECT mp_titre,
                                    mp_text,
                                    membre_pseudo
                                        FROM mp
                                        LEFT JOIN membres ON membre_id = mp_expediteur
                                        WHERE mp_id = "'.$mp.'"';
                    $result = mysqli_query($mysqli, $query)or die(mysqli_error());
                    if ($data = mysqli_fetch_assoc($result))
                    {
                        $to = $data['membre_pseudo'];
                        $titre = 'RE: '.$data['mp_titre'];
                        $text = "\n\n\n----------\n".$data['mp_text'];
                    }
                }
                ?>

                <form method="post" action="?page=mp&action=nouveau" name="formulaire" id="text_editor" >
                    <p>
                        <label for="to">Envoyer à : </label>
                        <input type="text" name="to" class="w100" value="<?php echo stripslashes(htmlspecialchars($to)); ?>" />
                    </p>
                    <p>
                        <label for="titre">Titre : </label>
                        <input type="text" name="titre" class="w100" value="<?php echo stripslashes(htmlspecialchars($titre)); ?>" />
                    </p>
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
                        <p><textarea name="message"><?php echo stripslashes(htmlspecialchars($text)); ?></textarea></p>
                    </div>

                <?php
                if ($_SESSION['level']>3)
                {
                    echo '<div id="message_modo" ><ul>';
                    echo '<li><img src="./images/smileys/arrow.gif" title="arrow" alt="arrow" onClick="smilies(\'->\');return(false)" /></li>';
                    echo '<li><img src="./images/smileys/info.gif" title="info" alt="info" onClick="smilies(\':i:\');return(false)" /></li>';
                    echo '<li><img src="./images/smileys/warn.gif" title="warn" alt="warn" onClick="smilies(\':!:\');return(false)" /></li>';
                    echo '</ul></div>';
                }
                ?>
                    <p class="center" >
                        <input type="submit" name="submit" value="Envoyer" />
                        <input type="reset" name = "Effacer" value = "Effacer"/>
                    </p>
                </form>

                <?php
            }
            break;

        case "supprimer":
            $mp = ((isset($_GET['mp']))? intval($_GET['mp']): 0);
            //Il faut vérifier que le membre est bien celui qui a reçu le message
            $query = 'SELECT mp_receveur
                        FROM mp
                        WHERE mp_id = '.$mp.'';
            $result = mysqli_query($mysqli, $query) or die (mysqli_error());

            if (mysqli_num_rows($result) != 0)
            {
                $data = mysqli_fetch_assoc($result);
                if ($_SESSION['id'] == $data['mp_receveur'])
                {
                    //2 cas pour cette partie : on est sûr de supprimer ou alors on ne l'est pas
                    $sur = (int) $_GET['sur'];
                    //Pas encore certain
                    if ($sur == 0)
                    {
                        echo'<p>Etes-vous certain de vouloir supprimer ce message ?<br/>';
                        echo '<a href="?page=mp&action=supprimer&mp='.$mp.'&sur=1">Oui</a> - <a href="?page=mp">Non</a></p>';
                    }
                    //Certain
                    else
                    {
                        $query = 'DELETE from mp
                                    WHERE mp_id = "'.$mp.'"';
                        $result = mysqli_query($mysqli, $query) or die (mysqli_error());
                        echo'<p>Le message a bien été supprimé.<br />';
                        echo 'Cliquez <a href="?page=mp">ici</a> pour revenir à la boite de messagerie.</p>';
                    }
                }
                else
                {
                    echo '<p>Vous n\'avez pas accès à ce mp</p>';
                }
            }
            else
            {
                echo '<p>Erreur! Le message auquel vous essayez de répondre n\'existe pas</p>';
            }
            break;

        //Si rien n'est demandé ou s'il y a une erreur dans l'url on affiche la boite de mp.
        default;
            $query = 'SELECT mp_lu,
                            mp_id,
                            mp_expediteur,
                            mp_titre,
                            mp_time,
                            membre_id,
                            membre_pseudo
                                FROM mp
                                LEFT JOIN membres ON mp_expediteur = membre_id
                                WHERE mp_receveur = '.intval($_SESSION['id']).'
                                ORDER BY mp_id DESC';
            $result = mysqli_query($mysqli, $query) or die(mysqli_error());
            if (mysqli_num_rows($result) > 0)
            {
                ?>

                <table>
                <tr>
                    <th colspan="2" ><?php echo'<p class="left" ><a href="?page=mp&action=nouveau"><img src="./images/nouveau_topic.gif" alt="Nouveau" title="Nouveau message" /></a></p>';
             ?>Sujet</th>
                    <th class="mp_expediteur" >Expéditeur</th>
                    <th class="mp_time" >Date</th>
                    <th class="mp_action" >Action</th>
                </tr>

                <?php
                //On boucle et on remplit le tableau
                while ($data = mysqli_fetch_assoc($result))
                {
                    echo'<tr>';
                    //Mp jamais lu, on affiche l'icone en question
                    if($data['mp_lu'] == 0)
                    {
                        echo'<td class="mp_image" ><img src="./images/topic_non_lu.gif" alt="Non lu" /></td>';
                    }
                    else //sinon une autre icone
                    {
                        echo'<td class="mp_image" ><img src="./images/topic.gif" alt="Déja lu" /></td>';
                    }
                    echo '<td class="mp_sujet" ><a href="?page=mp&action=consulter&mp='.$data['mp_id'].'">'.stripslashes(htmlspecialchars($data['mp_titre'])).'</a></td>';
                    echo '<td class="mp_expediteur" ><a href="?page=vp&action=consulter&m='.$data['membre_id'].'">'.stripslashes(htmlspecialchars($data['membre_pseudo'])).'</a></td>';
                    echo '<td class="mp_time" >'.date('H\hi \l\e d M Y',$data['mp_time']).'</td>';
                    echo '<td class="mp_action" ><a href="?page=mp&action=supprimer&mp='.$data['mp_id'].'&sur=0">supprimer</a></td>';
                    echo '</tr>';
                }
                echo '</table>';
            }
            else
            {
                echo'<p>Vous n avez aucun message privé pour l\'instant</p>';
            }
            break;
    }
}
?>