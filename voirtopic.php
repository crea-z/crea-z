<?php 
require("includes/class/Session.class.php");
$session = new Session();
$titre="Voir un sujet";
include_once("includes/identifiants.inc.php");
include_once("includes/debut.inc.php");
include_once("includes/menu.inc.php");

//On récupère la valeur de t
$topic = (int) $_GET['t'];
 
//A partir d'ici, on va compter le nombre de messages pour n'afficher que les 15 premiers
$query=$db->prepare('SELECT topic_titre, topic_post, forum_topic.forum_id, topic_last_post, forum_name, auth_view, auth_topic, auth_post 
FROM forum_topic 
LEFT JOIN forum_forum ON forum_topic.forum_id = forum_forum.forum_id 
WHERE topic_id = :topic');
$query->bindValue(':topic',$topic,PDO::PARAM_INT);
$query->execute();
$data=$query->fetch();
if (!verif_auth($data['auth_view']))
{
    erreur(ERR_AUTH_VIEW);
}
$forum=$data['forum_id']; 
$totalDesMessages = $data['topic_post'] + 1;
$nombreDeMessagesParPage = 15;
$nombreDePages = ceil($totalDesMessages / $nombreDeMessagesParPage);
?>
<p id="fil_ariane"><i>Vous êtes ici : </i><a href="index.php">Site de CréA-Z </a>><a href="voirforum.php?f=<?= $forum; ?>"><?= stripslashes(htmlspecialchars($data['forum_name'])); ?></a>><a href="voirtopic.php?t=<?= $topic; ?>"><?= stripslashes(htmlspecialchars($data['topic_titre'])); ?></a></p>

<h1><?= stripslashes(htmlspecialchars($data['topic_titre'])); ?></h1>
<br /><br />
<?php
//Nombre de pages
$page = (isset($_GET['page']))?intval($_GET['page']):1;

//On affiche les pages 1-2-3 etc...
echo '<p>Page : ';
for ($i = 1 ; $i <= $nombreDePages ; $i++)
{
    if ($i == $page) //On affiche pas la page actuelle en lien
    {
    echo $i;
    }
    else
    {
    echo '<a href="voirtopic.php?t='.$topic.'&page='.$i.'">
    ' . $i . '</a> ';
    }
}
echo'</p>';
 
$premierMessageAafficher = ($page - 1) * $nombreDeMessagesParPage;

if (verif_auth($data['auth_post']))
{
//On affiche l'image répondre
?>
<a href="poster.php?action=repondre&amp;t=<?= $topic; ?>"><img src="img/icones/forum/repondre.gif" alt="Répondre" title="Répondre à ce topic" /></a>
<?php
}

if (verif_auth($data['auth_topic']))
{
//On affiche l'image nouveau topic
?>
<a href="poster.php?action=nouveautopic&amp;f=<?= $data['forum_id']; ?>"><img src="img/icones/forum/nouveau.gif" alt="Nouveau topic" title="Poster un nouveau topic" /></a>
<?php
}
$query->CloseCursor(); 
//Enfin on commence la boucle !
$query=$db->prepare('SELECT post_id , post_createur , post_texte , post_time ,
membre_id, membre_pseudo, membre_inscrit, membre_avatar, membre_localisation, membre_post, membre_signature
FROM forum_post
LEFT JOIN membres ON membres.membre_id = forum_post.post_createur
WHERE topic_id =:topic
ORDER BY post_id
LIMIT :premier, :nombre');
$query->bindValue(':topic',$topic,PDO::PARAM_INT);
$query->bindValue(':premier',(int) $premierMessageAafficher,PDO::PARAM_INT);
$query->bindValue(':nombre',(int) $nombreDeMessagesParPage,PDO::PARAM_INT);
$query->execute();
 
//On vérifie que la requête a bien retourné des messages
if ($query->rowCount()<1)
{
        echo'<p class="center">Il n y a aucun post sur ce topic, vérifiez l\'url et reessayez</p>';
}
else
{
	//Si tout roule on affiche notre tableau puis on remplit avec une boucle
	?>
	<table>
		<tr>
			<th class="vt_auteur"><strong>Auteurs</strong></th>             
			<th class="vt_mess"><strong>Messages</strong></th>       
		</tr>
		<?php
		while ($data = $query->fetch())
		{
		//On commence à afficher le pseudo du créateur du message :
         //On vérifie les droits du membre
?>
			<tr>
				<td><strong><a href="voirprofil.php?m=<?= $data['membre_id']; ?>&amp;action=consulter"><?= stripslashes(htmlspecialchars($data['membre_pseudo'])); ?></a></strong></td>
				<?php
				/* Si on est l'auteur du message, on affiche des liens pour modérer celui-ci.
				 Les modérateurs pourront aussi le faire, il faudra donc revenir sur ce code un peu plus tard ! */     
				if ($id == $data['post_createur'])
				{
				?>
					<!--Pour moi il y a pb aec le id=p_ -->
						<td id=p_<?= $data['post_id']; ?>>Posté à <?= date('H\hi \l\e d M y',$data['post_time']); ?><a href="poster.php?p=<?= $data['post_id']; ?>&amp;action=delete"><img src="img/icones/forum/supprimer.gif" alt="Supprimer" title="Supprimer ce message" /></a>
						<a href="poster.php?p=<?= $data['post_id']; ?>&amp;action=edit"><img src="img/icones/forum/editer.gif" alt="Editer"title="Editer ce message" /></a></td>
					</tr>
			<?php
				}
				else
				{
			?>
						<td>Posté à <?= date('H\hi \l\e d M y',$data['post_time']); ?></td>
					</tr>
				<?php
				}
				//Détails sur le membre qui a posté
				?>
			<tr>
				<td><img src="img/avatars/<?= $data['membre_avatar'] ?>" alt="" />
				<br />Membre inscrit le <?= date('d/m/Y',$data['membre_inscrit']); ?>
				<br />Messages : <?= $data['membre_post']; ?><br />
				Localisation : <?= stripslashes(htmlspecialchars($data['membre_localisation'])); ?></td>
				<?php 
				 //Message
				?>
				<td><?= code(nl2br(stripslashes(htmlspecialchars($data['post_texte'])))); ?>
				<br /><hr /><?= code(nl2br(stripslashes(htmlspecialchars($data['membre_signature'])))); ?></td>
			</tr>
			<?php
		} //Fin de la boucle ! \o/
		$query->CloseCursor();
		?>
	</table>
	<p>Page : 
	<?php
	for ($i = 1 ; $i <= $nombreDePages ; $i++)
        {
                if ($i == $page) //On affiche pas la page actuelle en lien
                {
                echo $i;
                }
                else
                {
                echo '<a href="voirtopic.php?t='.$topic.'&amp;page='.$i.'">
                ' . $i . '</a> ';
                }
        }
        echo'</p>';
       
        //On ajoute 1 au nombre de visites de ce topic
        $query=$db->prepare('UPDATE forum_topic
        SET topic_vu = topic_vu + 1 WHERE topic_id = :topic');
        $query->bindValue(':topic',$topic,PDO::PARAM_INT);
        $query->execute();
        $query->CloseCursor();

} //Fin du if qui vérifiait si le topic contenait au moins un message
	?>
<br /><br />
<p id="fil_ariane"><i>Vous êtes ici : </i><a href="index.php">Site de CréA-Z </a>><a href="voirforum.php?f=<?= $forum; ?>"><?= stripslashes(htmlspecialchars($data['forum_name'])); ?></a>><a href="voirtopic.php?t=<?= $topic; ?>"><?= stripslashes(htmlspecialchars($data['topic_titre'])); ?></a></p>
<?php
$query=$db->prepare('SELECT auth_view, auth_modo, auth_post FROM forum_forum WHERE forum_id=:forum');
$query->bindValue(':forum',$forum,PDO::PARAM_INT);
$query->execute();
$data=$query->fetch();
$view = (verif_auth($data['auth_view']))? 'Vous pouvez <b>voir</b> ce topic':'Vous <i>ne</i> pouvez <i>pas</i> <b>voir</b> ce topic';
$post = (verif_auth($data['auth_post']))? 'Vous pouvez <b>répondre</b> à ce topic':'Vous <i>ne</i> pouvez <i>pas</i> <b>répondre</b> à ce topic';
$modo = (verif_auth($data['auth_modo']))? 'Vous pouvez <b>modérer</b> ce topic':'Vous <i>ne</i> pouvez <i>pas</i> <b>modérer</b> ce topic';
$foot1 = $view;
$foot2 = $post;
$foot3 = $modo;
include_once("includes/footer.inc.php");
?>