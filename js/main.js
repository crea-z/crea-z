/* ALERTES */

jQuery($)
{
	var alert = $('#alert');
	/*Ne pas oublier de mettre cette div en display none dans le css*/
	if(alert.length > 0)
	{
		alert.hide().slideDown(500).delay(3000).slideUp(); /*S'ouvre et se referme au bout de 3 secondes*/
	}
}