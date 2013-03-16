<?php

  if (@preg_match("includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");
    
    include_once("includes/common/Lib.ILocalizator.php");

	/** 
	 *	Library is the package with the common library functionality
	 *
	 *  @package Library.pkg
	 */
	 
 	/** 
	 *	Class GlobalLocals 
	 *
	 *  Цей класс реалізує глобальні локалізовані назви та функціонал задля роботи із локалізацією
	 *
	 *  @package Library.pkg
	 */

	class GlobalLocals implements ILocalizator
	{
		protected static $gLocals = array(
		
			"Program_Name" => array (
				"ua" => "MyXata",
				"ru" => "MyXata",
				"en" => "MyXata"
			),
			
			"Donate_Us" => array (
				"ua" => "Підтримай нас",
				"ru" => "Поддержи нас",
				"en" => "Donate"
			),
			
			"About_Us" => array (
				"ua" => "Про нас",
				"ru" => "О нас",
				"en" => "About"
			),
			
			'Terms_Of_Use' => array (
				"ua" => "Умови",
				"ru" => "Условия",
				"en" => "Terms of Use"
			),
			
			'Firefox' => array (
				"ua" => "Найкраще в <a href=\"http://www.mozilla.com/uk/firefox/\"><img width='16' height='16' src='http://www.mozilla.com/favicon.ico' title='Firefox'/></a>",
				"ru" => "Красивее в <a href=\"http://www.mozilla.com/ru/firefox/\"><img width='16' height='16' src='http://www.mozilla.com/favicon.ico' title='Firefox'/></a>",
				"en" => "Better with <a href=\"http://www.mozilla.com/firefox/\"><img width='16' height='16' src='http://www.mozilla.com/favicon.ico' title='Firefox'/></a>"
			),
			
			"Login_Title" => array (
				"ua" => "Логін",
				"ru" => "Логин",
				"en" => "Login"
			),
			
			"Register_Or" => array
			(
				"ua" => "або",
				"ru" => "или",
				"en" => "or"
			),
			
			"Login_Title" => array
			(
				"ua" => "Увійти",
				"ru" => "Войти",
				"en" => "Login"
			),
			
			"Register_Title" => array
			(
				"ua" => "зареєструватись",
				"ru" => "зарегистироваться",
				"en" => "register"
			),
			
			"Children_Security" => array
			(
				"ua" => "<a href='http://www.google.com.ua/familysafety/'>Безпека дітей в Інтернет</a>",
				"ru" => "<a href='http://www.google.ru/familysafety/'>Безопасность детей в Интернет</a>",
				"en" => "<a href='http://www.google.com/familysafety/'>Security of children in the Internet</a>"
			),
			
			"Copy" => array
			(
				"ua" => "&copy;&nbsp;MyXata 2011&nbsp;Всі права захищені",
				"ru" => "&copy;&nbsp;MyXata 2011&nbsp;Все права защищены",
				"en" => "&copy;&nbsp;MyXata 2011&nbsp;All rights reserved"
			)
			
		);

	/**
	 *  Повертає значення локалізованої змінної
	 */
	public function GetLocalValue($property, $lang = "")
	{
		if($lang == "") 
		{
			$lang = Session::GetLang();
		} 

		return $this->locals[$property][$lang];
	}
		
	/**
	 *  Повертає значення локалізованої змінної
	 */
	public function GetGlobalValue($property, $lang = "")
	{
		if($lang == "") 
		{
			$lang = Session::GetLang();
		}

		return self::$gLocals[$property][$lang];
	}
	
	/**
	 *  Повертає значення локалізованої змінної (статичний)
	 */
	public static function GetStaticGlobalValue($property, $lang = "")
	{
		if($lang == "") 
		{
			$lang = Session::GetLang();
		}
		
		return self::$gLocals[$property][$lang];
	}


}

?>