<?php 
require("includes/class/Session.class.php");
$session = new Session();
if(!empty($_POST['pseudo'])) // Si on la variable est vide, on peut considérer qu'on est sur la page de formulaire
{
	$message='';
	$type = '';
	
	//Commençons le traitement
	$pseudo_erreur1 = NULL;
    $pseudo_erreur2 = NULL;
    $mdp_erreur = NULL;
    $email_erreur1 = NULL;
    $email_erreur2 = NULL;
    $msn_erreur = NULL;
    $signature_erreur = NULL;
    $avatar_erreur = NULL;
    $avatar_erreur1 = NULL;
    $avatar_erreur2 = NULL;
    $avatar_erreur3 = NULL;
	
	//On récupère les variables
    $i = 0;
    $temps = time(); 
    $pseudo=$_POST['pseudo'];
    $signature = $_POST['signature'];
    $email = $_POST['email'];
    $msn = $_POST['msn'];
    $website = $_POST['website'];
    $localisation = $_POST['localisation'];
	/* A améliorer avec le système de cryptage*/
    $pass = md5($_POST['password']);
    $confirm = md5($_POST['confirm']);
	
    //Vérification du pseudo
    $query=$db->prepare('SELECT COUNT(*) AS nbr FROM membres WHERE membre_pseudo =:pseudo');
    $query->bindValue(':pseudo',$pseudo, PDO::PARAM_STR);
    $query->execute();
    $pseudo_free=($query->fetchColumn()==0)?1:0;
    $query->CloseCursor();
    if(!$pseudo_free)
    {
        $pseudo_erreur1 = "Votre pseudo est déjà utilisé par un membre";
        $i++;
    }

    if (strlen($pseudo) < 3 || strlen($pseudo) > 15)
    {
        $pseudo_erreur2 = "Votre pseudo est soit trop grand, soit trop petit";
        $i++;
    }

    //Vérification du mdp
    if ($pass != $confirm || empty($confirm) || empty($pass))
    {
        $mdp_erreur = "Votre mot de passe et votre confirmation diffèrent, ou sont vides";
        $i++;
    }
	
	//Vérification de l'adresse email

    //Il faut que l'adresse email n'ait jamais été utilisée
    $query=$db->prepare('SELECT COUNT(*) AS nbr FROM membres WHERE membre_email =:mail');
    $query->bindValue(':mail',$email, PDO::PARAM_STR);
    $query->execute();
    $mail_free=($query->fetchColumn()==0)?1:0;
    $query->CloseCursor();
    
    if(!$mail_free)
    {
        $email_erreur1 = "Votre adresse email est déjà utilisée par un membre";
        $i++;
    }
	
    //On vérifie la forme maintenant
    if (!preg_match("#^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]{2,}\.[a-z]{2,4}$#", $email) || empty($email))
    {
        $email_erreur2 = "Votre adresse E-Mail n'a pas un format valide";
        $i++;
    }
	
    //Vérification de l'adresse MSN
    if (!preg_match("#^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]{2,}\.[a-z]{2,4}$#", $msn) && !empty($msn))
    {
        $msn_erreur = "Votre adresse MSN n'a pas un format valide";
        $i++;
    }
	
    //Vérification de la signature
    if (strlen($signature) > 200)
    {
        $signature_erreur = "Votre signature est trop longue";
        $i++;
    }
	
	//Vérification de l'avatar :
    if (!empty($_FILES['avatar']['size']))
    {
        //On définit les variables :
        $maxsize = 10024; //Poid de l'image
        $maxwidth = 100; //Largeur de l'image
        $maxheight = 100; //Longueur de l'image
        $extensions_valides = array( 'jpg' , 'jpeg' , 'gif' , 'png', 'bmp' ); //Liste des extensions valides
        
        if ($_FILES['avatar']['error'] > 0)
        {
                $avatar_erreur = "Erreur lors du transfert de l'avatar : ";
        }
        if ($_FILES['avatar']['size'] > $maxsize)
        {
                $i++;
                $avatar_erreur1 = "Le fichier est trop gros : (<strong>".$_FILES['avatar']['size']." Octets</strong>    contre <strong>".$maxsize." Octets</strong>)";
        }

        $image_sizes = getimagesize($_FILES['avatar']['tmp_name']);
        if ($image_sizes[0] > $maxwidth OR $image_sizes[1] > $maxheight)
        {
                $i++;
                $avatar_erreur2 = "Image trop large ou trop longue : 
                (<strong>".$image_sizes[0]."x".$image_sizes[1]."</strong> contre <strong>".$maxwidth."x".$maxheight."</strong>)";
        }
        
        $extension_upload = strtolower(substr(  strrchr($_FILES['avatar']['name'], '.')  ,1));
        if (!in_array($extension_upload,$extensions_valides) )
        {
                $i++;
                $avatar_erreur3 = "Extension de l'avatar incorrecte";
        }
    }
	
	if($i != 0)
	{
		$message = "Inscription interrompue. Vous avez $i erreur :<br />";
		$type = "error";
		if(isset($peudo_erreur1) AND  $peudo_erreur1 != NULL)
		{
			$message .= "$pseudo_erreur1 <br />";
		}
		if(isset($pseudo_erreur2) AND  $pseudo_erreur2 != NULL)
		{
			$message .= "$pseudo_erreur2 <br />";
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
	else
	{
		//La ligne suivante sera commentée plus bas
		$nomavatar=(!empty($_FILES['avatar']['size']))?move_avatar($_FILES['avatar']):''; 
   
        $query=$db->prepare('INSERT INTO membres (membre_pseudo, membre_mdp, membre_email,             
        membre_msn, membre_siteweb, membre_avatar,
        membre_signature, membre_localisation, membre_inscrit,   
        membre_derniere_visite)
        VALUES (:pseudo, :pass, :email, :msn, :website, :nomavatar, :signature, :localisation, :temps, :temps)');
		$query->bindValue(':pseudo', $pseudo, PDO::PARAM_STR);
		$query->bindValue(':pass', $pass, PDO::PARAM_INT);
		$query->bindValue(':email', $email, PDO::PARAM_STR);
		$query->bindValue(':msn', $msn, PDO::PARAM_STR);
		$query->bindValue(':website', $website, PDO::PARAM_STR);
		$query->bindValue(':nomavatar', $nomavatar, PDO::PARAM_STR);
		$query->bindValue(':signature', $signature, PDO::PARAM_STR);
		$query->bindValue(':localisation', $localisation, PDO::PARAM_STR);
		$query->bindValue(':temps', $temps, PDO::PARAM_INT);
        $query->execute();

		//Et on définit les variables de sessions
        $_SESSION['Auth']['pseudo'] = $pseudo;
        $_SESSION['Auth']['id'] = $db->lastInsertId(); ;
        $_SESSION['Auth']['level'] = 2;
        $query->CloseCursor();
		$message = "Inscription terminée <br />Bienvenue".stripslashes(htmlspecialchars($_POST['pseudo']));
		$type = "valid";
		
		//Amélioration : envoyer un email de bienvenu. (voir tuto inscription/connexion) + Système antibot
	}
	$session->setFlash($message, $type);
	if($type = "error")
	{
		header("Location: register.php");
	}
	else
	{
		header("Location: $page");
	}
}
else
{
	$titre = "Inscription";
	include_once("includes/identifiants.inc.php");
	include_once("includes/debut.inc.php");
	include_once("includes/menu.inc.php");
	?>
	<p id="fil_ariane"><i>Vous êtes ici : </i><a href="index.php">Site de CréA-Z </a>><a href="register.php">Inscription</a></p>
	<h1>Inscription 1/2</h1>
	<form method="post" action="register.php" enctype="multipart/form-data">
		<fieldset>
		<legend>Identifiants</legend>
			<label for="pseudo">* Pseudo :</label>  <input name="pseudo" type="text" id="pseudo" /> (le pseudo doit contenir entre 3 et 15 caractères)<br />
			<label for="password">* Mot de Passe :</label><input type="password" name="password" id="password" /><br />
			<label for="confirm">* Confirmer le mot de passe :</label><input type="password" name="confirm" id="confirm" />
		</fieldset>
		<fieldset>
			<legend>Contacts</legend>
			<label for="email">* Votre adresse Mail :</label><input type="text" name="email" id="email" /><br />
			<label for="msn">Votre adresse MSN :</label><input type="text" name="msn" id="msn" /><br />
			<label for="website">Votre site web :</label><input type="text" name="website" id="website" />
		</fieldset>
		<fieldset>
			<legend>Informations supplémentaires</legend>
			<label for="localisation">Localisation :</label><input type="text" name="localisation" id="localisation" />
		</fieldset>
		<fieldset>
			<legend>Profil sur le forum</legend>
			<label for="avatar">Choisissez votre avatar :</label><input type="file" name="avatar" id="avatar" />(Taille max : 10Ko)<br />
			<label for="signature">Signature :</label><textarea cols="40" rows="4" name="signature" id="signature">La signature est limitée à 200 caractères</textarea>
		</fieldset>
		<p>Les champs précédés d un * sont obligatoires</p>
		<input type="hidden" name="page" value="<?php echo $_SERVER['HTTP_REFERER']; ?>" />
		<p><button type="submit">S'inscrire</button></p>
	</form>

	<p id="fil_ariane"><i>Vous êtes ici : </i><a href="index.php">Site de CréA-Z </a>><a href="register.php">Inscription</a></p>
	<?php
	include_once("includes/footer.inc.php");
}//Fin de la partie formulaire
?>