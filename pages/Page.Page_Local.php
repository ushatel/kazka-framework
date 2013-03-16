<?

	

class Page_Local extends GlobalLocals
{
	public $locals = array(
	
	'Title' => array (
		"ua" => "Реєстрація", 
		"en" => "Registration page",
		"ru" => "Регистрация"
		),

	'Terms_Of_Use_Title' => array (
		"ua" => "Логін", 
		"en" => "Login",
		"ru" => "Логин"
		),
		
	'Terms_Body' => array (
		"ua" => '',
		"en" => '',
		"ru" => ''
		),
		
	'Terms_Preambula' => array ( 
		"ua" => '',
		"ru" => '',
		"en" => ''
	),

	'MENU_TOP' => array
		(
			"ua" => '<a class="menu" href="/">Головна</a><a href="/Project/" class="menu" >Проекти</a><a class="menu" href="/Materials/">Матеріали</a><a class="menu" href="/Companies/">Компанії</a><a class="menu" href="/Contact/">Зворотній зв`язок</a><a class="menuSelected" style="float: right; margin-right: 20px;" href="/User/Registration/">Умови використання</a>',

			"ru" => '<a class="menu" href="/">Главная</a><a href="/Project/" class="menu" >Проекты</a><a class="menu" href="/Materials/">Материалы</a><a class="menu" href="/Companies/">Компании</a><a class="menu" href="/Contact/">Обратная связь</a><a class="menuSelected" style="float: right; margin-right: 20px;" href="/User/Registration/">Условия использования</a>',

			"en" => '<a class="menu" href="/">Main</a><a href="/Project/" class="menu" >Projects</a><a class="menu" href="/Materials/">Materials</a><a class="menu" href="/Companies/">Companies</a><a class="menu" href="/Contact/">Feed back</a><a class="menuSelected" style="float: right; margin-right: 20px;" href="/User/Registration/">Terms of Use</a>' //class="menuSelected"
		)
	);
	
	
	public function __construct() 
	{
		// Non Static initializators
		$this->locals['Registration_Letter_Body']['ua'] = "Дуже дякуємо за інтерес до системи ".$this->GetGlobalValue("Program_Name").". Будьте ласкаві, натисніть посилання для завершення регістрації: %s";
		$this->locals['Registration_Letter_Body']['en'] = "Thank you for interest to the system ".$this->GetGlobalValue("Program_Name").". Please, click the link bellow to finish registration: %s";
		$this->locals['Registration_Letter_Body']['ru'] = "Благодарим за проявленный интерес к системе ".$this->GetGlobalValue("Program_Name").". Пожалуйста, перейдите по ссылке для окончания регистрации: %s";
		
		$this->locals['Registration_Letter_Subject']['ua'] = "Підтвердження регістрації у системі ".$this->GetGlobalValue("Program_Name");
		$this->locals['Registration_Letter_Subject']['en'] = "Account activation in the ".$this->GetGlobalValue("Program_Name")." system";
		$this->locals['Registration_Letter_Subject']['ru'] = "Подтверждение регистрации в системе ".$this->GetGlobalValue("Program_Name")."";
	}
}

?>