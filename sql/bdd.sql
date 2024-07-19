-- phpMyAdmin SQL Dump
-- version 2.11.3
-- http://www.phpmyadmin.net
--
-- Serveur: creaftersawuikaz.sql.free.fr
-- G�n�r� le : Sam 06 Juin 2009 � 14:41
-- Version du serveur: 5.0.67
-- Version de PHP: 5.2.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Base de donn�es: `creaftersawuikaz`
--

-- --------------------------------------------------------

--
-- Structure de la table `categorie`
--

CREATE TABLE IF NOT EXISTS `categorie` (
  `cat_id` int(11) NOT NULL auto_increment,
  `cat_nom` varchar(50) character set latin1 collate latin1_general_ci NOT NULL,
  `cat_ordre` int(11) NOT NULL,
  PRIMARY KEY  (`cat_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Contenu de la table `categorie`
--

INSERT INTO `categorie` (`cat_id`, `cat_nom`, `cat_ordre`) VALUES
(1, 'Place publique', 1),
(2, 'Forums des Guildeux', 2),
(3, 'Forums modérateurs', 4),
(4, 'Forums VIPs', 3);

-- --------------------------------------------------------

--
-- Structure de la table `config`
--

CREATE TABLE IF NOT EXISTS `config` (
  `config_nom` varchar(100) NOT NULL,
  `config_valeur` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `config`
--

INSERT INTO `config` (`config_nom`, `config_valeur`) VALUES
('avatar_maxsize', '100000'),
('avatar_maxh', '100 '),
('avatar_maxl', '100 '),
('sign_maxl', '200'),
('auth_bbcode_sign', '1'),
('pseudo_maxsize', '30'),
('pseudo_minsize', '0'),
('topic_par_page', '10'),
('post_par_page', '10'),
('pass_minsize', '8');

-- --------------------------------------------------------

--
-- Structure de la table `forum`
--

CREATE TABLE IF NOT EXISTS `forum` (
  `forum_id` int(11) NOT NULL auto_increment,
  `forum_cat_id` mediumint(8) NOT NULL,
  `forum_name` varchar(50) character set latin1 collate latin1_general_ci NOT NULL,
  `forum_desc` text character set latin1 collate latin1_general_ci NOT NULL,
  `forum_ordre` mediumint(8) NOT NULL,
  `forum_last_post_id` int(11) NOT NULL,
  `forum_topic` mediumint(8) NOT NULL,
  `forum_post` mediumint(8) NOT NULL,
  `auth_view` tinyint(4) NOT NULL default '1',
  `auth_post` tinyint(4) NOT NULL default '2',
  `auth_topic` tinyint(4) NOT NULL default '2',
  `auth_annonce` tinyint(4) NOT NULL default '5',
  `auth_modo` tinyint(4) NOT NULL default '5',
  `auth_sondage` int(11) NOT NULL default '3',
  PRIMARY KEY  (`forum_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;

--
-- Contenu de la table `forum`
--

INSERT INTO `forum` (`forum_id`, `forum_cat_id`, `forum_name`, `forum_desc`, `forum_ordre`, `forum_last_post_id`, `forum_topic`, `forum_post`, `auth_view`, `auth_post`, `auth_topic`, `auth_annonce`, `auth_modo`, `auth_sondage`) VALUES
(1, 1, 'Who’s who’s land', 'Présentation des personnages des membres de la guilde', 3, 0, 0, 0, 1, 3, 3, 5, 5, 5),
(2, 1, 'Règlement de la guilde', '', 1, 7, 7, 7, 1, 5, 5, 5, 5, 5),
(3, 1, 'Recrutement', 'Candidats �  l’entrée dans notre guilde d’artisans créateurs, soyez les bienvenus !', 2, 8, 1, 1, 1, 2, 2, 5, 5, 5),
(4, 2, 'A la taverne du bâton rompu', 'discusses de tout et de rien', 1, 0, 0, 0, 3, 3, 3, 5, 5, 3),
(5, 2, 'Café du commerce', '', 2, 0, 0, 0, 3, 3, 3, 5, 5, 3),
(6, 2, 'Codes, maisons et coffres', '', 3, 0, 0, 0, 3, 3, 3, 5, 5, 3),
(7, 2, 'Elevage', '', 4, 0, 0, 0, 3, 3, 3, 5, 5, 3),
(8, 2, 'Rendez-vous sur la colline', '', 5, 0, 0, 0, 3, 3, 3, 5, 5, 3),
(9, 4, 'Salon bleu', 'pour taper la discute IRL ou pas', 1, 0, 0, 0, 4, 4, 4, 5, 5, 4),
(10, 4, 'Votes pour candidatures', '', 2, 0, 0, 0, 4, 4, 5, 5, 5, 5),
(11, 3, 'forum de modération', '', 1, 0, 0, 0, 5, 5, 5, 6, 6, 5);

-- --------------------------------------------------------

--
-- Structure de la table `membres`
--

CREATE TABLE IF NOT EXISTS `membres` (
  `membre_id` int(11) NOT NULL auto_increment,
  `membre_pseudo` varchar(50) character set latin1 collate latin1_general_ci NOT NULL,
  `membre_mdp` varchar(32) character set latin1 collate latin1_general_ci NOT NULL,
  `membre_email` varchar(100) character set latin1 collate latin1_general_ci NOT NULL,
  `membre_msn` varchar(100) character set latin1 collate latin1_general_ci NOT NULL,
  `membre_siteweb` varchar(100) character set latin1 collate latin1_general_ci NOT NULL,
  `membre_avatar` varchar(100) character set latin1 collate latin1_general_ci NOT NULL,
  `membre_signature` varchar(250) character set latin1 collate latin1_general_ci NOT NULL,
  `membre_localisation` varchar(100) character set latin1 collate latin1_general_ci NOT NULL,
  `membre_inscrit` int(11) NOT NULL,
  `membre_derniere_visite` int(11) NOT NULL,
  `membre_rang` tinyint(4) NOT NULL default '2',
  `membre_post` int(11) NOT NULL,
  `membre_verif` int(11) NOT NULL default '0',
  `membre_code` int(11) NOT NULL default '0',
  `membre_occup` varchar(100) NOT NULL,
  `membre_email_visible` int(11) NOT NULL default '0',
  PRIMARY KEY  (`membre_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Contenu de la table `membres`
--

INSERT INTO `membres` (`membre_id`, `membre_pseudo`, `membre_mdp`, `membre_email`, `membre_msn`, `membre_siteweb`, `membre_avatar`, `membre_signature`, `membre_localisation`, `membre_inscrit`, `membre_derniere_visite`, `membre_rang`, `membre_post`, `membre_verif`, `membre_code`, `membre_occup`, `membre_email_visible`) VALUES
(1, 'Lord-Azrael', '37f0de6944176a3f7bad42c410a2a99c', 'tonai59@hotmail.fr', '', 'creaftersawuikaz.free.fr', '1244291869.png', '', 'monde des 12', 1242036158, 1242036158, 6, 19, 1, 737115800, 'faire un forum', 0),
(2, 'modo', '098f6bcd4621d373cade4e832627b4f6', '', '', '', '', '', '', 0, 0, 5, 0, 1, 0, '', 0);

-- --------------------------------------------------------

--
-- Structure de la table `mp`
--

CREATE TABLE IF NOT EXISTS `mp` (
  `mp_id` int(11) NOT NULL auto_increment,
  `mp_expediteur` int(11) NOT NULL,
  `mp_receveur` int(11) NOT NULL,
  `mp_titre` varchar(100) character set latin1 collate latin1_general_ci NOT NULL,
  `mp_text` text character set latin1 collate latin1_general_ci NOT NULL,
  `mp_time` int(11) NOT NULL,
  `mp_lu` enum('0','1') character set latin1 collate latin1_general_ci NOT NULL,
  PRIMARY KEY  (`mp_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Contenu de la table `mp`
--


-- --------------------------------------------------------

--
-- Structure de la table `post`
--

CREATE TABLE IF NOT EXISTS `post` (
  `post_id` int(11) NOT NULL auto_increment,
  `post_createur` int(11) NOT NULL,
  `post_texte` text character set latin1 collate latin1_general_ci NOT NULL,
  `post_time` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `post_forum_id` int(11) NOT NULL,
  PRIMARY KEY  (`post_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

--
-- Contenu de la table `post`
--

INSERT INTO `post` (`post_id`, `post_createur`, `post_texte`, `post_time`, `topic_id`, `post_forum_id`) VALUES
(1, 1, '- Les Creafters Awuikaz (entendez ‘crafteurs créatifs �  huit cases’) est une guilde d’artisans qui a vu le jour sur Danathor durant l’été 2008. Neutre au départ, elle s’est bontarisée par la force des choses, mais les brakmariens y sont toujours les bienvenus.\r\n\r\n- La guilde est menée conjointement par Lord-Azrael et Leoferraie. Ce sont eux, et personne d’autre, qui ont le dernier mot au sujet du bon fonctionnement de cette guilde, et de la résolution des conflits inhérents �  celle-ci.\r\n\r\n- Un respect des autres joueurs du serveur et du règlement de la guilde sont les conditions sine qua non du maintient d’un perso au sein de notre guilde.\r\n\r\n- Les Creafters respectent la vie IRL et IG des joueurs de Danathor. Ils ne volent pas, ne trichent pas, ne hackent pas, n’arnaquent pas. Quiconque ne respectera pas ce point sera banni sans autre forme de procès.', 1243955223, 1, 2),
(2, 1, '- Il va sans dire que la guilde a été créée pour activer de l’entraide parmi les joueurs sympathiques et de bonne volonté. Cependant, gardons �  l’esprit qu’il existe une différence fondamentale entre ‘aide’ et ‘entraide’. Toutes les demandes d’xp, de drop et autres ne pourront être contentées.\r\n\r\n- Dans le même ordre d’idée, n’exagérez pas avec la demande en ressources. Ce n’est pas parce que Machin est maitre-bucheron qu’il devra obligatoirement vous fournir en orme sur simple demande. La récolte prend énormément de temps, même �  haut niveau.\r\nDe plus si certaines demandes de ressources aux récolteurs de la guilde sont justifiées pour un craft qui servira au demandeur (et donc �  la guilde), d’autres le sont beaucoup moins (Il est inacceptable de demander ne fut-ce que 20 bambous sombres pour crafter un truc qu’on met illico en hdv par exemple).', 1243955268, 2, 2),
(3, 1, '- Le recrutement se fera �  partir de ce jour uniquement sur le forum. Seuls les personnages de niveau 60 ou ayant un métier utile (les boulangers courent les rues) et HL (�  voir en fonction du métier) seront acceptés comme candidats.\r\n\r\n- OSEF du niveau ! Nous préférons de loin jouer avec un Crâ ultra sympathique de niveau 70 qu’avec un Sadi roxxor 178 soupe-au-lait, arrogant et vantard.\r\n\r\n- Si le recrutement n’est pas fermé aux plus jeunes d’entre nous, il est évident qu’une certaine maturité apporte plus de stabilité et de paix �  cette guilde. Ce critère sera aussi pris en compte dans l’acceptation des nouveaux membres.', 1243955308, 3, 2),
(4, 1, '- Faire XP la guilde sert bien évidemment �  rendre les percos plus résistants, mais aussi �  vous faire gagner plus de droits au sein de celle-ci.\r\n\r\n- Il est inutile de demander ‘tel grade’ avec ‘tel droit’. Le passage de grades se fait ‘automatiquement’ selon votre implication dans le jeu.\r\n\r\n- Le système de grades a été revu en fonction :\r\n  * Du niveau moyen des personnages de la guilde\r\n  * De votre niveau de personnage\r\n  * De votre xp guilde\r\n\r\n- On exception pourra être faite pour les personnages de bas niveaux qui auront xp un métier digne de ce nom (crafteur niveau 80 par exemple). Ces personnages pourront être acceptés avec le grade d’Artisan au sein de la guilde.', 1243955379, 4, 2),
(5, 1, 'Le canal guilde ne devra comporter que des messages concernant la guilde entière. Les messages �  caractère commerciaux sont interdits (sauf recherche ou offre exceptionnelle).  Dans la mesure du raisonnable, les messages �  caractère personnel seront évités (« Ouah eh t’as vu Melodie Trucmuche today en classe elle portait une robe �  pois bleus trop naze la meuf »). Autrement dit, utilisez les canaux �  bon escient : le mp et le canal groupe sont faits pour ça.', 1243955401, 5, 2),
(6, 1, '- Le système mis en place par Ankama pour les enclos ne nous permettant pas de garantir la sécurité de nos objets d’élevage, ainsi que de nos dindes, nous avons décidé :\r\n  * Que pour un accès simple �  l’enclos, sans toucher aux objets, les membres de la guilde devront avoir atteint les 50.000 points d’xp guilde.\r\n  * Que pour un accès �  l’enclos avec déplacement des objets, le personnage concerné devra avoir atteint les 50.000 points d’xp guilde ET s’acquitter  de la somme de 50.000 kamas payables �  Lord-Azrael (ceci afin d’éviter les vols de matériel).\r\n\r\n- Seul l’enclos de Bonta est accessible aux membres de la guilde.\r\n\r\n- Le partage de cet enclos devra se faire via l’horaire défini ici.\r\n\r\n- Apres utilisation de l’enclos, l’éleveur devra retirer toutes ses dindes de l’enclos pour laisser la place libre aux autres utilisateurs.\r\nSi ce point n’est pas respecté, les dragodindes concernées pourront être �  tout moment retirées de l’enclos par Lord-Azrael ou Leoferraie.', 1243955458, 6, 2),
(7, 1, '- Toute personne ayant acheté une maison pourra la mettre �  disposition de la guilde (sans obligation aucune). Déconnecter dans la maison vous permettra de récupérer de l’énergie plus rapidement que dans la rue.\r\n\r\n- Le coffre du deuxième étage dans la maison d’Astrub (appartenant �  Leoferraie) pour être utilisé pour des collaborations.\r\n\r\nExemples :\r\n* X est bucheron, Y est mineur, Z est paysan et ils veulent se faire profiter mutuellement de leurs talent, ils y mettent communément leurs ressources, et, dans la mesure du raisonnable, ils se servent des ressources stockées par leurs collaborateurs.\r\n* J’ai une pano picpic qui ne me sert pas vraiment mais elle pourrait servir �  l’xp d’un perso secondaire, je la mets donc �  disposition de la guilde en créant un topic sur le forum et en demandant simplement aux personnes qui l’empruntent de le signaler sur ce topic.\r\n\r\nEst-il utile de rappeler que les mêmes règles de bonne conduite et de politesse ont cours ici ? Si vous tapez dans les ressources d’un autre, vous le lui dites par mp sur le forum ou en jeu.\r\n\r\nPour obtenir le code de ce coffre, il faudra obligatoirement avoir montré patte blanche : c� d avoir passé plus de 6 mois au sein de la guilde en ayant été tout ni plus ni moins irréprochable et sympathique.\r\n\r\nSeuls Lord-Azrael et Léoferraie seront habilités �  donner l’accès �  ce coffre.', 1243955529, 7, 2),
(8, 1, '- Pour poster votre candidature, créez un sujet, et présentez-vous en jouant votre rôle (RP). Rappelons que si le sms est permis en jeu pour des raisons évidentes de rapidité d’action, il ne l’est pas sur ce forum.\r\n\r\nExemple d’une bonne candidature :\r\n[i]Salut la compagnie !\r\nJe m’appelle Souisouatche, je suis un Xelor de 81eme cercle, et je cherche aujourd’hui des compagnons de route qui pourraient m’aider �  assouvir ma soif de donjons. Je suis un véritable tueur. Si, si, tuer le temps, rien ne m’amuse plus.\r\nComme tout bon adepte de l’eau qui se respecte, je suis féru de mers et de lacs… et de pichons. Je serais d’ailleurs ravi de vous faire profiter �  tous des merveilleux requins que je pêche des heures durant sur les rives de notre bonne vieille Sufokia - durant mes heures perdues, cela va de soi.\r\nJe suis passé maitre poissonnier, je balaye, je fais la cuisine, et accessoirement, je ralentis la cadence durant les combats.\r\nAu plaisir de vous croiser dans les rues de Bonta,\r\nAmicalement,\r\n\r\nSouisouatche[/i]\r\n\r\nExemple d’une mauvaise candidature :\r\n[i]Slt, Xx-Mortayl, iop terre niv 116, je cherche une guilde sans noubes pour pex et fair sorties dj. Je suis aussi éleveur j’ai des dindes et je voudréfair de l’élevage si c possible.[/i]\r\n\r\n- Nous recrutons avant tout des personnes sympathiques, cherchant une bonne ambiance, et sachant s’exprimer dans un français clair et compréhensible.\r\n\r\n- Votre personnage devra avoir atteint au minimum le 60eme cercle (niveau 60).\r\n\r\n- Chaque candidature sera soumise �  un vote. Les résultats de ce vote seront affichés dans la rubrique ad hoc. La décision du jury sera donnée sur le topic de présentation du perso concerné.', 1243955700, 8, 3);

-- --------------------------------------------------------

--
-- Structure de la table `reponse_sondage`
--

CREATE TABLE IF NOT EXISTS `reponse_sondage` (
  `sondage_id` int(11) NOT NULL auto_increment,
  `sondage_post_id` int(11) NOT NULL,
  `sondage_option_id` int(11) NOT NULL,
  `sondage_membre_id` int(11) NOT NULL,
  PRIMARY KEY  (`sondage_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Contenu de la table `reponse_sondage`
--


-- --------------------------------------------------------

--
-- Structure de la table `sondage_option`
--

CREATE TABLE IF NOT EXISTS `sondage_option` (
  `option_id` int(11) NOT NULL auto_increment,
  `option_post_id` int(11) NOT NULL,
  `option_texte` text NOT NULL,
  PRIMARY KEY  (`option_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Contenu de la table `sondage_option`
--


-- --------------------------------------------------------

--
-- Structure de la table `topic`
--

CREATE TABLE IF NOT EXISTS `topic` (
  `topic_id` int(11) NOT NULL auto_increment,
  `forum_id` int(11) NOT NULL,
  `topic_titre` char(100) character set latin1 collate latin1_general_ci NOT NULL,
  `topic_createur` int(11) NOT NULL,
  `topic_vu` mediumint(8) NOT NULL,
  `topic_time` int(11) NOT NULL,
  `topic_genre` varchar(30) character set latin1 collate latin1_general_ci NOT NULL,
  `topic_last_post` int(11) NOT NULL,
  `topic_first_post` int(11) NOT NULL,
  `topic_post` mediumint(8) NOT NULL,
  `topic_locked` enum('0','1') NOT NULL,
  `topic_desc` text NOT NULL,
  `topic_cloture` int(11) NOT NULL default '0',
  PRIMARY KEY  (`topic_id`),
  UNIQUE KEY `topic_last_post` (`topic_last_post`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

--
-- Contenu de la table `topic`
--

INSERT INTO `topic` (`topic_id`, `forum_id`, `topic_titre`, `topic_createur`, `topic_vu`, `topic_time`, `topic_genre`, `topic_last_post`, `topic_first_post`, `topic_post`, `topic_locked`, `topic_desc`, `topic_cloture`) VALUES
(1, 2, 'Creafters Awuikaz ?', 1, 3, 1243955223, 'PostIt', 1, 1, 0, '0', 'Kénécé ?', 0),
(2, 2, 'Aider et se faire aider', 1, 2, 1243955268, 'PostIt', 2, 2, 0, '0', '', 0),
(3, 2, 'Recruter', 1, 2, 1243955308, 'PostIt', 3, 3, 0, '0', '', 0),
(4, 2, 'XP guilde et système de grades', 1, 2, 1243955379, 'PostIt', 4, 4, 0, '0', '', 0),
(5, 2, 'Le canal guilde vs canal groupe/personnel/commerce etc…', 1, 1, 1243955401, 'PostIt', 5, 5, 0, '0', '', 0),
(6, 2, 'Enclos de guilde', 1, 1, 1243955458, 'PostIt', 6, 6, 0, '0', '', 0),
(7, 2, 'Maisons de guilde et coffres', 1, 3, 1243955529, 'PostIt', 7, 7, 0, '0', '', 0),
(8, 3, 'A lire avant de poster', 1, 3, 1243955700, 'PostIt', 8, 8, 0, '0', 'C''est pour votre bien ! Si, si...', 0);

-- --------------------------------------------------------

--
-- Structure de la table `topic_view`
--

CREATE TABLE IF NOT EXISTS `topic_view` (
  `tv_id` int(11) NOT NULL,
  `tv_topic_id` int(11) NOT NULL,
  `tv_forum_id` int(11) NOT NULL,
  `tv_post_id` int(11) NOT NULL,
  `tv_poste` enum('0','1') NOT NULL,
  PRIMARY KEY  (`tv_id`,`tv_topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `topic_view`
--

INSERT INTO `topic_view` (`tv_id`, `tv_topic_id`, `tv_forum_id`, `tv_post_id`, `tv_poste`) VALUES
(1, 1, 2, 1, '1'),
(1, 2, 2, 2, '1'),
(1, 3, 2, 3, '1'),
(1, 4, 2, 4, '1'),
(1, 5, 2, 5, '1'),
(1, 6, 2, 6, '1'),
(1, 7, 2, 7, '1'),
(1, 8, 3, 8, '1');

-- --------------------------------------------------------

--
-- Structure de la table `whosonline`
--

CREATE TABLE IF NOT EXISTS `whosonline` (
  `online_id` int(11) NOT NULL,
  `online_time` int(11) NOT NULL,
  `online_ip` int(15) NOT NULL,
  PRIMARY KEY  (`online_ip`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `whosonline`
--

INSERT INTO `whosonline` (`online_id`, `online_time`, `online_ip`) VALUES
(1, 1244291990, 1363356231);
