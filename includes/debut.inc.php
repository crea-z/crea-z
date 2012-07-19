<!doctype html>
<html lang="fr">
<head>
	<meta charset="ANSI" />
	<title>
		<?php if(!isset($titre)) 
		{ ?> 
			CréA-Z - Nous développons pour vous de A à  Z.
		<?php } 
		else 
		{
			echo $titre; 
		} ?> 
	</title>
	<link rel="stylesheet" href= "css/reset.css" type="text/css" media="screen" />
    <link rel="stylesheet" href= "css/base.css" type="text/css" media="screen" />
    <link rel="stylesheet" href= "css/design.css" type="text/css" media="screen" />
</head>
<body>
<section id="wrap">
    <header>
		<p><img src="css/img/titre.png" alt="Titre CréA-Z" /></p>
    </header>
    <section id="fond">