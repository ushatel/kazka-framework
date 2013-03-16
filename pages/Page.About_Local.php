<?php

  if (@preg_match("/pages/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

	include_once("includes/common/Lib.GlobalLocals.php");

class About_local extends GlobalLocals
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
				"ua" => '<a class="menu" href="/">Головна</a><a href="/News/" class="menu" >Новини</a><a href="/Project/" class="menu" >Проекти</a><a class="menu" href="/Materials/">Матеріали</a><a class="menu" href="/Companies/">Компанії</a><a class="menu" href="/Contact/">Зворотній зв`язок</a>',
				"ru" => '<a class="menu" href="/">Главная</a><a href="/News/" class="menu" >Новости</a><a href="/Project/" class="menu" >Проекты</a><a class="menu" href="/Materials/">Материалы</a><a class="menu" href="/Companies/">Компании</a><a class="menu" href="/Contact/">Обратная связь</a>',
				"en" => '<a class="menu" href="/">Main</a><a href="/News/" class="menu" >News</a><a href="/Project/" class="menu" >Projects</a><a class="menu" href="/Materials/">Materials</a><a class="menu" href="/Companies/">Companies</a><a class="menu" href="/Contact/">Feed back</a>' //class="menuSelected"
			),
			
		'TITLE' => array
			(
				"ua" => "Про нас",
				"ru" => "О нас",
				"en" => "About us"
			),

		'About_Title' => array
			(
				"ua" => "Про нас",
				"ru" => "О нас",
				"en" => "About us"
			),
			
		'About_Text' => array
			(
				"ua" => '<img src="http://chart.apis.google.com/chart?chs=220x220&cht=qr&chld=L|0&chl=http://2tag.nl/7LGV55" alt="короткий код"/>
						<p>MyXata створено групою людей, що достатньо довго надавали допомогу будівельникам 
						щодо інформаційної підтримки іх проектів в мережі інтернет. Після багаторічного аналізу їх процесів народився продукт, 
						що як ми сподіваємось може допомогти багатьом підприємствам та часним лицям в їх маленьких та великих будівничіх проектах.

						Історія назви проекту доволі банальна - автогенерована назва з сервісу продажі домених імен.
						</p>',
				"ru" => '<img src="http://chart.apis.google.com/chart?chs=220x220&cht=qr&chld=L|0&chl=http://2tag.nl/7LGV55" alt="короткий код"/>',
				"en" => '<img src="http://chart.apis.google.com/chart?chs=220x220&cht=qr&chld=L|0&chl=http://2tag.nl/7LGV55" alt="short code"/>'
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