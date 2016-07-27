function UserAuth()
{
	var login=$("input[name='user_logname']").val();
	var password=$("input[name='user_pass']").val();
	
	$.ajax({
			type: "POST",
			url: 'http://baza-remontprofi.ru/inc/work/authorization.php',
			data: {"u_login" : login, "u_password" : password},
			beforeSend: function()
			{
				$("#loading-user-auth").fadeIn(400);
			},
			success: function(resultdata) 
			{
				resultauthdata=jQuery.parseJSON(resultdata);
																							
				if(resultauthdata.status=='ok')
				{
					$("#loading-user-auth").fadeOut(400);
				
					$("#result-user-auth").html('Вход выполнен');
					$("#result-user-auth").fadeIn(400);

                    window.location.href = 'http://baza-remontprofi.ru/'
				}
				else
				{
					$("#loading-user-auth").fadeOut(400);
				
					$("#result-user-auth").html('Ошибка');
					$("#result-user-auth").fadeIn(400);
				}
			}
	});
}