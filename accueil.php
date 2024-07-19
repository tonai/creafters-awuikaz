<?php
$level = ((isset($_SESSION['level']))? intval($_SESSION['level']): 1);
$action = ((isset($_GET['action']))? stripslashes(htmlspecialchars($_GET['action'])): '');

if ($action == 'marquer')
{
    if ($_SESSION['connected'])
    {
        $query = 'SELECT topic_id,
                        forum_id,
                        topic_last_post
                            FROM topic';
        $result = mysql_query($query) or die (mysql_error());
        while ($data = mysql_fetch_array($result))
        {
            //Topic déjà consulté ?
            $query = 'SELECT COUNT(*)
                        FROM topic_view
                        WHERE tv_topic_id = "'.$data['topic_id'].'"
                        AND tv_id = "'.intval($_SESSION['id']).'"';
            $result2 = mysql_query($query) or die (mysql_error());
            $nbr_vu = mysql_result($result2, 0);

            if ($nbr_vu == 0) //Si c'est la première fois on insère une ligne entière
            {
                $query = 'INSERT INTO topic_view (tv_id,
                                                tv_topic_id,
                                                tv_forum_id,
                                                tv_post_id)
                                        VALUES ("'.intval($_SESSION['id']).'",
                                                "'.$data['topic_id'].'",
                                                "'.$data['forum_id'].'",
                                                "'.$data['topic_last_post'].'")';
                $result2 = mysql_query($query) or die (mysql_error());
            }
            else //Sinon, on met simplement à jour
            {
                $query = 'UPDATE topic_view
                            SET tv_post_id = "'.$data['topic_last_post'].'"
                            WHERE tv_topic_id = "'.$data['topic_id'].'"
                            AND tv_id = "'.intval($_SESSION['id']).'"';
                $result2 = mysql_query($query) or die (mysql_error());
            }
        }
    }
}
        
//Cette requete permet d'obtenir tout sur le forum
$query = 'SELECT cat_id,
                cat_nom,
                forum.forum_id,
                forum_name,
                forum_desc,
                forum_post,
                forum_topic,
                auth_view,
                topic.topic_id,
                topic.topic_post,
                topic_last_post,
                post_id,
                post_time,
                post_createur,
                membre_pseudo,
                membre_id
                    FROM categorie
                    LEFT JOIN forum ON cat_id = forum_cat_id
                    LEFT JOIN post ON post_id = forum_last_post_id
                    LEFT JOIN topic ON topic.topic_id = post.topic_id
                    LEFT JOIN membres ON membre_id = post_createur
                    WHERE auth_view <= '.$level.'
                    ORDER BY cat_ordre, forum_ordre';
$result = mysql_query($query) or die (mysql_error());
if (mysql_num_rows($result) < 1)
{
    echo'Il n y a pas de forum. Allez en ajouter avec le panneau d administration !';
}
else
{
    $categorie = NULL;
    $table = false;
    while($data = mysql_fetch_assoc($result))
    {
        //Gestion de l'image à afficher
        $ico_mess = 'message.png';
        if ($_SESSION['connected'])
        {
            $query = 'SELECT topic_view.*, topic_last_post
                        FROM topic
                        LEFT JOIN forum ON forum.forum_id = topic.forum_id
                        LEFT OUTER JOIN topic_view ON forum.forum_id = tv_forum_id AND topic.topic_id = tv_topic_id AND tv_id = "'.intval($_SESSION['id']).'"
                        WHERE topic.forum_id = "'.$data['forum_id'].'"';
            $result2 = mysql_query($query) or die (mysql_error());

            while($data2 = mysql_fetch_assoc($result2))
            {
                if (!empty($data2['tv_id']) && $ico_mess != 'message_non_lu.png')
                {
                    if ($data2['tv_id'] == $_SESSION['id'] && $ico_mess != 'message_non_lu.png') //S'il a lu le topic
                    {
                        if ($data2['tv_poste'] == '0') // S'il n'a pas posté
                        {
                            if ($data2['tv_post_id'] == $data2['topic_last_post'] && ($ico_mess != 'messagep_non_lu.png' || $ico_mess != 'messagec_non_lus.png')) //S'il n'y a pas de nouveau message
                            {
                                $ico_mess = 'message.png';
                            }
                            else
                            {
                                if ($ico_mess != 'messagep_non_lu.png')
                                    $ico_mess = 'messagec_non_lus.png'; //S'il y a un nouveau message
                            }
                        }
                        else // S'il a  posté
                        {
                            if ($data2['tv_post_id'] == $data2['topic_last_post'] && ($ico_mess != 'messagep_non_lu.png' || $ico_mess != 'messagec_non_lus.png')) //S'il n'y a pas de nouveau message
                            {
                                $ico_mess = 'messagep_lu.png';
                            }
                            else //S'il y a un nouveau message
                            {
                                $ico_mess = 'messagep_non_lu.png';
                            }
                        }
                    }
                    else
                    {
                        $ico_mess = 'message_non_lu.png';
                    }
                }
                else
                {
                    $ico_mess = 'message_non_lu.png';
                }
            }
        }

        if (verif_auth($data['auth_view']))
        {
            if($categorie != $data['cat_id'])
            {
                $categorie = $data['cat_id'];
                if ($table)
                    echo '</table>';
                ?>

                <table>
                    <tr>
                        <th class="titre" ><?php echo stripslashes(htmlspecialchars($data['cat_nom'])); ?></th>
                        <th class="nombremessages" >Sujets</th>
                        <th class="nombresujets" >Messages</th>
                        <th class="derniermessage" >Dernier message</th>
                    </tr>

                <?php
                $table = true;
            }
            echo'<tr>';
            echo '<td class="titre" ><img src="./images/'.$ico_mess.'" alt="./images/'.$ico_mess.'" class="left" />';
            echo '<div><a href="?page=vf&f='.$data['forum_id'].'">'.stripslashes(htmlspecialchars($data['forum_name'])).'</a><br/>';
            echo '<span class="petit" >'.nl2br(stripslashes(htmlspecialchars($data['forum_desc']))).'&nbsp;</span></div></td>';
            echo '<td class="nombresujets" >'.$data['forum_topic'].'</td>';
            echo '<td class="nombremessages" >'.$data['forum_post'].'</td>';

            // Deux cas possibles :
            // Soit il y a un nouveau message, soit le forum est vide
            if (!empty($data['forum_post']))
            {
                //Selection dernier message
                $query = 'SELECT *
                            FROM config';
                $config = mysql_query($query) or die(mysql_error());
                while ($dataConfig = mysql_fetch_assoc($config))
                {
                    if ($dataConfig['config_nom'] == 'post_par_page')
                        $messageParPage = $dataConfig['config_valeur'];
                }
                $nbr_post = $data['topic_post'] +1;
                $page = ceil($nbr_post / $messageParPage);

                echo '<td class="derniermessage" >'.date('d/m/Y H:i',$data['post_time']).'<br/>';
                echo '<a href="?page=vp&m='.$data['membre_id'].'&action=consulter">'.stripslashes(htmlspecialchars($data['membre_pseudo'])).'</a>';
                echo '<a href="?page=vt&t='.$data['topic_id'].'&index='.$page.'"><img src="./images/go.png" alt="go" /></a></td>';
            }
            else
            {
                echo'<td class="nombremessages" >Pas de message</td>';
            }
            echo '</tr>';

        }
    }
    echo '</table>';
    if ($_SESSION['connected'])
    {
        echo '<p class="paragraphe" ><a href="?page=accueil&action=marquer" >Marquer tous les forums comme lus</a></p>';
    }
}
?>
