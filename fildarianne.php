<?php
echo '<div id="navigation" >';
echo '<p class="left" >';

if ($page == 'accueil')
    echo 'Accueil';
else
    echo '<a href="index.php" >Accueil</a>';

switch ($page)
{
    case 'mp':
        if (!isset($_GET['action']))
        {
            echo ' > Messagerie';
        }
        else
        {
            echo ' > <a href="?page='.$page.'" >Messagerie</a>';
            switch ($_GET['action'])
            {
                case 'consulter':
                    if (isset($_GET['mp']))
                    {
                        // amélioration : titre message
                        echo ' > Consulter un message';
                    }
                    break;

                case 'nouveau':
                    if (isset($_GET['m']))
                    {
                        // amélioration : écrire à
                        echo ' > Ecrire un message';
                    }
                    elseif (isset($_GET['mp']))
                    {
                        // amélioration : répondre à
                        echo ' > Répondre à un message';
                    }
                    else
                    {
                        echo ' > Ecrire un message';
                    }
                    break;

                case 'supprimer':
                    if (isset($_GET['mp']))
                    {
                        // amélioration : titre message
                        echo ' > Supprimer un message';
                    }
                    break;
            }
        }
        echo '</p>';
        break;

    case 'register':
        echo ' > Inscription';
        echo '</p>';
        break;

    case 'vf':
        if (isset($_GET['f']))
        {
            $query = 'SELECT forum_name,
                            forum_topic
                        FROM forum
                        WHERE forum_id = "'.intval($_GET['f']).'"';
            $result = mysql_query($query) or die (mysql_error());
            if ($data = mysql_fetch_assoc($result))
            {
                if (isset($_GET['action']))
                {
                    echo ' > <a href="?page='.$page.'&f='.$_GET['f'].'" >'.stripslashes(htmlspecialchars($data['forum_name'])).'</a>';
                    switch ($_GET['action'])
                    {
                        case 'nouveau_topic':
                            echo ' > Créer un nouveau topic';
                            break;

                        case 'nouveau_sondage':
                             echo ' > Créer un nouveau sondage';
                            break;
                    }
					echo '</p>';
                }
                else
                {
                    echo ' > '.stripslashes(htmlspecialchars($data['forum_name'])).'</p>';

                    $query = 'SELECT *
                                FROM config';
                    $config = mysql_query($query) or die(mysql_error());
                    while ($dataConfig = mysql_fetch_assoc($config))
                    {
                        if ($dataConfig['config_nom'] == 'topic_par_page')
                            $messageParPage = $dataConfig['config_valeur'];
                    }
                    $totalDesMessages = $data['forum_topic'] + 1;

                    if (!isset($_GET['index']))
                        $pageActuelle=1;
                    else
                        $pageActuelle=$_GET['index'];

                    $pagesTotales=ceil(($totalDesMessages)/$messageParPage);
                    $pages=$pagesTotales;
					
                    echo '<div id="index" >';
                    if ($pageActuelle!=1)
                    {
                        $pagePrec=$pageActuelle-1;
                        echo '<a href="?page='.$page.'&t='.$_GET['t'].'&index='.$pagePrec.' title="page précédante" ><</a>';
                        echo '<a href="?page='.$page.'&t='.$_GET['t'].'&index=1" title="première page" >1</a>';
                    }
                    else
                    {
                        echo '<div>1</div>';
                    }
                    $i=2;
                    if ($pageActuelle<=5)
                    {
                        $i=2;
                        if ($pages>9)
                            $pages=9;
                    }
                    elseif ($pageActuelle>=($pagesTotales-4) and $pageActuelle>5)
                    {
                        if ($pagesTotales>=6)
                            $i=$pagesTotales-7;
                    }
                    else
                    {
                        $i=$pageActuelle-3;
                        $pages=$pageActuelle+3;
                    }
                    for ($i;$i<$pages;$i++)
                    {
                        if ($pageActuelle!=$i)
                            echo '<a href="?page='.$page.'&t='.$_GET['t'].'&index='.$i.'" >'.$i.'</a>';
                        else
                            echo '<div>'.$i.'</div>';
                    }
                    if ($pagesTotales!=1)
                    {
                        if ($pageActuelle!=$pagesTotales)
                            echo '<a href="?page='.$page.'&t='.$_GET['t'].'&index='.$pagesTotales.'" title="dernière page" >'.$pagesTotales.'</a>';
                        else
                            echo '<div>'.$pagesTotales.'</div>';
                    }
                    if ($pageActuelle!=$pagesTotales)
                    {
                        $pageSuiv=$pageActuelle+1;
                        echo '<a href="?page='.$page.'&t='.$_GET['t'].'&index='.$pageSuiv.'" title="page suivante" >></a>';
                    }
					echo '</div>';
                }
            }
        }
        break;

    case 'vt':
        if (isset($_GET['t']))
        {
            $query = 'SELECT forum_name,
                            forum.forum_id,
                            topic_titre,
                            topic_post
                                FROM forum
                                LEFT JOIN topic ON forum.forum_id = topic.forum_id
                                WHERE topic_id = "'.intval($_GET['t']).'"';
            $result = mysql_query($query) or die (mysql_error());
            if ($data = mysql_fetch_assoc($result))
            {
                if (isset($_GET['action']))
                {
                    echo ' > <a href="?page=vf&f='.$data['forum_id'].'" >'.stripslashes(htmlspecialchars($data['forum_name'])).'</a> > <a href="?page='.$page.'&t='.$_GET['t'].'" >'.stripslashes(htmlspecialchars($data['topic_titre'])).'</a>';
                    switch ($_GET['action'])
                    {
                        case 'repondre':
                            if (isset($_GET['t']))
                            {
                                // amélioration : titre topic
                                echo ' > Repondre à un topic';
                            }
                            break;

                        case 'edit':
                            if (isset($_GET['p']) && isset($_GET['t']))
                            {
                                echo ' > Editer un post';
                            }
                            break;

                        case 'delete':
                            if (isset($_GET['p']) && isset($_GET['t']))
                            {
                                echo ' > Supprimer un post';
                            }
                            break;

                        case 'deletetopic':
                            if (isset($_GET['t']))
                            {
                                echo ' > Supprimer un topic';
                            }
                            break;

                        case 'lock':
                            if (isset($_GET['t']))
                            {
                                echo ' > Vérouiller un topic';
                            }
                            break;

                        case 'unlock':
                            if (isset($_GET['t']))
                            {
                                echo ' > Dévérouiller un topic';
                            }
                            break;

                        case 'deplacer':
                            if (isset($_GET['t']))
                            {
                                echo ' > Déplacer un topic';
                            }
                            break;
                    }
                    echo '</p>';
                }
                else
                {
                    echo ' > <a href="?page=vf&f='.$data['forum_id'].'" >'.stripslashes(htmlspecialchars($data['forum_name'])).'</a> > '.stripslashes(htmlspecialchars($data['topic_titre'])).'</p>';

                    $query = 'SELECT *
                                FROM config';
                    $config = mysql_query($query) or die(mysql_error());
                    while ($dataConfig = mysql_fetch_assoc($config))
                    {
                        if ($dataConfig['config_nom'] == 'post_par_page')
                            $messageParPage = $dataConfig['config_valeur'];
                    }
                    $totalDesMessages = $data['topic_post'] + 1;

                    if (!isset($_GET['index']))
                        $pageActuelle=1;
                    else
                        $pageActuelle=$_GET['index'];

                    $pagesTotales=ceil(($totalDesMessages)/$messageParPage);
                    $pages=$pagesTotales;

                    echo '<div id="index" >';
                    if ($pageActuelle!=1)
                    {
                        $pagePrec=$pageActuelle-1;
                        echo '<a href="?page='.$page.'&t='.$_GET['t'].'&index='.$pagePrec.' title="page précédante" ><</a>';
                        echo '<a href="?page='.$page.'&t='.$_GET['t'].'&index=1" title="première page" >1</a>';
                    }
                    else
                    {
                        echo '<div>1</div>';
                    }
                    $i=2;
                    if ($pageActuelle<=5)
                    {
                        $i=2;
                        if ($pages>9)
                            $pages=9;
                    }
                    elseif ($pageActuelle>=($pagesTotales-4) and $pageActuelle>5)
                    {
                        if ($pagesTotales>=6)
                            $i=$pagesTotales-7;
                    }
                    else
                    {
                        $i=$pageActuelle-3;
                        $pages=$pageActuelle+3;
                    }
                    for ($i;$i<$pages;$i++)
                    {
                        if ($pageActuelle!=$i)
                            echo '<a href="?page='.$page.'&t='.$_GET['t'].'&index='.$i.'" >'.$i.'</a>';
                        else
                            echo '<div>'.$i.'</div>';
                    }
                    if ($pagesTotales!=1)
                    {
                        if ($pageActuelle!=$pagesTotales)
                            echo '<a href="?page='.$page.'&t='.$_GET['t'].'&index='.$pagesTotales.'" title="dernière page" >'.$pagesTotales.'</a>';
                        else
                            echo '<div>'.$pagesTotales.'</div>';
                    }
                    if ($pageActuelle!=$pagesTotales)
                    {
                        $pageSuiv=$pageActuelle+1;
                        echo '<a href="?page='.$page.'&t='.$_GET['t'].'&index='.$pageSuiv.'" title="page suivante" >></a>';
                    }
                    echo '</div>';
                }
            }
        }
        break;

    case 'vp':
        if (isset($_GET['action']))
        {
            switch ($_GET['action'])
            {
                case 'consulter':
                    if (isset($_GET['m']))
                    {
                        echo ' > Consulter un profil';
                    }
                    break;

                case 'modifier':
                    echo ' > Modifier son profil';
                    break;
            }
        }
        echo '</p>';
        break;

    case 'vi':
        echo ' > Validation de l\'insciption';
        echo '</p>';
        break;

    case 'admin':
        if (!isset($_GET['cat']))
        {
            echo ' > Administration';
        }
        else
        {
            echo ' > <a href="?page='.$page.'" >Administration</a>';
            switch ($_GET['cat'])
            {
                case 'config':
                    echo ' > Configuration';
                    break;

                case 'creer_forum':
                    echo ' > Créer un forum';
                    break;

                case 'creer_categorie':
                    echo ' > Créer une catégorie';
                    break;

                case 'edit_forum':
                    echo ' > Editer un forum';
                    break;

                case 'droits_forum':
                    echo ' > Editer les droits d\'un forum';
                    break;

                case 'edit_categorie':
                    echo ' > Editer les droits d\'un forum';
                    break;

                case 'membres':
                    if (!isset($_GET['membre']))
                        echo ' > Gestion des membres';
                    else
                    {
                        echo ' > <a href="?page='.$page.'&cat='.$_GET['cat'].'" >Gestion des membres</a>';
                        echo ' > Editer profil';
                    }
                    break;
            }
        }
        echo '</p>';
        break;
}
echo '</div>';
?>