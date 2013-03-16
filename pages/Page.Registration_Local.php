<?

	

class Registration_Local extends GlobalLocals
{
	public $locals = array(
	
	'Title' => array (
		"ua" => "Реєстрація", 
		"en" => "Registration page",
		"ru" => "Регистрация"
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
	
	'Confirm_password' => array (
		"ua" => "Підтвердження паролю", 
		"en" => "Confirm password",
		"ru" => "Подтверждение пароля"
		),
		
	'Email' => array (
		"ua" => "Електрона пошта", 
		"en" => "Email",
		"ru" => "Электронная почта"
		),

	'First_Name' => array (
		"ua" => "Ім`я", 
		"en" => "First Name",
		"ru" => "Имя"
		),

	'Second_Name' => array (
		"ua" => "Прізвище", 
		"en" => "Second Name",
		"ru" => "Фамилия"
		),
		
	'Third_Name' => array (
		"ua" => "По батькові", 
		"en" => "Patronymic Name",
		"ru" => "Отчество"
		),

	'Company' => array (
		"ua" => "Назва компанії", 
		"en" => "Company",
		"ru" => "Компания"
		),
		
	'Public_Email' => array (
		"ua" => "Email для загального доступу", 
		"en" => "Public Email",
		"ru" => "Email для общего доступа"
		),

	'Submit_Text' => array (
		"ua" => "Зареєструватись", 
		"en" => "Register",
		"ru" => "Зарегистрироваться"
		),

	'Submit_Post_Registration' => array (
		"ua" => "підтвердити реєстрацію",
		"ru" => "подтвердить регистрацию",
		"en" => "registration submit"
		),

	'MENU_TOP' => array
		(
			"ua" => '<a class="menu" href="/">Головна</a><a href="/News/" class="menu" >Новини</a><a href="/Project/" class="menu" >Проекти</a><a class="menu" href="/Materials/">Матеріали</a><a class="menu" href="/Companies/">Компанії</a><a class="menu" href="/Contact/">Зворотній зв`язок</a><a class="menuSelected" style="float: right; margin-right: 20px; */ margin-top: -45px;" href="/User/Registration/">Реєстрація</a>',
			"ru" => '<a class="menu" href="/">Главная</a><a href="/News/" class="menu" >Новости</a><a href="/Project/" class="menu" >Проекты</a><a class="menu" href="/Materials/">Материалы</a><a class="menu" href="/Companies/">Компании</a><a class="menu" href="/Contact/">Обратная связь</a><a class="menuSelected" style="float: right; margin-right: 20px; */ margin-top: -45px;" href="/User/Registration/">Регистрация</a>',
			"en" => '<a class="menu" href="/">Main</a><a href="/News/" class="menu" >News</a><a href="/Project/" class="menu" >Projects</a><a class="menu" href="/Materials/">Materials</a><a class="menu" href="/Companies/">Companies</a><a class="menu" href="/Contact/">Feed back</a><a class="menuSelected" style="float: right; margin-right: 20px; */ margin-top: -45px;" href="/User/Registration/">Registration</a>' //class="menuSelected"
		),

	'Terms_Of_Use_Checkbox' => array
		(
			"ua" => 'Підтвердіть, що ознайомились із <a href="/Terms/" target="_blank">Умовами</a>',
			"ru" => 'Подтвердите, что ознакомились с <a href="/Terms/" target="_blank">Условиями</a>',
			"en" => 'Accept that you have read the <a href="/Terms/" target="_blank">Terms of Use</a>'
		),

	'SUB_MENU' => array
		(
			"ua" => "<a href='/User/Lost/'>Загублений пароль</a>&nbsp;<a href='/Contact/'>Проблеми із доступом</a>",
			"ru" => "<a href='/User/Lost/'>Потерянный пароль</a>&nbsp;<a href='/Contact/'>Проблемы с доступом</a>",
			"en" => "<a href='/User/Lost/'>Lost password</a>&nbsp;<a href='/Contact/'>Problems with access</a>"
		),

	'Validation_Terms_Error' => array
		(
			"ua" => "Ви маєте ознайомитись із Умовами. У будь якому разі ви підпадаєте під їхню дію на цьому Сервісі.",
			"ru" => "Вы должны ознакомиться с Условиями. В каком угодно случае вы подпадаєте под их действие на этом Сервисе.",
			"en" => "You should accept the Terms of Use. In any case you covered these terms on this Service."
		),

	'Validation_Email_Error' => array(
		"ua" => "Ви помилились при вводі адреси електроної пошти",
		"en" => "You`ve made error entering the mail box address",
		"ru" => "Вы ошиблись при вводе электронной почты"
		),
		
	'Validation_Password_Error' => array(
		"ua" => "Пароль та його підтвердження не збігаються",
		"en" => "Password and Password confirmation does not match",
		"ru" => "Пароль и его подтверждение не совпадают"
		),
		
	'Validation_Password_Length_Error' => array(
		"ua" => "Пароль занадто короткий (не меньш ніж 7 знаків)",
		"en" => "Password is too short (not less then 7 symbols)", 
		"ru" => "Пароль слишком короткий (не менее 7 знаков)"
		),
		
	'Validation_FirstName_IsTooShort_Error' => array(
		"ua" => "Поле Ім`я занадто коротке (не меньше ніж 3 знаки)",
		"en" => "First name is too short (not less then 3 symbols)",
		"ru" => "Поле Имя слишком коротко (не менее 3 знаков)"
		),
		
	'Validation_SecondName_IsTooShort_Error' => array(
		"ua" => "Поле Прізвище занадто коротке (не меньше ніж 3 знаки)",
		"en" => "Second name is too short (not less then 3 symbols)",
		"ru" => "Поле Фамілія слишком коротко (не менее 3 знаков)"
		),
		
	'Validation_Login_IsTooShort_Error' => array(
		"ua" => "Поле Login занадто коротке (не меньше ніж 3 знаки)",
		"en" => "Login is too short (not less then 3 symbols)",
		"ru" => "Поле Логин слишком коротко (не менее 3 знаков)"
		),
		
	'Validation_Login_IsExists_Error' => array(
		"ua" => "Користувач із таким логіном вже існує",
		"en" => "The user with the login already exists",
		"ru" => "Пользователь с таким логином уже сущестует"
		),
		
	'Thank_You_Registering' => array(
		"ua" => "Дякуємо, що вибрали нашу систему. <br>На вашу поштову скриньку відправлено лист активації акаунту.",
		"en" => "Thank you for use our system. <br>Please, check your mail for the account activation letter.",
		"ru" => "Спасибо, что выбрали нашу систему. <br>На ваш почтовый ящик отправлен лист активации учетной записи."
		),
		
	'Registration_Error' => array(
		"ua" => "Критична помилка. Будьте ласкаві, спробуйте повторити спробу пізніше.",
		"en" => "System failure. Please, try again later.",
		"ru" => "Критическая ошибка. Пожалуйста, повторите попытку позже."
		),
		
	'Confirmation_Submit_Code_Validation' => array(
		"ua" => "Будьте ласкаві, вкажіть ім`я користувача",
		"en" => "Confirm your login, please.",
		"ru" => "Пожалуйста, укажите имя пользователя"
		),
		
	'Validation_Confirmation_Login_IsNotExists_Error' => array (
		"ua" => "Логін не знайдено",
		"en" => "Login is not exists",
		"ru" => "Логин не существует"
		),
		
	'Confirmation_Submit_Code_Validation_OK' => array (
		"ua" => "Дякуємо, ви успішно зареєструвались. Тепер ви можете почати роботу.",
		"en" => "Thank you. You have been succesfully registered in the system.",
		"ru" => "Благодарим за регистрацию. Теперь вы можете начать работу."
		),
		
	'Confirmation_Submit_Code_Validation_Error' => array (
		"ua" => "Ваш код не дійсний. Будьте ласкаві, зареєструйтесь ще раз.",
		"en" => "Validation code is not valid. Register one more time, please.",
		"ru" => "Код подтверждения не верен. Пожалуйста, зарегистрируйтесь еще раз."
		)
	);
	
	
	public function __construct() 
	{
		// Non Static initializators
		$this->locals['Registration_Letter_Body']['ua'] = "<img src=3D'http://www.myxata.com/images/mid_top.png' height=3D'65' width=3D'700' /><br>Привіт, %s.<br/>Дуже дякуємо за інтерес до системи ".$this->GetGlobalValue("Program_Name").". <br><br>Будьте ласкаві, натисніть посилання для завершення реєстрації: %s<br/><br/><span style='font-style:italic;font-size:15px;'>Із повагою, команда ".$this->GetGlobalValue("Program_Name")."</span><hr>";
		$this->locals['Registration_Letter_Body']['en'] = "<img src=3D'http://www.myxata.com/images/mid_top.png' height=3D'65' width=3D'700' /><br>Hi, %s.<br/>Thank you for interest to the system ".$this->GetGlobalValue("Program_Name").". <br><br>Please, click the link bellow to finish registration: %s<br/><br/><span style='font-style:italic;font-size:15px;'>With respect, the Team of the ".$this->GetGlobalValue("Program_Name")."</span><hr>";
		$this->locals['Registration_Letter_Body']['ru'] = "<img src=3D'http://www.myxata.com/images/mid_top.png' height=3D'65' width=3D'700' /><br>Привет, %s.<br/>Благодарим за проявленный интерес к системе ".$this->GetGlobalValue("Program_Name").". <br><br>Пожалуйста, перейдите по ссылке для окончания регистрации: %s<br/><br/><span style='font-style:italic;font-size:15px;'>С уважением, команда ".$this->GetGlobalValue("Program_Name")."</span><hr>";

		$this->locals['Registration_Letter_Subject']['ua'] = "Підтвердження реєстрації у системі ".$this->GetGlobalValue("Program_Name");
		$this->locals['Registration_Letter_Subject']['en'] = "Account activation in the ".$this->GetGlobalValue("Program_Name")." system";
		$this->locals['Registration_Letter_Subject']['ru'] = "Подтверждение регистрации в системе ".$this->GetGlobalValue("Program_Name")."";
	}
}

?>