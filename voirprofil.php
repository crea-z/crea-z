<?php 
require("includes/class/Session.class.php");
$session = new Session();
//On récupère la valeur de nos variables passées par URL
$action = isset($_GET['action'])?htmlspecialchars($_GET['action']):'consulter';
$membre = isset($_GET['m'])?(int) $_GET['m']:'';
//On regarde la valeur de la variable $action
switch($action)
{
	//Si on choisit de modifier son profil
    case "modifier":
	if (!empty($_POST['sent'])) //Si on la variable n'est pas vide on est dans la page de traitement
    {
		//On déclare les variables 
		$mdp_erreur = NULL;
		$email_erreur1 = NULL;
		$email_erreur2 = NULL;
		$msn_erreur = NULL;
		$signature_erreur = NULL;
		$avatar_erreur = NULL;
		$avatar_erreur1 = NULL;
		$avatar_erreur2 = NULL;
		$avatar_erreur3 = NULL;
		
		//Encore et toujours notre belle variable $i :p
		$i = 0;
		$temps = time(); 
		$signature = $_POST['signature'];
		$email = $_POST['email'];
		$msn = $_POST['msn'];
		$website = $_POST['website'];
		$localisation = $_POST['localisation'];
		$pass = md5($_POST['password']);
		$confirm = md5($_POST['confirm']);
		
		//Vérification du mdp
		if ($pass != $confirm || empty($confirm) || empty($pass))
		{
			 $mdp_erreur = "Votre mot de passe et votre confirmation diffèrent ou sont vides";
			 $i++;
		}

		//Vérification de l'adresse email
		//Il faut que l'adresse email n'ait jamais été utilisée (sauf si elle n'a pas été modifiée)

		//On commence donc par récupérer le mail
		$query=$db->prepare('SELECT membre_email FROM membres WHERE membre_id =:id'); 
		$query->bindValue(':id',$id,PDO::PARAM_INT);
		$query->execute();
		$data=$query->fetch();
		if (strtolower($data['membre_email']) != strtolower($email))
		{
			//Il faut que l'adresse email n'ait jamais été utilisée
			$query=$db->prepare('SELECT COUNT(*) AS nbr FROM membres WHERE membre_email =:mail');
			$query->bindValue(':mail',$email,PDO::PARAM_STR);
			$query->execute();
			$mail_free=($query->fetchColumn()==0)?1:0;
			$query->CloseCursor();
			if(!$mail_free)
			{
				$email_erreur1 = "Votre adresse email est déjà utilisé par un membre";
				$i++;
			}

			//On vérifie la forme maintenant
			if (!preg_match("#^[a-z0-9A-Z._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$#", $email) || empty($email))
			{
				$email_erreur2 = "Votre nouvelle adresse E-Mail n'a pas un format valide";
				$i++;
			}
		}
		//Vérification de l’adresse MSN
		if (!preg_match("#^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$#", $msn) && !empty($msn))
		{
			$msn_erreur = "Votre nouvelle adresse MSN n'a pas un format valide";
			$i++;
		}

		//Vérification de la signature
		if (strlen($signature) > 200)
		{
			$signature_erreur = "Votre nouvelle signature est trop longue";
			$i++;
		}
	 
	 
		//Vérification de l'avatar
	 
		if (!empty($_FILES['avatar']['size']))
		{
			//On définit les variables :
			$maxsize = 30072; //Poid de l'image
			$maxwidth = 100; //Largeur de l'image
			$maxheight = 100; //Longueur de l'image
			//Liste des extensions valides
			$extensions_valides = array( 'jpg' , 'jpeg' , 'gif' , 'png', 'bmp' );
	 
			if ($_FILES['avatar']['error'] > 0)
			{
			$avatar_erreur = "Erreur lors du tranfsert de l'avatar : ";
			}
			if ($_FILES['avatar']['size'] > $maxsize)
			{
			$i++;
			$avatar_erreur1 = "Le fichier est trop gros :
			(<strong>".$_FILES['avatar']['size']." Octets</strong>
			contre <strong>".$maxsize." Octets</strong>)";
			}
	 
			$image_sizes = getimagesize($_FILES['avatar']['tmp_name']);
			if ($image_sizes[0] > $maxwidth OR $image_sizes[1] > $maxheight)
			{
			$i++;
			$avatar_erreur2 = "Image trop large ou trop longue :
			(<strong>".$image_sizes[0]."x".$image_sizes[1]."</strong> contre
			<strong>".$maxwidth."x".$maxheight."</strong>)";
			}
	 
			$extension_upload = strtolower(substr(  strrchr($_FILES['avatar']['name'], '.')  ,1));
			if (!in_array($extension_upload,$extensions_valides) )
			{
					$i++;
					$avatar_erreur3 = "Extension de l'avatar incorrecte";
			}
		}
		if ($i == 0) // Si $i est vide, il n'y a pas d'erreur
		{
			if (!empty($_FILES['avatar']['size']))
			{
					$nomavatar=move_avatar($_FILES['avatar']);
					$query=$db->prepare('UPDATE membres
					SET membre_avatar = :avatar 
					WHERE membre_id = :id');
					$query->bindValue(':avatar',$nomavatar,PDO::PARAM_STR);
					$query->bindValue(':id',$id,PDO::PARAM_INT);
					$query->execute();
					$query->CloseCursor();
			}
	 
			//Une nouveauté ici : on peut choisis de supprimer l'avatar
			if (isset($_POST['delete']))
			{
					$query=$db->prepare('UPDATE membres
					SET membre_avatar=0 WHERE membre_id = :id');
					$query->bindValue(':id',$id,PDO::PARAM_INT);
					$query->execute();
					$query->CloseCursor();
			}
			//On modifie la table
 
			$query=$db->prepare('UPDATE membres
			SET  membre_mdp = :mdp, membre_email=:mail, membre_msn=:msn, membre_siteweb=:website,
			membre_signature=:sign, membre_localisation=:loc
			WHERE membre_id=:id');
			$query->bindValue(':mdp',$pass,PDO::PARAM_INT);
			$query->bindValue(':mail',$email,PDO::PARAM_STR);
			$query->bindValue(':msn',$msn,PDO::PARAM_STR);
			$query->bindValue(':website',$website,PDO::PARAM_STR);
			$query->bindValue(':sign',$signature,PDO::PARAM_STR);
			$query->bindValue(':loc',$localisation,PDO::PARAM_STR);
			$query->bindValue(':id',$id,PDO::PARAM_INT);
			$query->execute();
			$query->CloseCursor();
			
			$message = "Modification terminée <br />Votre profil a été modifié avec succès !";
			$type = "valid";
		}
		else
		{		
			$message = "Modification interrompue. Vous avez $i erreur(s) :<br />";
			$type = "error";
			if(isset($mdp_erreur) AND  $mdp_erreur != NULL)
			{
				$message .= "$mdp_erreur <br />";
			}
			if(isset($email_erreur1) AND  $email_erreur1 != NULL)
			{
				$message .= "$email_erreur1 <br />";
			}
			if(isset($email_erreur2) AND  $email_erreur2 != NULL)
			{
				$message .= "$email_erreur2 <br />";
			}
			if(isset($msn_erreur) AND  $msn_erreur != NULL)
			{
				$message .= "$msn_erreur <br />";
			}
			if(isset($signature_erreur) AND  $signature_erreur != NULL)
			{
				$message .= "$signature_erreur <br />";
			}
			if(isset($avatar_erreur) AND  $avatar_erreur != NULL)
			{
				$message .= "$avatar_erreur <br />";
			}
			if(isset($avatar_erreur1) AND  $avatar_erreur1 != NULL)
			{
				$message .= "$avatar_erreur1 <br />";
			}
			if(isset($avatar_erreur2) AND  $avatar_erreur2 != NULL)
			{
				$message .= "$avatar_erreur2 <br />";
			}
			if(isset($avatar_erreur3) AND  $avatar_erreur3 != NULL)
			{
				$message .= "$avatar_erreur3 <br />";
			}
		}
		$session->setFlash($message, $type);
		header("Location: voirprofil.php?action=modifier");
	}   
    else //Sinon , on peut considérer qu'on est sur la page de formulaire
    {
		$titre = "Profil de $data['membre_pseudo']";
		include_once("includes/identifiants.inc.php");
		include_once("includes/debut.inc.php");
		include_once("includes/menu.inc.php");
		//On commence par s'assurer que le membre est connecté
        if ($id==0) erreur(ERR_IS_NOT_CO);
		//On prend les infos du membre
        $query=$db->prepare('SELECT membre_pseudo, membre_email, membre_siteweb, membre_signature, membre_msn, membre_localisation, membre_avatar
        FROM membres WHERE membre_id=:id');
        $query->bindValue(':id',$id,PDO::PARAM_INT);
        $query->execute();
        $data=$query->fetch();
		?>
		<p id="fil_ariane"><i>Vous êtes ici : </i><a href="index.php">Site de CréA-Z </a>><a href="voirprofil.php?action=modifier"> Modification de votre profil</a></p>
		
		<h1>Modification du profil</h1>
		
		<form method="post" action="voirprofil.php?action=modifier" enctype="multipart/form-data">
			<fieldset>
				<legend>Identifiants</legend>
				Pseudo : <strong><?= stripslashes(htmlspecialchars($data['membre_pseudo'])); ?></strong><br />       
				<label for="password">Nouveau mot de Passe :</label>
				<input type="password" name="password" id="password" /><br />
				<label for="confirm">Confirmer le mot de passe :</label>
				<input type="password" name="confirm" id="confirm"  />
			</fieldset>
	 
			<fieldset>
				<legend>Contacts</legend>
				<label for="email">Votre adresse E_Mail :</label>
				<input type="text" name="email" id="email" value="<?=stripslashes($data['membre_email']); ?>" /><br />
		 
				<label for="msn">Votre adresse MSN :</label>
				<input type="text" name="msn" id="msn" value="<?= stripslashes($data['membre_msn']); ?>" /><br />
		 
				<label for="website">Votre site web :</label>
				<input type="text" name="website" id="website" value="'<?= stripslashes($data['membre_siteweb']); ?>" /><br />
			</fieldset>
	 
			<fieldset>
				<legend>Informations supplémentaires</legend>
				<label for="localisation">Localisation :</label>
				<input type="text" name="localisation" id="localisation" value="<?= stripslashes($data['membre_localisation']); ?>" /><br />
			</fieldset>
				   
			<fieldset>
				<legend>Profil sur le forum</legend>
				<label for="avatar">Changer votre avatar :</label>
				<input type="file" name="avatar" id="avatar" />
				(Taille max : 10 ko)<br /><br />
				<label><input type="checkbox" name="delete" value="Delete" />
				Supprimer l avatar</label>
				Avatar actuel :
				<img src="img/avatars/<?= $data['membre_avatar']; ?>" alt="pas d avatar" />
			 
				<br /><br />
				<label for="signature">Signature :</label>
				<textarea cols="40" rows="4" name="signature" id="signature">
				<?= stripslashes($data['membre_signature']); ?>
				</textarea>
		 
			</fieldset>
			<p>
			<input type="submit" value="Modifier le profil" />
			<input type="hidden" id="sent" name="sent" value="1" />
			</p>
		</form>
		
		<p id="fil_ariane"><i>Vous êtes ici : </i><a href="index.php">Site de CréA-Z </a>><a href="voirprofil.php?action=modifier"> Modification de votre profil</a></p>
		<?php
		$query->CloseCursor();   
	}
	break;
    //Si c'est "consulter"
    case "consulter":
		//On récupère les infos du membre
		$query=$db->prepare('SELECT membre_pseudo, membre_avatar, membre_email, membre_msn, membre_signature, membre_siteweb, membre_post, membre_inscrit, membre_localisation
		FROM membres WHERE membre_id=:membre');
		$query->bindValue(':membre',$membre, PDO::PARAM_INT);
		$query->execute();
		$data=$query->fetch();
		$titre = "Profil de $data['membre_pseudo']";
		include_once("includes/identifiants.inc.php");
		include_once("includes/debut.inc.php");
		include_once("includes/menu.inc.php");

       //On affiche les infos sur le membre
?>
		<p id="fil_ariane"><i>Vous êtes ici : </i><a href="index.php">Site de CréA-Z </a>><a href="voirprofil.php?action=consulter&amp;m=<?= $membre ?>">Profil de <?= stripslashes(htmlspecialchars($data['membre_pseudo'])); ?></a></p>
		<h1>Profil de <?= stripslashes(htmlspecialchars($data['membre_pseudo'])); ?></h1>
		<img src="img/avatars/<?= $data['membre_avatar'] ?>" alt="Ce membre n a pas d avatar" />
		<p><strong>Adresse E-Mail : </strong>
		<a href="mailto:<?= stripslashes($data['membre_email']); ?>"><?= stripslashes(htmlspecialchars($data['membre_email'])); ?></a><br />
		<strong>MSN Messenger : </strong><?= stripslashes(htmlspecialchars($data['membre_msn'])); ?><br />
		<strong>Site Web : </strong><a href="<?= stripslashes($data['membre_siteweb']); ?>"><?= stripslashes(htmlspecialchars($data['membre_siteweb'])); ?></a>
		<br /><br />
		Ce membre est inscrit depuis le <strong><?= date('d/m/Y',$data['membre_inscrit']); ?></strong> et a posté <strong><?= $data['membre_post']; ?></strong> messages
        <br /><br />
		<strong>Localisation : </strong><?= stripslashes(htmlspecialchars($data['membre_localisation'])); ?></p>
		<p id="fil_ariane"><i>Vous êtes ici : </i><a href="index.php">Site de CréA-Z </a>><a href="voirprofil.php?action=consulter&amp;m=<?= $membre ?>">Profil de <?= stripslashes(htmlspecialchars($data['membre_pseudo'])); ?></a></p>
<?php
		$query->CloseCursor();
     break;
	default; //Si jamais c'est aucun de ceux-là c'est qu'il y a eu un problème :o
		$titre = "Erreur";
		include_once("includes/identifiants.inc.php");
		include_once("includes/debut.inc.php");
		include_once("includes/menu.inc.php");
		echo'<p>Cette action est impossible</p>';
}//Fin du switch
include_once("includes/footer.inc.php");
?>