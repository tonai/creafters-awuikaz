<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" >
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" media="screen" type="text/css" title="Design" href="./css/design.css" />
        <title>Forum Creafters Awuikaz</title>
    </head>
    <body>
        <div id="page" >
            <div id="connexion" >

<?php
$page = ((isset($_GET['page']))? stripslashes(htmlspecialchars($_GET['page'])): 'accueil');

if ($_SESSION['connected']) // Si le membre est connecté
{
    ?>
                    <form method="post" action="index.php">
                        <fieldset>
                            <legend><?php echo stripslashes(htmlspecialchars($_SESSION['pseudo'])); ?></legend>
                            <p>
    <?php
    echo '<ul>';
    echo '<li><a href="?page=vp&action=modifier">Modifier mon profil</a></li>';
    echo '<li><a href="?page=mp">messages privés</a></li>';
    if (verif_auth(LEVEL_MODO))
    {
        echo '<li><a href ="?page=admin">Administration</a></li>';
    }
    echo '</ul>';
    ?>

                            </p>
                            <p>
                                <input type="submit" value="Déconnexion" name="deconnexion" /><br/>
                                <?php echo $messageError; ?>
                            </p>
                        </fieldset>
                    </form>

    <?php
}
else // Sinon, on propose de se connecter ou de s'enregistrer
{
    ?>
                    <form method="post" action="index.php">
                        <fieldset>
                            <legend>Connexion</legend>
                            <p>
                                <label for="pseudo" >Pseudo :</label><input name="pseudo" type="text" id="pseudo" /><br />
                                <label for="password" >Password :</label><input type="password" name="password" id="password" /><br />
                            </p>
                            <p class="memory" >
                                <label>Se souvenir de moi ?</label><input type="checkbox" name="souvenir" /><br />
                            </p>
                            <p class="center" >
                                <input type="hidden" name="page" value="<?php echo $page; ?>" />
                                <input type="submit" value="Connexion" name="connexion" />
                            </p>
                            <p class="center" >
                                <a href="?page=register">Pas encore inscrit ?</a><br/>
                            </p>
                            <p class="error" >
                                <?php echo $messageError; ?>
                            </p>
                        </fieldset>
                    </form>
    <?php
}
echo '</div>';
echo '<div id="banniere"></div>';
echo '<div id="corps">';


// fil d'Arianne
include ('fildarianne.php');

echo '<div id="corps_forum">';
switch ($page)
{
    case 'accueil':
        include("accueil.php");
        break;

    case 'mp':
        include("messages_prives.php");
        break;

    case 'poster':
        include("poster.php");
        break;

    case 'register':
        include("register.php");
        break;

    case 'vf':
        include("voir_forum.php");
        break;

    case 'vp':
        include("voir_profil.php");
        break;

    case 'vt':
        include("voir_topic.php");
        break;

    case 'vi':
        include("verif_inscription.php");
        break;

    case 'admin':
        include("admin.php");
        break;

    default :
        echo '<p>La page que vous demandez n\'existe pas</p>';
        break;
}
echo '</div>';

$query = 'SELECT count(post_id) AS nbMessages
            FROM post';
$result = mysql_query($query) or die (mysql_error());
$data = mysql_fetch_assoc($result);
$nbMessage = $data['nbMessages'];

$query = 'SELECT count(membre_id) AS nbMembres
                    FROM membres';
$result = mysql_query($query) or die (mysql_error());
$data = mysql_fetch_assoc($result);
$nbMembres = $data['nbMembres'];

$query = "SELECT membre_pseudo,
                membre_id
                    FROM membres
                    ORDER BY membre_id DESC
                    LIMIT 0, 1";
$result = mysql_query($query) or die (mysql_error());
$data = mysql_fetch_assoc($result);
$derniermembre = stripslashes(htmlspecialchars($data['membre_pseudo']));

$count_online = 0;
$query = 'SELECT COUNT(*) AS nbr_visiteurs
            FROM whosonline
            WHERE online_id = 0';
$result = mysql_query($query) or die (mysql_error());
$count_visiteurs = mysql_result($result,0);

$query = 'SELECT membre_id,
                membre_pseudo
                    FROM whosonline
                    LEFT JOIN membres ON membre_id = online_id
                    WHERE online_time > '.TIME_MAX;
$result = mysql_query($query) or die (mysql_error());
$count_membres = mysql_num_rows($result);

$count_online = $count_visiteurs + $count_membres;

?>
            <div id="footer" >
                <table>
                    <tr>
                        <td>
                            <img src="images/message.png" alt="message" class="left" />
                            <div>Pas de nouveau message</div>
                        </td>
                        <td>
                            <img src="images/message_non_lu.png" alt="message" class="left" />
                            <div>Nouveau(x) message(s)</div>
                        </td>
                        <td class="w20" >
                            <img src="images/locked.png" alt="message" class="left" />
                            <div>Topic fermé</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <img src="images/messagep_lu.png" alt="message" class="left" />
                            <div>Pas de nouveau message<br/>
                            Vous avez posté/répondu</div>
                        </td>
                        <td>
                            <img src="images/messagep_non_lu.png" alt="message" class="left" />
                            <div>Nouveau(x) message(s)<br/>
                            Vous avez posté/répondu</div>
                        </td>
                        <td class="w20" >
                            <img src="images/postit.png" alt="message" class="left" />
                            <div>Post-it</div>
                        </td>
                    </tr>
                </table>
<?php
echo '<p>Il y a '.$count_online.' connectés ('.$count_membres.' membres et '.$count_visiteurs.' invités)<br/>';
echo 'Liste des personnes en ligne : ';
while ($data = mysql_fetch_assoc($result))
{
    echo '<a href="?page=vp&m='.$data['membre_id'].'&action=consulter">'.stripslashes(htmlspecialchars($data['membre_pseudo'])).'</a> ';
}
echo '<br/>';
echo 'Le total des messages du forum est de <strong>'.$nbMessage.'</strong>.<br/>';
echo 'le forum compte <strong>'.$nbMembres.'</strong> membres.<br/>';
echo 'Le dernier membre enregistré est : <a href="?page=vp&m='.$data['membre_id'].'&action=consulter">'.$derniermembre.'</a>.</p>';
?>
                <div class="clear" ></div>
            </div>
        </div>
        <script type="text/javascript" >
        function bbcode(bbdebut, bbfin)
        {
            var input = window.document.formulaire.message;
            input.focus();
            if(typeof document.selection != 'undefined')
            {
                var range = document.selection.createRange();
                var insText = range.text;
                range.text = bbdebut + insText + bbfin;
                range = document.selection.createRange();
                if (insText.length == 0)
                {
                    range.move('character', -bbfin.length);
                }
                else
                {
                    range.moveStart('character', bbdebut.length + insText.length + bbfin.length);
                }
                range.select();
            }
            else if(typeof input.selectionStart != 'undefined')
            {
                var start = input.selectionStart;
                var end = input.selectionEnd;
                var insText = input.value.substring(start, end);
                input.value = input.value.substr(0, start) + bbdebut + insText + bbfin + input.value.substr(end);
                var pos;
                if (insText.length == 0)
                {
                    pos = start + bbdebut.length;
                }
                else
                {
                    pos = start + bbdebut.length + insText.length + bbfin.length;
                }
                input.selectionStart = pos;
                input.selectionEnd = pos;
            }
            else
            {
                var pos;
                var re = new RegExp('^[0-9]{0,3}$');
                while(!re.test(pos))
                {
                    pos = prompt("insertion (0.." + input.value.length + "):", "0");
                }
                if(pos > input.value.length)
                {
                    pos = input.value.length;
                }
                var insText = prompt("Veuillez taper le texte");
                input.value = input.value.substr(0, pos) + bbdebut + insText + bbfin + input.value.substr(pos);
            }
        }

        function smilies(img)
        {
            window.document.formulaire.message.value += '' + img + '';
        }

        function citer(id, auteur)
        {
            var xhr;
            try
            {
                xhr = new ActiveXObject('Msxml2.XMLHTTP');
            }
            catch (e)
            {
                try
                {
                    xhr = new ActiveXObject('Microsoft.XMLHTTP');
                }
                catch (e2)
                {
                    try
                    {
                        xhr = new XMLHttpRequest();
                    }
                    catch (e3)
                    {
                        xhr = false;
                    }
                }
            }


            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && (xhr.status == 200 || xhr.status == 0)) {
                    readData(xhr.responseText, auteur);
                    //document.getElementById("loader").style.display = "none";
                } else if (xhr.readyState < 4) {
                    //document.getElementById("loader").style.display = "inline";
                }
            };

            xhr.open("POST", "ajax.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send("id=" + encodeURIComponent(id));
        }

        function readData (content, auteur)
        {
            var input = window.document.formulaire.message;
            var bbdebut = '[quote=' + auteur + ']';
            var bbfin = '[/quote]';
            input.focus();

            if(typeof document.selection != 'undefined')
            {
                var range = document.selection.createRange();
                var insText = range.text;
                range.text = bbdebut + content + bbfin;
                range = document.selection.createRange();
                if (insText.length == 0)
                {
                    range.move('character', 0);
                }
                else
                {
                    range.moveStart('character', bbdebut.length + content.lenght + bbfin.length);
                }
                range.select();
            }
            else if(typeof input.selectionStart != 'undefined')
            {
                var start = input.selectionStart;
                var end = input.selectionEnd;
                var insText = input.value.substring(start, end);
                input.value = input.value.substr(0, start) + bbdebut + content + bbfin + input.value.substr(end);
                var pos;
                if (insText.length == 0)
                {
                    pos = start + bbdebut.length;
                }
                else
                {
                    pos = start + bbdebut.length + content.length + bbfin.length;
                }
                input.selectionStart = pos;
                input.selectionEnd = pos;
            }
            else
            {
                var pos;
                var re = new RegExp('^[0-9]{0,3}$');
                while(!re.test(pos))
                {
                    pos = prompt("insertion (0.." + input.value.length + "):", "0");
                }
                if(pos > input.value.length)
                {
                    pos = input.value.length;
                }
                input.value = input.value.substr(0, pos) + bbdebut + content + bbfin + input.value.substr(pos);
            }
        }
        </script>
    </body>
</html>
