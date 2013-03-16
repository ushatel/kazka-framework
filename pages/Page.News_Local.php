<?php

	include_once("includes/common/Lib.GlobalLocals.php");
	
class News_Local extends GlobalLocals
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
			
		'News_Add_New' => array
			(
				"ua" => "Додати новину",
				"ru" => "Добавить новость",
				"en" => "Add news"
			),
			
		'News_Edit_Title' => array
			(
				"ua" => "Редагувати новину",
				"ru" => "Редактировать новость",
				"en" => "Edit news"
			),
			
		'Are_You_Sure_Delete_News' => array
			(
				"ua" => "Чи ви впевнені, що бажаєте видалити вказану новину?",
				"ru" => "Вы уверены, что хотите удалить указанную новость?",
				"en" => "Are you sure to delete the news?"
			),

		'MENU_TOP' => array
			(
				"ua" => '<a class="menu" href="/">Головна</a><a class="menuSelected" class="menu" href="/News/">Новини</a><a href="/Project/" class="menu" >Проекти</a><a class="menu" href="/Materials/">Матеріали</a><a class="menu" href="/Companies/">Компанії</a><a class="menu" href="/Contact/">Зворотній зв`язок</a>',
				"ru" => '<a class="menu" href="/">Главная</a><a class="menuSelected" href="/News/">Новости</a><a href="/Project/" class="menu" >Проекты</a><a class="menu" href="/Materials/">Матеріали</a><a class="menu" href="/Companies/">Компании</a><a class="menu" href="/Contact/">Обратная связь</a>',
				"en" => '<a class="menu" href="/">Main</a><a class="menuSelected" href="/News/">News</a><a href="/Project/" class="menu" >Projects</a><a class="menu" href="/Materials/">Матеріали</a><a class="menu" href="/Companies/">Companies</a><a class="menu" href="/Contact/">Feed back</a>'
			),
			
		'SUB_MENU' => array
			(
				"ua" => "<a href='/News/Latest/' >Останні новини</a>&nbsp;<a href='/News/' >Всі новости</a>",
				"ru" => "<a href='/News/Latest/' >Последние новости</a>&nbsp;<a href='/News/'>Все новости</a>",
				"en" => "<a href='/News/Latest/' >Latest news</a>&nbsp;<a href='/News/'>All news</a>",
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