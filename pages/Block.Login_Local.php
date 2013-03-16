<?php

class Login_Local extends GlobalLocals
{
	public $locals = array(
	
	'Block_Title' => array (
		"ua" => "Заголовок блоку",
		"en" => "Block`s title",
		"ru" => "Заголовок блока"
		),

	'Login' => array (
		"ua" => "Логін", 
		"en" => "Login",
		"ru" => "Логин"
		),

	'Password' => array (
		"ua" => "Пароль", 
		"en" => "Password",
		"ru" => "Пароль"
		),

	'Old_Password' => array (
		"ua" => "Старий пароль",
		"ru" => "Старый пароль",
		"en" => "Old Password"
		),

	'Password_Confirm' => array (
		"ua" => "Підтвердження паролю",
		"ru" => "Подтверждение пароля",
		"en" => "Password confirm"
		),

	'Change_Password' => array (
		"ua" => "Змінити пароль",
		"ru" => "Сменить пароль",
		"en" => "Change password"
		),
		
	'Login_Btn' => array (
		"ua" => "Увійти",
		"en" => "Login",
		"ru" => "Войти"
		),
		
	'Lost_Login' => array
		(
			"ua" => "Повідомити",
			"ru" => "Сообщить",
			"en" => "Inform"
		),
		
	'Lost_Login_Title' => array 
		(
			"ua" => "Загублений пароль",
			"ru" => "Потерянный пароль",
			"en" => "Lost password"
		),
		
	'Save_Profile' => array
		(
			"ua" => "Зберегти",
			"en" => "Save Profile",
			"ru" => "Сохранить"
		),
		
	'Save_OK' => array
		(
			"ua" => "Зміни збережено",
			"ru" => "Изменения сохранены",
			"en" => "Changes are saved"
		),
		
	'PUBLIC_EMAIL' => array
		(
			"ua" => "Публичний Email",
			"ru" => "Публичный Email",
			"en" => "Public Email"
		),
		
	'PUBLIC_ADDRESS' => array
		(
			"ua" => "Публична адреса",
			"en" => "Public address",
			"ru" => "Публичный адрес"
		),
		
	'PHONE_NUMBER' => array
		(
			"ua" => "Телефон",
			"ru" => "Телефон",
			"en" => "Phone"
		),
		
	'PHONE2_NUMBER' => array
		(
			"ua" => "Телефон2",
			"ru" => "Телефон2",
			"en" => "Phone2"
		),
		
	'COMPANY' => array
		(
			"ua" => "Компанія",
			"ru" => "Компания",
			"en" => "Company"
		),
		
	'LOGIN_FIELD' => array
		(
			"ua" => "Логін",
			"ru" => "Логин",
			"en" => "Login"
		),
		
	'DESCRIPTION' => array
		(
			"ua" => "Опис",
			"ru" => "Описание",
			"en" => "Description"
		),
		
	'Login_Lost' => array
		(
			"ua" => "Загублений пароль",
			"ru" => "Потерянный пароль",
			"en" => "Lost Password"
		),
		
	'Login_Lost_Successfully_Approved' => array
		(
			"ua" => "На вашу електрону адресу надіслано лист активації. <br />Будьте ласкаві, підтвердіть, що являєтесь власником аккаунту та встановіть новий пароль. <br/> Якщо у вас виникли якісь труднощі, повідомте нас скориставшись <a href='/Contact/'>зворотнім зв`язком</a>",
			"ru" => "На вашу электронную почту отправлено письмо активации. <br />Пожалуйста, подтвердите, что являетесь собственником учетной записи и задайте новый пароль. <br/>  В случае, если возникли какие-либо трудности, пожалуйста, сообщите нам воспользовавщись <a href='/Contact/'>обратной связью</a>",
			"en" => "Please, check your mail to activate the account again. <br />Please, approve that you own the account and put the new password again. <br/> If you have any difficulties, please, <a href='/Contact/'>contact us</a>"
		),
		
	'Submit_Post_Registration' => array
		(
			"ua" => "підтвердити реєстрацию",
			"ru" => "подтвердить регистрацию",
			"en" => "submit registration"
		),

	'Submit_Lost_Password' => array
		(
			"ua" => "підтвердити втрачений пароль",
			"ru" => "подтвердить потерянный пароль",
			"en" => "submit lost password"
		),
		
	

	'Update_Password_Succeed' => array
		(
			"ua" => "Пароль успішно змінено.",
			"ru" => "Пароль успешно изменен.",
			"en" => "Password changed."
		),
		
	'Validation_Lost_Login_Is_Not_Found' => array 
		(
			"ua" => "Вказаний логін не знайдено",
			"ru" => "Указаный логин не найден",
			"en" => "The login is not found"
		),
		
	'Validation_Email_Error' => array (
			"ua" => "Невірний email. Перевірте поле",
			"ru" => "Неверный email. Проверьте поле",
			"en" => "Wrong email. Please check the field"
		),
		
	'Validation_Login_IsNotFound_Error' => array (
		"ua" => "Вказаний логін не знайдено, або невірний пароль",
		"en" => "Vrong password or login is not found",
		"ru" => "Неверный пароль или логин не найден"
		),

	'Validation_Incorrect_Password' => array (
		"ua" => "Пароль не вірний",
		"en" => "Password is incorrect",
		"ru" => "Не верный пароль"
		),

	'Validation_Incorrect_New_Password' => array
		(
		"ua" => "Пароль та його підтвердження не збігаються",
		"en" => "Password and the confirmation is not correct",
		"ru" => "Пароль и его подтверждение не коректны"
		)

	);
	
	public function __construct() 
	{
		// Non Static initializators
		/*
		$this->locals['Registration_Letter_Body']['ua'] = "Дуже дякуємо за інтерес до системи ".$this->GetGlobalValue("Program_Name").". Будьте ласкаві, натисніть посилання для завершення регістрації: %s";
		$this->locals['Registration_Letter_Body']['en'] = "Thank you for interest to the system ".$this->GetGlobalValue("Program_Name").". Please, click the link bellow to finish registration: %s";
		$this->locals['Registration_Letter_Body']['ru'] = "Благодарим за проявленный интерес к системе ".$this->GetGlobalValue("Program_Name").". Пожалуйста, перейдите по ссылке для окончания регистрации: %s";
		*/
		
		$this->locals['Registration_Letter_Body']['ua'] = "<br>Привіт, %s.<br/>Дуже дякуємо за інтерес до системи ".$this->GetGlobalValue("Program_Name").". <br><br>Будьте ласкаві, натисніть посилання для завершення реєстрації: %s<br/><br/><span style='font-style:italic;font-size:15px;'>Із повагою, команда ".$this->GetGlobalValue("Program_Name")."</span><hr>";
		$this->locals['Registration_Letter_Body']['en'] = "<br>Hi, %s.<br/>Thank you for interest to the system ".$this->GetGlobalValue("Program_Name").". <br><br>Please, click the link bellow to finish registration: %s<br/><br/><span style='font-style:italic;font-size:15px;'>With respect, the Team of the ".$this->GetGlobalValue("Program_Name")."</span><hr>";
		$this->locals['Registration_Letter_Body']['ru'] = "<br>Привет, %s.<br/>Благодарим за проявленный интерес к системе ".$this->GetGlobalValue("Program_Name").". <br><br>Пожалуйста, перейдите по ссылке для окончания регистрации: %s<br/><br/><span style='font-style:italic;font-size:15px;'>С уважением, команда ".$this->GetGlobalValue("Program_Name")."</span><hr>";

		
		/*
		$this->locals['Registration_Letter_Subject']['ua'] = "Підтвердження регістрації у системі ".$this->GetGlobalValue("Program_Name");
		$this->locals['Registration_Letter_Subject']['en'] = "Account activation in the ".$this->GetGlobalValue("Program_Name")." system";
		$this->locals['Registration_Letter_Subject']['ru'] = "Подтверждение регистрации в системе ".$this->GetGlobalValue("Program_Name")."";
		*/
		
	}
}

?>