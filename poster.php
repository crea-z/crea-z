<?php 
require("includes/class/Session.class.php");
$session = new Session();
$titre = "Poster";
$balises = true;
include_once("includes/identifiants.inc.php");
include_once("includes/debut.inc.php");
include_once("includes/menu.inc.php");
include_once("includes/bbcode.inc.php");

//Qu'est ce qu'on veut faire ? poster, répondre ou éditer ?
$action = (isset($_GET['action']))?htmlspecialchars($_GET['action']):'';

//Il faut être connecté pour poster !
if ($id==0) erreur(ERR_IS_NOT_CO);

//Si on veut poster un nouveau topic, la variable f se trouve dans l'url,
//On récupère certaines valeurs
if (isset($_GET['f']))
{
    $forum = (int) $_GET['f'];
    $query= $db->prepare('SELECT forum_name, auth_view, auth_post, auth_topic, auth_annonce, auth_modo
    FROM forum_forum WHERE forum_id =:forum');
    $query->bindValue(':forum',$forum,PDO::PARAM_INT);
    $query->execute();
    $data=$query->fetch();
	if (!verif_auth($data['auth_view']))
	{
		erreur(ERR_AUTH_VIEW);
	}
?>
<p id="fil_ariane"><i>Vous êtes ici : </i><a href="index.php">Site de CréA-Z </a>><a href="voirforum.php?f=<?= $data['forum_id']; ?>"><?= stripslashes(htmlspecialchars($data['forum_name'])); ?></a>>Nouveau topic</p>
<?php
}
//Sinon c'est un nouveau message, on a la variable t et
//On récupère f grâce à une requête
elseif (isset($_GET['t']))
{
    $topic = (int) $_GET['t'];
    $query=$db->prepare('SELECT topic_titre, forum_topic.forum_id,
    forum_name, auth_view, auth_post, auth_topic, auth_annonce, auth_modo
    FROM forum_topic
    LEFT JOIN forum_forum ON forum_forum.forum_id = forum_topic.forum_id
    WHERE topic_id =:topic');
    $query->bindValue(':topic',$topic,PDO::PARAM_INT);
    $query->execute();
    $data=$query->fetch();
	if (!verif_auth($data['auth_view']))
	{
		erreur(ERR_AUTH_VIEW);
	}
    $forum = $data['forum_id'];  
?>
<p id="fil_ariane"><i>Vous êtes ici : </i><a href="index.php">Site de CréA-Z </a>><a href="voirforum.php?f=<?= $data['forum_id']; ?>"><?= stripslashes(htmlspecialchars($data['forum_name'])); ?></a>><a href="voirtopic.php?t=<?= $topic; ?>"><?= stripslashes(htmlspecialchars($data['topic_titre'])); ?></a>> Répondre</p>
<?php
}
//Enfin sinon c'est au sujet de la modération(on verra plus tard en détail)
//On ne connait que le post, il faut chercher le reste
elseif (isset ($_GET['p']))
{
    $post = (int) $_GET['p'];
    $query=$db->prepare('SELECT post_createur, forum_post.topic_id, topic_titre, forum_topic.forum_id,
    forum_name, auth_view, auth_post, auth_topic, auth_annonce, auth_modo
    FROM forum_post
    LEFT JOIN forum_topic ON forum_topic.topic_id = forum_post.topic_id
    LEFT JOIN forum_forum ON forum_forum.forum_id = forum_topic.forum_id
    WHERE forum_post.post_id =:post');
    $query->bindValue(':post',$post,PDO::PARAM_INT);
    $query->execute();
    $data=$query->fetch();
	if (!verif_auth($data['auth_modo']))
	{
		erreur(ERR_AUTH_VIEW);
	}
    $topic = $data['topic_id'];
    $forum = $data['forum_id'];
?>
<p id="fil_ariane"><i>Vous êtes ici : </i><a href="index.php">Site de CréA-Z </a>><a href="voirforum.php?f=<?= $data['forum_id']; ?>"><?= stripslashes(htmlspecialchars($data['forum_name'])); ?></a>><a href="voirtopic.php?t=<?= $topic; ?>"><?= stripslashes(htmlspecialchars($data['topic_titre'])); ?></a>> Modérer un message</p>
<?php
}
$query->CloseCursor();  
switch($action)
{
	case "repondre": //Premier cas : on souhaite répondre
	//Ici, on affiche le formulaire de réponse
	if (!verif_auth($data['auth_post']))
	{
		erreur(ERR_AUTH_POST);
	}
	?>
		<h1>Poster une réponse</h1>
		 
		<form method="post" action="postok.php?action=repondre&amp;t=<?php echo $topic ?>" name="bbform">
		 
			<fieldset>
				<legend>Mise en forme</legend>
				<input type="button" id="gras" name="gras" value="Gras" onClick="javascript:bbcode('[g]', '[/g]');return(false)" />
				<input type="button" id="italic" name="italic" value="Italic" onClick="javascript:bbcode('[i]', '[/i]');return(false)" />
				<input type="button" id="souligné" name="souligné" value="Souligné" onClick="javascript:bbcode('[s]', '[/s]');return(false)" />
				<input type="button" id="lien" name="lien" value="Lien" onClick="javascript:bbcode('[url]', '[/url]');return(false)" />
				<br /><br />
				<img src="img/smiley/heureux.gif" title="heureux" alt="heureux" onClick="javascript:smilies(' :D ');return(false)" />
				<img src="img/smiley/lol.gif" title="lol" alt="lol" onClick="javascript:smilies(' :lol: ');return(false)" />
				<img src="img/smiley/triste.gif" title="triste" alt="triste" onClick="javascript:smilies(' :triste: ');return(false)" />
				<img src="img/smiley/cool.gif" title="cool" alt="cool" onClick="javascript:smilies(' :frime: ');return(false)" />
				<img src="img/smiley/rire.gif" title="rire" alt="rire" onClick="javascript:smilies(' XD ');return(false)" />
				<img src="img/smiley/confus.gif" title="confus" alt="confus" onClick="javascript:smilies(' :s ');return(false)" />
				<img src="img/smiley/choc.gif" title="choc" alt="choc" onClick="javascript:smilies(' :o ');return(false)" />
				<img src="img/smiley/question.gif" title="?" alt="?" onClick="javascript:smilies(' :interrogation: ');return(false)" />
				<img src="img/smiley/exclamation.gif" title="!" alt="!" onClick="javascript:smilies(' :exclamation: ');return(false)" />
			</fieldset>
			 
			<fieldset>
				<legend>Message</legend>
				<textarea cols="80" rows="8" id="wysiwyg" name="message"></textarea>
			</fieldset>
			<p>
				<button type="submit" name="submit">Envoyer</button>
				<button type="reset" name = "Effacer">Effacer</button>
			</p>
		</form>
		<?php
	break;
	 
	case "nouveautopic": //Deuxième cas : on souhaite créer un nouveau topic
	//Ici, on affiche le formulaire de nouveau topic
	if (!verif_auth($data['auth_topic']))
	{
		erreur(ERR_AUTH_TOPIC);
	}
		?>
		<h1>Nouveau topic</h1>
		<form method="post" action="postok.php?action=nouveautopic&amp;f=<?php echo $forum ?>" name="bbform">
		 
			<fieldset>
				<legend>Titre</legend>
				<input type="text" size="80" id="titre" name="titre" />
			</fieldset>
			
			<fieldset>
				<legend>Mise en forme</legend>
				<input type="button" id="gras" name="gras" value="Gras" onClick="javascript:bbcode('[g]', '[/g]');return(false)" />
				<input type="button" id="italic" name="italic" value="Italic" onClick="javascript:bbcode('[i]', '[/i]');return(false)" />
				<input type="button" id="souligné" name="souligné" value="Souligné" onClick="javascript:bbcode('[s]', '[/s]');return(false)" />
				<input type="button" id="lien" name="lien" value="Lien" onClick="javascript:bbcode('[url]', '[/url]');return(false)" />
				<br /><br />
				<img src="img/smiley/heureux.gif" title="heureux" alt="heureux" onClick="javascript:smilies(':D');return(false)" />
				<img src="img/smiley/lol.gif" title="lol" alt="lol" onClick="javascript:smilies(':lol:');return(false)" />
				<img src="img/smiley/triste.gif" title="triste" alt="triste" onClick="javascript:smilies(':triste:');return(false)" />
				<img src="img/smiley/cool.gif" title="cool" alt="cool" onClick="javascript:smilies(':frime:');return(false)" />
				<img src="img/smiley/rire.gif" title="rire" alt="rire" onClick="javascript:smilies('XD');return(false)" />
				<img src="img/smiley/confus.gif" title="confus" alt="confus" onClick="javascript:smilies(':s');return(false)" />
				<img src="img/smiley/choc.gif" title="choc" alt="choc" onClick="javascript:smilies(':O');return(false)" />
				<img src="img/smiley/question.gif" title="?" alt="?" onClick="javascript:smilies(':interrogation:');return(false)" />
				<img src="img/smiley/exclamation.gif" title="!" alt="!" onClick="javascript:smilies(':exclamation:');return(false)" />
			</fieldset>
			 
			<fieldset>
			<legend>Message</legend>
				<textarea cols="80" rows="8" id="wysiwyg" name="message"></textarea>
				<?php
				if (verif_auth($data['auth_annonce']) AND $lvl != CLIENT)
				{
					?>
					<label><input type="radio" name="mess" value="Annonce" />Annonce</label>
					<label><input type="radio" name="mess" value="Message" checked="checked" />Topic</label>
				   <?php
				}
				?>
			</fieldset>
			<p>
				<button type="submit" name="submit">Envoyer</button>
				<button type="reset" name = "Effacer">Effacer</button>
			</p>
		</form>
		<?php
	break;
	 
	//D'autres cas viendront s'ajouter là plus tard :p
	 
	default: //Si jamais c'est aucun de ceux-là, c'est qu'il y a eu un problème :o
	echo'<h2>Cette action est impossible</h2>';
 
} //Fin du switch
include_once("includes/footer.inc.php");
?>