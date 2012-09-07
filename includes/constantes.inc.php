<?php
//Définissons nos utilisateurs :
define('BANNI', 0);
define('VISITEUR', 1);
define('INSCRIT', 2);
define('MODO', 3);
define('CLIENT', 4);
define('ADMIN', 5);

//Voici les erreurs de connexion :
define('ERR_IS_CO','Vous ne pouvez pas accéder à cette page si vous êtes connecté');
define('ERR_IS_NOT_CO','Vous ne pouvez pas accéder à cette page si vous n\'êtes pas connecté');
define('ERR_IS_MODO','Vous devez être modérateur pour accéder à cette page');
define('ERR_IS_CL','Vous devez être un client pour accéder à cette page');
define('ERR_IS_ADM','Vous ne pouvez pas accéder à cette page si vous n\'êtes pas administrateur');
define('ERR_WRONG_USER','Ce message ne vous concerne pas');
define('ERR_AUTH_VIEW','Vous n\'êtes pas autorisé à voir ce forum');
define('ERR_AUTH_TOPIC','Vous n\'êtes pas autorisé à poster de nouveau sujet sur ce forum');
define('ERR_AUTH_POST','Vous n\'êtes pas autorisé à poster sur ce forum');


