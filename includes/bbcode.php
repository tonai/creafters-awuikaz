<?php
function code($texte)
{
    //Smileys
    $texte = str_replace(':)', '<img src="./images/smileys/smile.gif" title=":)" alt=":)" />', $texte);
    $texte = str_replace('^^', '<img src="./images/smileys/^^.gif" title="^^" alt="^^" />', $texte);
    $texte = str_replace(':D', '<img src="./images/smileys/biggrin.gif" title=":D" alt=":D" />', $texte);
    $texte = str_replace('XD', '<img src="./images/smileys/mdr.gif" title="XD" alt="XD"/>', $texte);
    $texte = str_replace(':he:', '<img src="./images/smileys/he.gif" title="he" alt="he" />', $texte);
    $texte = str_replace(':intello:', '<img src="./images/smileys/intello.gif" title="intello" alt="intello" />', $texte);
    $texte = str_replace(';)', '<img src="./images/smileys/wink2.gif" title=";)" alt=";)" />', $texte);
    $texte = str_replace(':p', '<img src="./images/smileys/tongue.gif" title=":p" alt=":p" />', $texte);
    $texte = str_replace(';p ', '<img src="./images/smileys/winktongue.gif" title=";p" alt=";p" />', $texte);
    $texte = str_replace('O_o', '<img src="./images/smileys/oh.gif" title="O_o" alt="O_o" />', $texte);
    $texte = str_replace(':eek:', '<img src="./images/smileys/eek.gif" title=":eek:" alt=":eek:" />', $texte);
    $texte = str_replace(':o', '<img src="./images/smileys/shocked.gif" title=":o" alt=":o" />', $texte);
    $texte = str_replace(':s', '<img src="./images/smileys/ouch.gif" title=":s" alt=":s" />', $texte);
    $texte = str_replace(':|', '<img src="./images/smileys/erf.gif" title=":|" alt=":|" />', $texte);
    $texte = str_replace(':aww:', '<img src="./images/smileys/aww.gif" title="aww" alt="aww" />', $texte);
    $texte = str_replace(':(', '<img src="./images/smileys/frown.gif" title=":(" alt=":(" />', $texte);
    $texte = str_replace(':bad:', '<img src="./images/smileys/bad.gif" title="bad" alt="bad" />', $texte);
    $texte = str_replace(':\'(', '<img src="./images/smileys/cry.gif" title=":\'(" alt=":\'(" />', $texte);
    $texte = str_replace(':clown:', '<img src="./images/smileys/clown.gif" title="clowms" alt="clowm" />', $texte);
    $texte = str_replace('B)', '<img src="./images/smileys/glasses.gif" title="B)" alt="B)" />', $texte);
    $texte = str_replace(':zzz:', '<img src="./images/smileys/zzz.gif" title="zzz" alt="zzz" />', $texte);
    $texte = str_replace(':kiss:', '<img src="./images/smileys/kiss.gif" title="kiss" alt="kiss" />', $texte);
    $texte = str_replace('è_é', '<img src="./images/smileys/mad.gif" title="è_é" alt="è_é" />', $texte);
    $texte = str_replace('$_$', '<img src="./images/smileys/money.gif" title="$_$" alt="$_$" />', $texte);
    $texte = str_replace(':star:', '<img src="./images/smileys/star.gif" title="star" alt="star" />', $texte);
    $texte = str_replace(':kado:', '<img src="./images/smileys/present.gif" title="kado" alt="kado" />', $texte);
    $texte = str_replace('<3', '<img src="./images/smileys/heart.gif" title="<3" alt="<3" />', $texte);
    $texte = str_replace('</3', '<img src="./images/smileys/unlove.gif" title="</3" alt="</3" />', $texte);
    $texte = str_replace(':idee:', '<img src="./images/smileys/idea.gif" title="idee" alt="idee" />', $texte);
    $texte = str_replace(':user:', '<img src="./images/smileys/user.gif" title="user" alt="user" />', $texte);
    $texte = str_replace(':happy:', '<img src="./images/smileys/happy.gif" title="happy" alt="happy" />', $texte);
    $texte = str_replace(':/ ', '<img src="./images/smileys/arf.gif" title=":/" alt=":/" />', $texte);
    $texte = str_replace(':jap:', '<img src="./images/smileys/jap.gif" title="jap" alt="jap" />', $texte);
    $texte = str_replace(':love:', '<img src="./images/smileys/love.gif" title="love" alt="love" />', $texte);
    $texte = str_replace(':666:', '<img src="./images/smileys/666.gif" title="666" alt="666" />', $texte);
    $texte = str_replace(':note:', '<img src="./images/smileys/note.gif" title="note" alt="note" />', $texte);
    //for modo
    $texte = str_replace(':!:', '<img src="./images/smileys/warn.gif" title="!" alt="!" />', $texte);
    $texte = str_replace('->', '<img src="./images/smileys/arrow.gif" title="->" alt="->" />', $texte);
    $texte = str_replace(':i:', '<img src="./images/smileys/info.gif" title="i" alt="i" />', $texte);

    //Mise en forme du texte
    //gras
    $texte = preg_replace('#\[g\](.+)\[/g\]#isU', '<strong>$1</strong>', $texte);
    //italique
    $texte = preg_replace('#\[i\](.+)\[/i\]#isU', '<em>$1</em>', $texte);
    //souligné
    $texte = preg_replace('#\[s\](.+)\[/s\]#isU', '<u>$1</u>', $texte);
    //lien
    $texte = preg_replace('#[^\[img\]|\[url=]http://[a-z0-9._/-]+#i', '<a href="$0" >$0</a>', $texte);
    //img
    $texte = preg_replace('#\[img\](.+)\[/img\]#isU', '<img src="$1" alt="$1" />', $texte);
    //url
    $texte = preg_replace('#\[url=(.+)\](.+)\[/url\]#isU', '<a href="$1" >$2</a>', $texte);
    //quote
    $texte = preg_replace('#\[quote=(.+)\](.+)\[/quote\]#isU', '<div class="quote" ><div class="quote_auteur" >Citation : $1 </div><div class="quote_message" >$2</div></div>', $texte);
    //color
    $texte = preg_replace('#\[color=(.+)\](.+)\[/color\]#isU', '<span style="color: $1 ;"> $2 </span>', $texte);

    return $texte;
}
?>
