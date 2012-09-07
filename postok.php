<?php 
require("includes/class/Session.class.php");
$session = new Session();
$titre="Poster";
include_once("includes/identifiants.inc.php");
include_once("includes/debut.inc.php");
include_once("includes/menu.inc.php");
//On récupère la valeur de la variable action
$action = (isset($_GET['action']))?htmlspecialchars($_GET['action']):'';

// Si le membre n'est pas connecté, il est arrivé ici par erreur
if ($id==0) erreur(ERR_IS_NOT_CO);
switch($action)
{
    //Premier cas : nouveau topic
    case "nouveautopic":
		if (!verif_auth($data['auth_annonce']) && isset($_POST['mess']))
		{
			exit('</div></body></html>');//Pas sure que l'utiliser comme il faut
		}
		//On passe le message dans une série de fonction
		$message = $_POST['message'];
		$mess = $_POST['mess'];

		//Pareil pour le titre
		$titre = $_POST['titre'];

		//ici seulement, maintenant qu'on est sur qu'elle existe, on récupère la valeur de la variable f
		$forum = (int) $_GET['f'];
		$temps = time();

		if (empty($message) || empty($titre))
		{
			echo'<p>Votre message ou votre titre est vide, 
			cliquez <a href="poster.php?action=nouveautopic&amp;f='.$forum.'">ici</a> pour recommencer</p>';
		}
		else //Si jamais le message n'est pas vide
		{
		//On entre le topic dans la base de donnée en laissant
			//le champ topic_last_post à 0
			$query=$db->prepare('INSERT INTO forum_topic
			(forum_id, topic_titre, topic_createur, topic_vu, topic_time, topic_genre)
			VALUES(:forum, :titre, :id, 1, :temps, :mess)');
			$query->bindValue(':forum', $forum, PDO::PARAM_INT);
			$query->bindValue(':titre', $titre, PDO::PARAM_STR);
			$query->bindValue(':id', $id, PDO::PARAM_INT);
			$query->bindValue(':temps', $temps, PDO::PARAM_INT);
			$query->bindValue(':mess', $mess, PDO::PARAM_STR);
			$query->execute();


			$nouveautopic = $db->lastInsertId(); //Notre fameuse fonction !
			$query->CloseCursor(); 

			//Puis on entre le message
			$query=$db->prepare('INSERT INTO forum_post
			(post_createur, post_texte, post_time, topic_id, post_forum_id)
			VALUES (:id, :mess, :temps, :nouveautopic, :forum)');
			$query->bindValue(':id', $id, PDO::PARAM_INT);
			$query->bindValue(':mess', $message, PDO::PARAM_STR);
			$query->bindValue(':temps', $temps,PDO::PARAM_INT);
			$query->bindValue(':nouveautopic', (int) $nouveautopic, PDO::PARAM_INT);
			$query->bindValue(':forum', $forum, PDO::PARAM_INT);
			$query->execute();


			$nouveaupost = $db->lastInsertId(); //Encore notre fameuse fonction !
			$query->CloseCursor(); 


			//Ici on update comme prévu la valeur de topic_last_post et de topic_first_post
			$query=$db->prepare('UPDATE forum_topic
			SET topic_last_post = :nouveaupost,
			topic_first_post = :nouveaupost
			WHERE topic_id = :nouveautopic');
			$query->bindValue(':nouveaupost', (int) $nouveaupost, PDO::PARAM_INT);    
			$query->bindValue(':nouveautopic', (int) $nouveautopic, PDO::PARAM_INT);
			$query->execute();
			$query->CloseCursor();

			//Enfin on met à jour les tables forum_forum et membres
			$query=$db->prepare('UPDATE forum_forum SET forum_post = forum_post + 1 ,forum_topic = forum_topic + 1, 
			forum_last_post_id = :nouveaupost
			WHERE forum_id = :forum');
			$query->bindValue(':nouveaupost', (int) $nouveaupost, PDO::PARAM_INT);    
			$query->bindValue(':forum', (int) $forum, PDO::PARAM_INT);
			$query->execute();
			$query->CloseCursor();
		
			$query=$db->prepare('UPDATE membres SET membre_post = membre_post + 1 WHERE membre_id = :id');
			$query->bindValue(':id', $id, PDO::PARAM_INT);    
			$query->execute();
			$query->CloseCursor();

			//Et un petit message
			echo'<p>Votre message a bien été ajouté!<br /><br />Cliquez <a href="forum.php">ici</a> pour revenir à l index du forum<br />
			Cliquez <a href="voirtopic.php?t='.$nouveautopic.'">ici</a> pour le voir</p>';
		}
    break; //Houra !
	 //Deuxième cas : répondre
    case "repondre":
		$message = $_POST['message'];

		//ici seulement, maintenant qu'on est sur qu'elle existe, on récupère la valeur de la variable t
		$topic = (int) $_GET['t'];
		$temps = time();

		if (empty($message))
		{
			echo'<p>Votre message est vide, cliquez <a href="poster.php?action=repondre&amp;t='.$topic.'">ici</a> pour recommencer</p>';
		}
		else //Sinon, si le message n'est pas vide
		{

			//On récupère l'id du forum
			$query=$db->prepare('SELECT forum_id, topic_post FROM forum_topic WHERE topic_id = :topic');
			$query->bindValue(':topic', $topic, PDO::PARAM_INT);    
			$query->execute();
			$data=$query->fetch();
			$forum = $data['forum_id'];

			//Puis on entre le message
			$query=$db->prepare('INSERT INTO forum_post
			(post_createur, post_texte, post_time, topic_id, post_forum_id)
			VALUES(:id,:mess,:temps,:topic,:forum)');
			$query->bindValue(':id', $id, PDO::PARAM_INT);   
			$query->bindValue(':mess', $message, PDO::PARAM_STR);  
			$query->bindValue(':temps', $temps, PDO::PARAM_INT);  
			$query->bindValue(':topic', $topic, PDO::PARAM_INT);   
			$query->bindValue(':forum', $forum, PDO::PARAM_INT); 
			$query->execute();

			$nouveaupost = $db->lastInsertId();
			$query->CloseCursor(); 

			//On change un peu la table forum_topic
			$query=$db->prepare('UPDATE forum_topic SET topic_post = topic_post + 1, topic_last_post = :nouveaupost WHERE topic_id =:topic');
			$query->bindValue(':nouveaupost', (int) $nouveaupost, PDO::PARAM_INT);   
			$query->bindValue(':topic', (int) $topic, PDO::PARAM_INT); 
			$query->execute();
			$query->CloseCursor(); 

			//Puis même combat sur les 2 autres tables
			$query=$db->prepare('UPDATE forum_forum SET forum_post = forum_post + 1 , forum_last_post_id = :nouveaupost WHERE forum_id = :forum');
			$query->bindValue(':nouveaupost', (int) $nouveaupost, PDO::PARAM_INT);   
			$query->bindValue(':forum', (int) $forum, PDO::PARAM_INT); 
			$query->execute();
			$query->CloseCursor(); 

			$query=$db->prepare('UPDATE membres SET membre_post = membre_post + 1 WHERE membre_id = :id');
			$query->bindValue(':id', $id, PDO::PARAM_INT); 
			$query->execute();
			$query->CloseCursor(); 

			//Et un petit message
			$nombreDeMessagesParPage = 15;
			$nbr_post = $data['topic_post']+1;
			$page = ceil($nbr_post / $nombreDeMessagesParPage);
			echo'<p>Votre message a bien été ajouté!<br /><br />
			Cliquez <a href="forum.php">ici</a> pour revenir à l index du forum<br />
			Cliquez <a href="voirtopic.php?t='.$topic.'&amp;page='.$page.'#p_'.$nouveaupost.'">ici</a> pour le voir</p>';
		}//Fin du else
    break;
	
	case "repondremp": //Si on veut répondre

		//On récupère le titre et le message
		$message = $_POST['message'];
		$titre = $_POST['titre'];
		$temps = time();

		//On récupère la valeur de l'id du destinataire
		$dest = (int) $_GET['dest'];
		//Enfin on peut envoyer le message

		$query=$db->prepare('INSERT INTO messagerie
		(mp_expediteur, mp_receveur, mp_titre, mp_text, mp_time, mp_lu)
		VALUES(:id, :dest, :titre, :txt, :tps, 0)'); 
		$query->bindValue(':id',$id,PDO::PARAM_INT);   
		$query->bindValue(':dest',$dest,PDO::PARAM_INT);   
		$query->bindValue(':titre',$titre,PDO::PARAM_STR);   
		$query->bindValue(':txt',$message,PDO::PARAM_STR);   
		$query->bindValue(':tps',$temps,PDO::PARAM_INT);   
		$query->execute();
		$query->CloseCursor(); 

		echo'<p>Votre message a bien été envoyé!<br />
		<br />Cliquez <a href="index.php">ici</a> pour revenir à l index du forum<br />
		<br />Cliquez <a href="messagesprives.php">ici</a> pour retourner à la messagerie</p>';

    break;
	
	case "nouveaump": //On envoie un nouveau mp

		//On récupère le titre et le message
		$message = $_POST['message'];
		$titre = $_POST['titre'];
		$temps = time();
		$dest = $_POST['to'];

		//On récupère la valeur de l'id du destinataire
		//Il faut déja vérifier le nom

		$query=$db->prepare('SELECT membre_id FROM membres WHERE LOWER(membre_pseudo) = :dest');
		$query->bindValue(':dest',strotolower($dest),PDO::PARAM_STR);
		$query->execute();
		if($data = $query->fetch())
		{
			$query=$db->prepare('INSERT INTO messagerie
			(mp_expediteur, mp_receveur, mp_titre, mp_text, mp_time, mp_lu)
			VALUES(:id, :dest, :titre, :txt, :tps, :lu)'); 
			$query->bindValue(':id',$id,PDO::PARAM_INT);   
			$query->bindValue(':dest',(int) $data['membre_id'],PDO::PARAM_INT);   
			$query->bindValue(':titre',$titre,PDO::PARAM_STR);   
			$query->bindValue(':txt',$message,PDO::PARAM_STR);   
			$query->bindValue(':tps',$temps,PDO::PARAM_INT);   
			$query->bindValue(':lu','0',PDO::PARAM_STR);   
			$query->execute();
			$query->CloseCursor(); 

		   echo'<p>Votre message a bien été envoyé!
		   <br /><br />Cliquez <a href="index.php">ici</a> pour revenir à l index du forum<br />
		   <br />Cliquez <a href="messagesprives.php">ici</a> pour retourner à la messagerie</p>';
		}
		//Sinon l'utilisateur n'existe pas !
		else
		{
			echo'<p>Désolé ce membre n\'existe pas, veuillez vérifier et réessayez à nouveau.</p>';
		}
    break;
	
	default;
		echo'<p>Cette action est impossible</p>';
} //Fin du Switch
include_once("includes/footer.inc.php");
?>