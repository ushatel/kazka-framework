<?php

	include_once("includes/common/Lib.GlobalLocals.php");
	
class Services_Local extends GlobalLocals
{
	public $locals = array
	(			
		'Title' => array
			(
				"ua" => "",
				"ru" => "",
				"en" => ""
			),

		"Add_New_Material" => array
			(
				"ua" => "Додати матеріал",
				"ru" => "Добавить материал",
				"en" => "Add material"
			),
			
		"Edit_Material" => array
			(
				"ua" => "Редагувати матеріал",
				"ru" => "Отредактировать материал",
				"en" => "Edit material"
			),
			
		'Material_Supplier' => array
			(
				"ua" => "Постачальник",
				"ru" => "Поставщик",
				"en" => "Supplier"
			),
			
		"Material_Search_Field" => array
			(
				"ua" => "Шукати матеріали",
				"ru" => "Искать материалы",
				"en" => "Materials search"
			),
			
		'Material_Search_Btn' => array
			(
				"ua" => "Пошук",
				"ru" => "Искать",
				"en" => "Search"
			),
			

		'MENU_TOP' => array
			(
				"ua" => '<a class="menu" href="/">Головна</a><a class="menu" href="/News/">Новини</a><a href="/Project/" class="menu" >Проекти</a><a href="/Services/" class="menuSelected" >Услуги</a><a class="menu" href="/Materials/">Матеріали</a><a class="menu" href="/Companies/">Компанії</a><a class="menu" href="/Contact/">Зворотній зв`язок</a>',
				"ru" => '<a class="menu" href="/">Главная</a><a class="menu" href="/News/">Новости</a><a href="/Project/" class="menu" >Проекты</a><a href="/Services/" class="menuSelected" >Послуги</a><a class="menu" href="/Materials/">Матеріали</a><a class="menu" href="/Companies/">Компании</a><a class="menu" href="/Contact/">Обратная связь</a>',
				"en" => '<a class="menu" href="/">Main</a><a class="menu" href="/News/">News</a><a href="/Project/" class="menu" >Projects</a><a href="/Services/" class="menuSelected" >Services</a><a class="menu" href="/Materials/">Матеріали</a><a class="menu" href="/Companies/">Companies</a><a class="menu" href="/Contact/">Feed back</a>'
			),
			
		'SUB_MENU' => array
			(
				"ua" => "<a href='/Services/Latest/' >Останні послуги</a>&nbsp;<a href='/Services/Latest/Bid/' >Останні пропозиції (Bid)</a>&nbsp;<a href='/Services/Latest/Ask/' >Останні запити (Ask)</a>&nbsp;<a href='/Services/' >Усі послуги</a>",
				"ru" => "<a href='/Services/Latest/' >Последние услуги</a>&nbsp;<a href='/Services/Latest/Bid/' >Последние предложения (Bid)</a>&nbsp;<a href='/Services/Latest/Ask/' >Последние заявки (Ask)</a>&nbsp;<a href='/Services/' >Все услуги</a>",
				"en" => "<a href='/Services/Latest/' >Latest services</a>&nbsp;<a href='/Services/Latest/Bid/' >Latest Bid</a>&nbsp;<a href='/Services/Latest/Ask/' >Latest Ask</a>&nbsp;<a href='/Services/' >All services</a>",
			)
	);
	
	public function __construct()
	{
		if(Session::GetUserId() > 0)
		{
			$this->locals['SUB_MENU']["ua"] = "<a href='/News/' onClick=\"NewsEdit('', 0); return false;\" class='ajaxLink' >Додати новину</a>&nbsp;".$this->locals['SUB_MENU']["ua"];
			$this->locals['SUB_MENU']["ru"] = "<a href='/News/' onClick=\"NewsEdit('', 0); return false;\" class='ajaxLink' >Добавить новость</a>&nbsp;".$this->locals['SUB_MENU']["ru"];
			$this->locals['SUB_MENU']["en"] = "<a href='/News/' onClick=\"NewsEdit('', 0); return false;\" class='ajaxLink' >Add news</a>&nbsp;".$this->locals['SUB_MENU']["en"];
		}
	}
}

?>