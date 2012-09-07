<!doctype html>
<html lang="fr">
<head>
	<meta charset="utf-8" />
	<title>
		<?php if(empty($titre)) 
		{ ?> 
			CréA-Z - Nous développons pour vous de A à Z.
		<?php } 
		else 
		{
			echo $titre; 
		} ?> 
	</title>
	<link rel="stylesheet" href= "css/reset.css" type="text/css" media="screen" />
    <link rel="stylesheet" href= "css/base.css" type="text/css" media="screen" />
    <link rel="stylesheet" href= "css/design.css" type="text/css" media="screen" />
	
	<!-- Fichier javascript principal -->
	
	<script type="text/javascript" src="js/main.js"></script>
	<?php
	$balises=(isset($balises))?$balises:0;
	if($balises)
	{
	?>
	<script>
		function bbcode(bbdebut, bbfin)
		{
			var input = window.document.bbform.wysiwyg;
			input.focus();
			if(typeof document.selection != 'undefined')
			{
				var range = document.selection.createRange();
				var insText = range.text;
				range.text = bbdebut + insText + bbfin;
				range = document.selection.createRange();
				if (insText.length == 0)
				{
					range.move('character', -bbfin.length);
				}
				else
				{
					range.moveStart('character', bbdebut.length + insText.length + bbfin.length);
				}
				range.select();
			}
			else if(typeof input.selectionStart != 'undefined')
			{
				var start = input.selectionStart;
				var end = input.selectionEnd;
				var insText = input.value.substring(start, end);
				input.value = input.value.substr(0, start) + bbdebut + insText + bbfin + input.value.substr(end);
				var pos;
				if (insText.length == 0)
				{
					pos = start + bbdebut.length;
				}
				else
				{
					pos = start + bbdebut.length + insText.length + bbfin.length;
				}
				input.selectionStart = pos;
				input.selectionEnd = pos;
			}
			 
			else
			{
				var pos;
				var re = new RegExp('^[0-9]{0,3}$');
				while(!re.test(pos))
				{
					pos = prompt("insertion (0.." + input.value.length + "):", "0");
				}
				if(pos > input.value.length)
				{
					pos = input.value.length;
				}
					var insText = prompt("Veuillez taper le texte");
					input.value = input.value.substr(0, pos) + bbdebut + insText + bbfin + input.value.substr(pos);
				}
		}

		function smilies(img)
		{
			window.document.bbform.wysiwyg.value += '' + img + '';
		}
	</script>
<?php
	}
	?>
</head>
<?php
//Attribution des variables de session
$lvl=(isset($_SESSION['Auth']['level']))?(int) $_SESSION['Auth']['level']:1;
$id=(isset($_SESSION['Auth']['id']))?(int) $_SESSION['Auth']['id']:0;
$pseudo=(isset($_SESSION['Auth']['pseudo']))?$_SESSION['Auth']['pseudo']:'';

//On inclue les 2 pages restantes
include_once("includes/fonction.inc.php");
include_once("includes/constantes.inc.php");
?>