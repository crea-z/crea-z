<?php 
require("includes/class/Session.class.php");
$session = new Session();
if (isset($_POST['pseudo'])) //On est dans la page de formulaire
{
	$message='';
	$type = '';
	$page = htmlspecialchars($_POST['page']);
    if (empty($_POST['pseudo']) || empty($_POST['password']) ) //Oublie d'un champ
    {
        $message = '<p>une erreur s\'est produite pendant votre identification.
		Vous devez remplir tous les champs</p>
		<p>Cliquez <a href="connexion.php">ici</a> pour revenir</p>';
		$type = 'error';
    }
    else //On check le mot de passe
    {
        $query=$db->prepare('SELECT membre_mdp, membre_id, membre_rang, membre_pseudo
        FROM membres WHERE membre_pseudo = :pseudo');
        $query->bindValue(':pseudo',$_POST['pseudo'], PDO::PARAM_STR);
        $query->execute();
        $data=$query->fetch();
		
		
	//A revoir avec le système de cryptage
	
	
	if ($data['membre_mdp'] == md5($_POST['password'])) // Acces OK !
	{
		if($data['membre_rang']) == 0) //Le membre est banni
		{
			$message = "<p>Vous avez été banni, impossible de vous connecter sur le site</p>";
			$type = "error";
		}
		else
		{
			$_SESSION['Auth']['pseudo'] = $data['membre_pseudo'];
			$_SESSION['Auth']['level'] = $data['membre_rang'];
			$_SESSION['Auth']['id'] = $data['membre_id'];
			$message = "<p>Bienvenue $_SESSION['Auth']['pseudo'], vous êtes maintenant connecté!</p>";
			$type = "valid";
		}
	}
	else // Acces pas OK !
	{
	    $message = '<p>Une erreur s\'est produite 
	    pendant votre identification.<br /> Le mot de passe ou le pseudo 
            entré n\'est pas correcte.</p><p>Cliquez <a href="connexion.php">ici</a> 
	    pour revenir à la page précédente</p>';
		$type = 'error';
	}
    $query->CloseCursor();
    }
    $session->setFlash($message, $type);
	header("Location: $page");
}
else
{
	$titre = "Connexion";
	include_once("includes/identifiants.inc.php");
	include_once("includes/debut.inc.php");
	include_once("includes/menu.inc.php");
	?>
	<p id="fil_ariane"><i>Vous êtes ici : </i><a href="index.php">Site de CréA-Z </a>><a href="connexion.php">Connexion </a></p>
	<section id="page_co">
		<h1>Connexion</h1>
		<?php
		//Si l'utilisateur est déjà connecté, il ne peut pas le refaire !
		if ($id!=0) erreur(ERR_IS_CO);
		?>

		<form method="post" action="connexion.php">
			<fieldset>
				<legend>Connexion</legend>
				<p>
				<label for="pseudo">Pseudo :</label><input name="pseudo" type="text" id="pseudo" /><br />
				<label for="password">Mot de Passe :</label><input type="password" name="password" id="password" />
				</p>
			</fieldset>
			<input type="hidden" name="page" value="<?php echo $_SERVER['HTTP_REFERER']; ?>" />
			<p><button type="submit">Connexion </button></p>
		</form>
		<a href="register.php">Pas encore inscrit ?</a>
	</section>
	<p id="fil_ariane"><i>Vous êtes ici : </i><a href="index.php">Site de CréA-Z </a>><a href="connexion.php">Connexion </a></p>
	<?php
	include_once("includes/footer.inc.php");
}
?>