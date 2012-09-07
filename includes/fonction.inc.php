<?php
function erreur($err='')
{
   $mess=($err!='')? $err:'Une erreur inconnue s\'est produite';
   exit('<p>'.$mess.'</p>
   <p>Cliquez <a href="index.php">ici</a> pour revenir à la page d\'accueil</p> </section><footer><article id="foot1"></article><article id="foot2"></article><article id="foot3"></article></footer></section></section></body></html>');
}

function move_avatar($avatar)
{
    $extension_upload = strtolower(substr(strrchr($avatar['name'], '.'),1));
    $name = time();
    $nomavatar = str_replace(' ','',$name).".".$extension_upload;
    $name = "img/avatars/".str_replace(' ','',$name).".".$extension_upload;
    move_uploaded_file($avatar['tmp_name'],$name);
    return $nomavatar;
}

function verif_auth($auth_necessaire)
{
	$level=(isset($_SESSION['Auth']['level']))?$_SESSION['Auth']['level']:1;
	return ($auth_necessaire <= intval($level));
}
