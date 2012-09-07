<?php 
require("includes/class/Session.class.php");
$session = new Session();
$titre = "Messagerie Privée";
$balises = true;
include_once("includes/identifiants.inc.php");
include_once("includes/debut.inc.php");
include_once("includes/menu.inc.php");
include_once("includes/bbcode.inc.php");

$action = (isset($_GET['action']))?htmlspecialchars($_GET['action']):''; //On récupère la valeur de la variable $action
 
switch($action)
{
	case "consulter": //1er cas : on veut lire un mp
		//Ici on a besoin de la valeur de l'id du mp que l'on veut lire
		?>
		<p id="fil_ariane"><i>Vous êtes ici : </i><a href="index.php">Site de CréA-Z </a>><a href="messagesprives.php">Messagerie privée</a>> Consulter un message</p>
		<h1>Consulter un message</h1><br /><br />
		<?php
		$id_mess = (int) $_GET['id']; //On récupère la valeur de l'id
		 
		 //La requête nous permet d'obtenir les infos sur ce message :
		$query = $db->prepare('SELECT  mp_expediteur, mp_receveur, mp_titre, mp_time, mp_text, mp_lu, membre_id, membre_pseudo, membre_avatar, membre_localisation, membre_inscrit, membre_post, membre_signature, dossier_titre
		FROM messagerie
		LEFT JOIN membres ON membre_id = mp_expediteur
		LEFT JOIN dossier_mp ON  dossier_mp.dossier_id = messagerie.dossier_id
		WHERE mp_id = :id');
		$query->bindValue(':id',$id_mess,PDO::PARAM_INT);
		$query->execute();
		$data=$query->fetch();

		// Attention ! Seul le receveur du mp peut le lire !
			if ($id != $data['mp_receveur']) erreur(ERR_WRONG_USER);
		?>
		<p><a href="messagesprives.php?action=repondre&amp;dest=<?= $data['mp_expediteur']; ?>"><img src="img/icones/forum/repondre.gif" alt="Répondre" title="Répondre à ce message" /></a></p>

		<table>     
			<tr>
				<th class="vt_auteur"><strong>Auteur</strong></th>             
				<th class="vt_mess"><strong>Message</strong></th>       
			</tr>
			<tr>
				<td><strong><a href="voirprofil.php?m=<?= $data['membre_id']; ?>&amp;action=consulter"><?= stripslashes(htmlspecialchars($data['membre_pseudo'])); ?></a></strong></td>
				<td>Posté à <?= date('H\hi \l\e d M Y',$data['mp_time']); ?></td>
			</tr>
			<tr>
				<td><p><img src="img/avatar/<?= $data['membre_avatar']; ?>" alt="" /><br />Membre inscrit le <?= date('d/m/Y',$data['membre_inscrit']); ?><br />Messages : <?= $data['membre_post']; ?><br />Localisation : <?= stripslashes(htmlspecialchars($data['membre_localisation'])); ?></p></td>
				<td><?= code(nl2br(stripslashes(htmlspecialchars($data['mp_text'])))); ?><hr /><?= code(nl2br(stripslashes(htmlspecialchars($data['membre_signature'])))); ?></td>
			</tr>
		</table>

		<p id="fil_ariane"><i>Vous êtes ici : </i><a href="index.php">Site de CréA-Z </a>><a href="messagesprives.php">Messagerie privée</a>> Consulter un message</p>
		<?php

		if ($data['mp_lu'] == 0) //Si le message n'a jamais été lu
		{
			$query->CloseCursor();
			$query=$db->prepare('UPDATE messagerie SET mp_lu = :lu
			WHERE mp_id= :id');
			$query->bindValue(':id',$id_mess, PDO::PARAM_INT);
			$query->bindValue(':lu','1', PDO::PARAM_STR);
			$query->execute();
			$query->CloseCursor();
		}

	break;
	 
	case "nouveau": //2eme cas : on veut poster un nouveau mp
		//Ici on a besoin de la valeur d'aucune variable :p
		?>
		<p id="fil_ariane"><i>Vous êtes ici : </i><a href="index.php">Site de CréA-Z </a>><a href="messagesprives.php">Messagerie privée</a>> Ecrire un message</p>
		<h1>Nouveau message privé</h1><br /><br />

		<form method="post" action="postok.php?action=nouveaump" name="bbform">
		   <p>
				<label for="to">Envoyer à : </label>
				<input type="text" size="30" id="to" name="to" />
				<br />
				<label for="titre">Titre : </label>
				<input type="text" size="80" id="titre" name="titre" />
				<br /><br />
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

			   <textarea cols="80" rows="8" id="wysiwyg" name="message"></textarea>
			   <br />
			   <button type="submit" name="submit">Envoyer</button>
			<button type="reset" name = "Effacer">Effacer</button>
		   </p>
		</form>

		<p id="fil_ariane"><i>Vous êtes ici : </i><a href="index.php">Site de CréA-Z </a>><a href="messagesprives.php">Messagerie privée</a>> Ecrire un message</p>
		<?php
	break;
	 
	case "repondre": //3eme cas : on veut répondre à un mp reçu
		//Ici on a besoin de la valeur de l'id du membre qui nous a posté un mp
		?>
		<p id="fil_ariane"><i>Vous êtes ici : </i><a href="index.php">Site de CréA-Z </a>><a href="messagesprives.php">Messagerie privée</a>> Répondre à un message</p>
		<h1>Répondre à un message privé</h1><br /><br />

		<?php
		$dest = (int) $_GET['dest'];
		?>
		<form method="post" action="postok.php?action=repondremp&amp;dest=<?php echo $dest ?>" name="bbform">
			<p>
				<label for="titre">Titre : </label>
				<input type="text" size="80" id="titre" name="titre" />
				<br /><br />
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

				<textarea cols="80" rows="8" id="wysiwyg" name="message"></textarea>
				<br />
				<button type="submit" name="submit">Envoyer</button>
				<button type="reset" name = "Effacer">Effacer</button>
		   </p>
		</form>
		<p id="fil_ariane"><i>Vous êtes ici : </i><a href="index.php">Site de CréA-Z </a>><a href="messagesprives.php">Messagerie privée</a>> Répondre à un message</p>
		<?php
	break;
	 
	case "supprimer": //4eme cas : on veut supprimer un mp reçu
		//Ici on a besoin de la valeur de l'id du mp à supprimer

		//On récupère la valeur de l'id
		$id_mess = (int) $_GET['id'];
		//Il faut vérifier que le membre est bien celui qui a reçu le message
		$query=$db->prepare('SELECT mp_receveur
		FROM messagerie WHERE mp_id = :id');
		$query->bindValue(':id',$id_mess,PDO::PARAM_INT);
		$query->execute();
		$data = $query->fetch();
		//Sinon la sanction est terrible :p
		if ($id != $data['mp_receveur']) erreur(ERR_WRONG_USER);
		$query->CloseCursor(); 

		//2 cas pour cette partie : on est sûr de supprimer ou alors on ne l'est pas
		$sur = (int) $_GET['sur'];
		//Pas encore certain
		if ($sur == 0)
		{
			echo'<p>Etes-vous certain de vouloir supprimer ce message ?<br />
			<a href="messagesprives.php?action=supprimer&amp;id='.$id_mess.'&amp;sur=1">
			Oui</a> - <a href="messagesprives.php">Non</a></p>';
		}
		//Certain
		else
		{
			$query=$db->prepare('DELETE from forum_mp WHERE mp_id = :id');
			$query->bindValue(':id',$id_mess,PDO::PARAM_INT);
			$query->execute();
			$query->CloseCursor(); 
			echo'<p>Le message a bien été supprimé.<br />
			Cliquez <a href="messagesprives.php">ici</a> pour revenir à la boite de réception.</p>';
		}

	break;
	 
	default; //Si rien n'est demandé ou s'il y a une erreur dans l'url, on affiche la boite de mp.
	 ?>
	<p id="fil_ariane"><i>Vous êtes ici : </i><a href="index.php">Site de CréA-Z </a>><a href="messagesprives.php">Messagerie privée</a></p>
	<h1>Messagerie Privée</h1><br /><br />
	<p><a href="messagesprives.php?action=nouveau"><img src="img/icones/forum/nouveau.gif" alt="Nouveau" title="Nouveau message" /></a></p>
	<?php
	$query=$db->prepare('SELECT mp_lu, mp_id, mp_expediteur, mp_titre, mp_time, membre_id, membre_pseudo
		FROM messagerie
		LEFT JOIN membres ON messagerie.mp_expediteur = membres.membre_id
		WHERE mp_receveur = :id ORDER BY mp_id DESC');
	$query->bindValue(':id',$id,PDO::PARAM_INT);
	$query->execute();
	if ($query->rowCount()>0)
    { 
	?>
		<table>
			<tr>
				<th></th>
				<th class="mp_titre"><strong>Titre</strong></th>
				<th class="mp_expediteur"><strong>Expéditeur</strong></th>
				<th class="mp_time"><strong>Date</strong></th>
				<th><strong>Action</strong></th>
			</tr>
		<?php
		//On boucle et on remplit le tableau
		while ($data = $query->fetch())
		{
		?>
			<tr>
		<?php
			//Mp jamais lu, on affiche l'icone en question
			if($data['mp_lu'] == 0)
			{
				?><td><img src="img/icones/forum/nonlu.gif" alt="Non lu" /></td>
				<?php
			}
			else //sinon une autre icone
			{
				?>
				<td><img src="img/icones/forum/lu.gif" alt="Déja lu" /></td>
				<?php
			}
			?>
			<td id="mp_titre"><a href="messagesprives.php?action=consulter&amp;id=<?= $data['mp_id']; ?>"><?= stripslashes(htmlspecialchars($data['mp_titre'])); ?></a></td>
			<td id="mp_expediteur"><a href="voirprofil.php?action=consulter&amp;m=<?= $data['membre_id']; ?>"><?= stripslashes(htmlspecialchars($data['membre_pseudo'])); ?></a></td>
			<td id="mp_time"><?= date('H\hi \l\e d M Y',$data['mp_time']); ?></td>
			<td><a href="messagesprives.php?action=supprimer&amp;id=<?= $data['mp_id']; ?>&amp;sur=0">supprimer</a></td></tr>
			<?php
		} //Fin de la boucle
			$query->CloseCursor();
       ?>
	   </table>
	<?php
    } //Fin du if
    else
    {
        ?><p>Vous n'avez aucun message privé pour l'instant, cliquez<a href="index.php"> ici</a> pour revenir à la page d'accueil</p>
		<?php
    }
	?>
	 <p id="fil_ariane"><i>Vous êtes ici : </i><a href="index.php">Site de CréA-Z </a>><a href="messagesprives.php">Messagerie privée</a></p>
	 <?php
} //fin du switch
?>

<?php
include_once("includes/footer.inc.php");
?>