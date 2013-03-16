<?php

  if (@preg_match("/pages/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

	include_once("includes/common/Lib.GlobalLocals.php");

class Profile_local extends GlobalLocals
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
		
		'Login_Title' => array
		(
			"ua" => "Аутентифікація",
			"ru" => "Аутентификация",
			"en" => "Authentification"
		),
		
		'MENU_TOP' => array
		(
			"ua" => '<a class="menu" href="/">Головна</a><a class="menu" href="/News/" >Новини</a><a href="/Project/" class="menu" >Проекти</a><a class="menu" href="/Materials/">Матеріали</a><a class="menu" href="/Companies/">Компанії</a><a class="menu" href="/Contact/">Зворотній зв`язок</a>',
			"ru" => '<a class="menu" href="/">Главная</a><a class="menu" href="/News/" >Новости</a><a href="/Project/" class="menu" >Проекты</a><a class="menu" href="/Materials/">Материалы</a><a class="menu" href="/Companies/">Компании</a><a class="menu" href="/Contact/">Обратная связь</a>',
			"en" => '<a class="menu" href="/">Main</a><a class="menu" href="/News/" >News</a><a href="/Project/" class="menu" >Projects</a><a class="menu" href="/Materials/">Materials</a><a class="menu" href="/Companies/">Companies</a><a class="menu" href="/Contact/">Feed back</a>' //class="menuSelected"
		),
		
		'SUB_MENU' => array
		(
			"ua" => "<a href='/User/Registration/'>Зареєструватись</a>",
			"ru" => "<a href='/User/Registration/'>Зарегистрироваться</a>",
			"en" => "<a href='/User/Registration/'>Register</a>"
		)
	);

	public function __construct()
	{
		if(Session::GetUserId() > 0)
		{
			$this->locals['MENU_TOP']['ua'] .= '<a class="menuSelected" style="float: right; margin-right: 20px; */ margin-top: -45px;" href="/User/"'.Session::GetUserLogin().'"/">'.Session::GetUserLogin().'</a>';
			$this->locals['MENU_TOP']['en'] .= '<a class="menuSelected" style="float: right; margin-right: 20px; */ margin-top: -45px;" href="/User/"'.Session::GetUserLogin().'"/">'.Session::GetUserLogin().'</a>';
			$this->locals['MENU_TOP']['ru'] .= '<a class="menuSelected" style="float: right; margin-right: 20px; */ margin-top: -45px;" href="/User/"'.Session::GetUserLogin().'"/">'.Session::GetUserLogin().'</a>';
		}
		else
		{
			$this->locals['SUB_MENU']['ua'] = "<a href='/User/Lost/'>Загублений пароль</a>&nbsp;" .$this->locals['SUB_MENU']['ua'];
			$this->locals['SUB_MENU']['en'] = "<a href='/User/Lost/'>Lost password</a>&nbsp;".$this->locals['SUB_MENU']['en'];
			$this->locals['SUB_MENU']['ru'] = "<a href='/User/Lost/'>Потерянный пароль</a>&nbsp;".$this->locals['SUB_MENU']['ru'];
		}
		
	}	
}
?>