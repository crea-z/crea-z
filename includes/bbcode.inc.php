<?php
function code($texte)
{
	//Smileys
	$texte = str_replace(':D ', '<img src="img/smiley/heureux.gif" title="heureux" alt="heureux" />', $texte);
	$texte = str_replace(':lol: ', '<img src="img/smiley/lol.gif" title="lol" alt="lol" />', $texte);
	$texte = str_replace(':triste:', '<img src="img/smiley/triste.gif" title="triste" alt="triste" />', $texte);
	$texte = str_replace(':frime:', '<img src="img/smiley/cool.gif" title="cool" alt="cool" />', $texte);
	$texte = str_replace(':rire:', '<img src="img/smiley/rire.gif" title="rire" alt="rire" />', $texte);
	$texte = str_replace(':s', '<img src="img/smiley/confus.gif" title="confus" alt="confus" />', $texte);
	$texte = str_replace(':O', '<img src="img/smiley/choc.gif" title="choc" alt="choc" />', $texte);
	$texte = str_replace(':question:', '<img src="img/smiley/question.gif" title="?" alt="?" />', $texte);
	$texte = str_replace(':exclamation:', '<img src="img/smiley/exclamation.gif" title="!" alt="!" />', $texte);

	//Mise en forme du texte
	//gras
	$texte = preg_replace('`\[g\](.+)\[/g\]`isU', '<strong>$1</strong>', $texte); 
	//italique
	$texte = preg_replace('`\[i\](.+)\[/i\]`isU', '<em>$1</em>', $texte);
	//souligné
	$texte = preg_replace('`\[s\](.+)\[/s\]`isU', '<u>$1</u>', $texte);
	//lien
	$texte = preg_replace('#http://[a-z0-9._/-]+#i', '<a href="$0">$0</a>', $texte);
	//citations
	$texte = preg_replace('`\[quote auteur=([a-z0-9A-Z._-]+) \](.+)\[/quote\]`isU', '<div id="quote">Auteur : $1 </ br> $2 </div>', $texte);
	//etc., etc.

	//On retourne la variable texte
	return $texte;
}