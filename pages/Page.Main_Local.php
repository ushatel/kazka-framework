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
		
		'Title' => array
		(
			"ua" => "Будівельний портал",
			"ru" => "Строительный портал",
			"en" => "Developement portal"
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

		'SUB_MENU' => array
		(
			"ua" => "<span style='padding-left: 7px;'>Ласкаво просимо, до фази бета тестування</span>",//"<a href='/Companies/Add/' onClick='EditCompanyClick(); return false;' class='ajaxLink' >Додати компанію</a>&nbsp;<a href='/Companies/Latest/' >Останні компанії</a>&nbsp;<a href='/Companies/' >Всі компанії</a>",
			"ru" => "<span style='padding-left: 7px;'>Добро пожаловать, на фазу бета тестирования</span>",//"<a href='/Companies/Add/' onClick='EditCompanyClick(); return false;' class='ajaxLink' >Добавить компанию</a>&nbsp;<a href='/Companies/Latest/' >Последние компании</a>&nbsp;<a href='/Companies/'>Все компании</a>",
			"en" => "<span style='padding-left: 7px;'>Wellcome, to the beta-testing phase</span>",//"<a href='/Companies/Add/' onClick='EditCompanyClick(); return false;' class='ajaxLink' >Add company</a>&nbsp;<a href='/Companies/Latest/' >Latest companies</a>&nbsp;<a href='/Companies/'>All companies</a>",
		),

		'MENU_TOP' => array
			(
				"ua" => '<a class="menuSelected" href="/">Головна</a><a class="menu" href="/News/">Новини</a><a href="/Project/" class="menu" >Проекти</a><a class="menu" href="/Materials/">Матеріали</a><a class="menu" href="/Companies/">Компанії</a><a class="menu" href="/Contact/">Зворотній зв`язок</a>',
				"ru" => '<a class="menuSelected" href="/">Главная</a><a class="menu" href="/News/">Новости</a><a href="/Project/" class="menu" >Проекты</a><a class="menu" href="/Materials/">Материалы</a><a class="menu" href="/Companies/">Компании</a><a class="menu" href="/Contact/">Обратная связь</a>',
				"en" => '<a class="menuSelected" href="/">Main</a><a class="menu" href="/News/">News</a><a href="/Project/" class="menu" >Projects</a><a class="menu" href="/Materials/">Materials</a><a class="menu" href="/Companies/">Companies</a><a class="menu" href="/Contact/">Feed back</a>'
			)
	);
	
	
}
?>