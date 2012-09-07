<?php 
require("includes/class/Session.class.php");
$session = new Session();

$titre = "Index du forum";
include_once("includes/identifiants.inc.php");
include_once("includes/debut.inc.php");
include_once("includes/menu.inc.php");
//A présent, notre fil d'Ariane
?>
<section id="alert"></section>
<p id="fil_ariane"><i>Vous êtes ici : </i><a href="index.php">Site de CréA-Z </a>><a href="forum.php">Index du forum </a></p>
<section id="banniere"></section>
<section id="corps_forum">
	<h1>Bienvenus sur le forum !</h1>
	<?php
	//Initialisation de deux variables ayant pour but de diminuer le nombre de requetes de cette page.
	$totaldesmessages = 0; // compte le ,ombre de messages dans chaque forum pour l'écrire en bas de page
	$categorie = NULL;

	//Cette requête permet d'obtenir tout sur le forum
	$query=$db->prepare('SELECT cat_id, cat_nom, 
	forum_forum.forum_id, forum_name, forum_desc, forum_post, forum_topic, auth_view, forum_topic.topic_id,  forum_topic.topic_post, post_id, post_time, post_createur, membre_pseudo, 
	membre_id 
	FROM forum_categorie
	LEFT JOIN forum_forum ON forum_categorie.cat_id = forum_forum.forum_cat_id
	LEFT JOIN forum_post ON forum_post.post_id = forum_forum.forum_last_post_id
	LEFT JOIN forum_topic ON forum_topic.topic_id = forum_post.topic_id
	LEFT JOIN membres ON membres.membre_id = forum_post.post_createur
	WHERE auth_view <= :lvl 
	ORDER BY cat_ordre DESC, forum_ordre DESC');
	$query->bindValue(':lvl',$lvl,PDO::PARAM_INT);
	$query->execute();
	?>
	<table>
		<?php
		//Début de la boucle
		while($data = $query->fetch())
		{
			//On affiche chaque caétgorie
			if( $categorie != $data['cat_id'] AND verif_auth($data['auth_view']) )
			{
				//Si c'est une nouvelle catégorie on l'affiche
			   
				$categorie = $data['cat_id'];
				?>
				<tr>
					<th></th>
					<th class="titre"><strong><?= stripslashes(htmlspecialchars($data['cat_nom'])); ?>
					</strong></th>             
					<th class="nombremessages"><strong>Sujets</strong></th>       
					<th class="nombresujets"><strong>Messages</strong></th>       
					<th class="derniermessage"><strong>Dernier message</strong></th>   
				</tr>
				<?php
					   
			}

			//Ici, on met le contenu de chaque caétgorie
			?>
			<tr>
				<td><img src="img/icones/forum/lu.gif" alt="message" /></td>
				<td class="titre"><strong>
					<a href="./voirforum.php?f=<?= $data['forum_id']; ?>">
				<?= stripslashes(htmlspecialchars($data['forum_name'])); ?></a></strong>
				<br /><?= nl2br(stripslashes(htmlspecialchars($data['forum_desc'])))?></td>
				<td class="nombresujets"><?= $data['forum_topic']?></td>
				<td class="nombremessages"><?= $data['forum_post']?></td>
			<?php
			// Deux cas possibles :
			// Soit il y a un nouveau message, soit le forum est vide
			if (!empty($data['forum_post']))
			{
				//Selection dernier message
				$nombreDeMessagesParPage = 15;
				$nbr_post = $data['topic_post'] +1;
				$page = ceil($nbr_post / $nombreDeMessagesParPage);
				?>
				 <td class="derniermessage">
				 <?= date('H\hi \l\e d/M/Y',$data['post_time']); ?><br />
				 <a href="./voirprofil.php?m=<?= stripslashes(htmlspecialchars($data['membre_id'])); ?>&amp;action=consulter"><?= $data['membre_pseudo']; ?> </a>
				 <a href="./voirtopic.php?t=<?= $data['topic_id']?>&amp;page=<?= $page?>#p_<?= $data['post_id']?>">
				 <img src="img/icones/forum/go.png" alt="go" /></a></td>
			</tr>
			<?php
			 }
			 else
			 {
				 echo'<td class="nombremessages">Pas de message</td></tr>';
			 }

			 //Cette variable stock le nombre de messages, on la met Ã  jour
			 $totaldesmessages += $data['forum_post'];

			 //On ferme notre boucle et nos balises
		} //fin de la boucle
		$query->CloseCursor();
		?>
	</table>
</section>
<p id="fil_ariane"><i>Vous êtes ici : </i><a href="index.php">Site de CréA-Z </a>><a href="forum.php">Index du forum </a></p>
<?php
//Pied de page
//On compte les membres
$TotalDesMembres = $db->query('SELECT COUNT(*) FROM membres')->fetchColumn();
$query->CloseCursor();	
$query = $db->query('SELECT membre_pseudo, membre_id FROM membres ORDER BY membre_id DESC LIMIT 0, 1');
$data = $query->fetch();
$derniermembre = stripslashes(htmlspecialchars($data['membre_pseudo']));
$foot1 = "<h2>Qui est en ligne ?</h2>";
$foot2 = '<p>Le total des messages du forum est <strong>'.$totaldesmessages.'</strong>.<br />
Le site et le forum comptent <strong>'.$TotalDesMembres.'</strong> membres.<br />
Le dernier membre est <a href="./voirprofil.php?m='.$data['membre_id'].'&amp;action=consulter">'.$derniermembre.'</a>.</p>';
$query->CloseCursor();
$foot3 = "Le prochain concours est prévu pour Novembre 2012. Ne ratez pas le lancement ;)";
include_once("includes/footer.inc.php");
?>