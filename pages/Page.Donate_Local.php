<?php

  if (@preg_match("/pages/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

	include_once("includes/common/Lib.GlobalLocals.php");

class Main_local extends GlobalLocals
{
	public $locals = array(
		'Login' => array (
			"ua" => "&#65533;&#65533;&#65533;&#65533;", 
			"en" => "Login",
			"ru" => "&#65533;&#65533;&#65533;&#65533;&#65533;"
		),
		
		'MAIN_PROJECTS' => array (
			"ua" => "Проекти",
			"en" => "Projects",
			"ru" => "Проекты"
		),
		
		'MAIN_MATERIALS' => array (
			"ua" => "Матеріали",
			"ru" => "Материалы",
			"en" => "Materials"
		),
		
		'MAIN_NEWS' => array (
			"ua" => "Новини",
			"ru" => "Новости",
			"en" => "News"
		),
		
		'MAIN_FRIENDS' => array (
			"ua" => "Друзі",
			"ru" => "Друзья",
			"en" => "Friends"
		),

		'MENU_TOP' => array
		(
			"ua" => '<a class="menu" href="/">Головна</a><a href="/Project/" class="menu" >Проекти</a><a class="menu" href="/Materials/">Матеріали</a><a class="menu" href="/Companies/">Компанії</a><a class="menu" href="/Contact/">Зворотній зв`язок</a>',
			"ru" => '<a class="menu" href="/">Главная</a><a href="/Project/" class="menu" >Проекты</a><a class="menu" href="/Materials/">Материалы</a><a class="menu" href="/Companies/">Компании</a><a class="menu" href="/Contact/">Обратная связь</a>',
			"en" => '<a class="menu" href="/">Main</a><a href="/Project/" class="menu" >Projects</a><a class="menu" href="/Materials/">Materials</a><a class="menu" href="/Companies/">Companies</a><a class="menu" href="/Contact/">Feed back</a>' //class="menuSelected"
		),
		
		'Donate_Ammount' => array
			(
				"ua" => "Сума",
				"ru" => "Сумма",
				"en" => "Ammount"
			),
		
		'Donate_Phone' => array
			(
				"ua" => "Номер телефону",
				"ru" => "Номер телефона",
				"en" => "Phone number"
			),
		
		'Donate_Comment' => array
			(
				"ua" => "Коментарій",
				"ru" => "Комментарий",
				"en" => "Comment"
			),
		
		'Donate_Button' => array
			(
				"ua" => "Пожертвувати",
				"ru" => "Пожертвовать",
				"en" => "Donate"
			),
			
		'Donate_Thanks' => array
			(
				"ua" => "Шіро дякуємо! Ці кошти пойдуть на розвиток проекту.",
				"ru" => "Благодарим за помощь. Эти деньги внесут вклад в развитие проекта.",
				"en" => "This money will go to the projects needs."
			),
			
		'SUB_MENU' => array
			(
				"ua" => "",
				"ru" => "",
				"en" => ""
			)
	);
	
}
?>