<?php
session_start();
session_name('creaftersawuikaz4ever');



include("includes/configbdd.php");
$mysqli = mysqli_connect($adresse, $nom, $motdepasse);
mysqli_select_db($mysqli, $database);

include("includes/config.php");
include("includes/fonctions.php");
include("includes/bbcode.php");

fix_magic_quotes();

if (!isset($_SESSION['connected']))
{
    $_SESSION['pseudo'] = '';
    $_SESSION['level'] = 1;
    $_SESSION['id'] = 0;
    $_SESSION['connected'] = false;
}

if (isset ($_COOKIE['pseudo']) && !$_SESSION['connected'])
{
    $query = 'SELECT membre_mdp,
                    membre_id,
                    membre_rang
                        FROM membres
                        WHERE membre_pseudo = "'.$_COOKIE['pseudo'].'"';
    $result = mysqli_query($mysqli, $query)or die (mysqli_error());
    $data = mysqli_fetch_assoc($result);
    if ($data['membre_mdp'] == $_COOKIE['password']) // Acces OK !
    {
        $_SESSION['pseudo'] = $_COOKIE['pseudo'];
        $_SESSION['level'] = $data['membre_rang'];
        $_SESSION['id'] = $data['membre_id'];
        $_SESSION['connected'] = true;
    }
}

$messageError = '';
if (isset($_POST['connexion']) && !$_SESSION['connected'])
{
    if (!empty($_POST['pseudo']) && !empty($_POST['password']))
    {
        $pseudo = mysqli_real_escape_string($_POST['pseudo']);
        $password = mysqli_real_escape_string($_POST['password']);

        $query = 'SELECT membre_mdp,
                        membre_id,
                        membre_rang,
                        membre_pseudo
                            FROM membres
                            WHERE membre_pseudo = "'.$pseudo.'"';
        $result = mysqli_query($mysqli, $query) or die (mysqli_error());
        $data = mysqli_fetch_assoc($result);

        if ($data['membre_mdp'] == md5($password)) // Acces OK !
        {
            if ($data['membre_rang'] == 0) //Le membre est banni
            {
                $messageError = 'Vous avez été banni, impossible de vous connecter sur ce forum';
            }
            else //Sinon c'est ok, on se connecte
            {
                $_SESSION['pseudo'] = $data['membre_pseudo'];
                $_SESSION['level'] = $data['membre_rang'];
                $_SESSION['id'] = $data['membre_id'];
                $_SESSION['connected'] = true;
                //cookie
                if (isset($_POST['souvenir']))
                {
                    $expire = time() + 365*24*3600;
                    setcookie('pseudo', $_SESSION['pseudo'], $expire);
                    setcookie('password', md5($password), $expire);
                }

                $page = $_POST['page'];
                /*****************
                 *
                 * redirection?
                 *
                 * ************************/
            }
        }
    }
}

if (isset($_POST['deconnexion']) && $_SESSION['connected'])
{
    if (isset($_COOKIE['pseudo']))
    {
        setcookie('pseudo', '', -1);
    }
    $_SESSION['pseudo'] = '';
    $_SESSION['level'] = 1;
    $_SESSION['id'] = 0;
    $_SESSION['connected'] = false;

    $query = 'DELETE FROM whosonline
                WHERE online_id = "'.intval($_SESSION['id']).'"';
    $result = mysqli_query($mysqli, $query) or die (mysqli_error());
}

$ip = ip2long($_SERVER['REMOTE_ADDR']);
$query = 'INSERT INTO whosonline
            VALUES("'.$_SESSION['id'].'",
                    "'.time().'",
                    "'.$ip.'")
            ON DUPLICATE KEY
                UPDATE online_time = "'.time().'" , online_id = "'.$_SESSION['id'].'"';
$result = mysqli_query($mysqli, $query) or die (mysqli_error());

$query = 'DELETE FROM whosonline
            WHERE online_time < '.TIME_MAX.'';
$result = mysqli_query($mysqli, $query) or die (mysqli_error());

include("displayManager.php");

mysqli_close($mysqli)
?>