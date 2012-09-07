<?php 
require("includes/class/Session.class.php");
$session = new Session();
session_destroy();
$titre = "Déconnexion";
include_once("includes/identifiants.inc.php");
include_once("includes/debut.inc.php");
if ($id==0) erreur(ERR_IS_NOT_CO);
?>
<p>Vous êtes à présent déconnecté <br />
Cliquez <a href="<?= htmlspecialchars($_SERVER['HTTP_REFERER']) ?>">ici</a> 
pour revenir à la page précédente.<br />
Cliquez <a href="index.php">ici</a> pour revenir à la page d'accueil du site</p>
<?php
include_once("includes/footer.inc.php");
?>
