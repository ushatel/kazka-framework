<?php  

	include_once("includes/common/Lib.GlobalLocals.php");
	
class Material_Local extends GlobalLocals
{
	public $locals = array
	(			
		'Title' => array
			(
				"ua" => "Матеріал %s",
				"ru" => "Материал %s",
				"en" => "Material %s"
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
			
		'Edit_Material' => array
			(
				"ua" => "Редагувати",
				"ru" => "Редактировать",
				"en" => "Edit"
			),
			
		'Add_New_Material' => array 
			(
				"ua" => "Додати",
				"ru" => "Добавить",
				"en" => "Add"
			),
			
		'MENU_TOP' => array
			(
				"ua" => '<a class="menu" href="/">Головна</a><a class="menu" href="/News/">Новини</a><a href="/Project/" class="menu" >Проекти</a><a class="menuSelected" class="menu" href="/Materials/">Матеріали</a><a class="menu" href="/Companies/">Компанії</a><a class="menu" href="/Contact/">Зворотній зв`язок</a>',
				"ru" => '<a class="menu" href="/">Главная</a><a class="menu" href="/News/">Новости</a><a href="/Project/" class="menu" >Проекты</a><a class="menuSelected" href="/Materials/">Материалы</a><a class="menu" href="/Companies/">Компании</a><a class="menu" href="/Contact/">Обратная связь</a>',
				"en" => '<a class="menu" href="/">Main</a><a class="menu" href="/News/">News</a><a href="/Project/" class="menu" >Projects</a><a class="menuSelected" href="/Materials/">Materials</a><a class="menu" href="/Companies/">Companies</a><a class="menu" href="/Contact/">Feed back</a>'
			),
			
		'SUB_MENU' => array
			(
				"ua" => "<a href='/Materials/Latest/' >Останні матеріали</a>&nbsp;<a href='/Materials/' >Всі матеріали</a>",
				"ru" => "<a href='/Materials/Latest/' >Последние материалы</a>&nbsp;<a href='/Materials/'>Все материалы</a>",
				"en" => "<a href='/Materials/Latest/' >Latest materials</a>&nbsp;<a href='/Materials/'>All materials</a>",
			)
	);
	
	public function __construct()
	{
		if(Session::GetUserId() > 0)
		{
			$this->locals['SUB_MENU']["ua"] = "<a href='/Materials/' onClick='AjaxMaterialClick(); return false;' class='ajaxLink' >Додати матеріал</a>&nbsp;".$this->locals['SUB_MENU']["ua"]."&nbsp;<a href='/Materials/Yours/'>Ваші матеріали</a>"; 
			$this->locals['SUB_MENU']["ru"] = "<a href='/Materials/' onClick='AjaxMaterialClick(); return false;' class='ajaxLink' >Добавить материал</a>&nbsp;".$this->locals['SUB_MENU']["ru"]."&nbsp;<a href='/Materials/Yours/'>Ваши материалы</a>";
			$this->locals['SUB_MENU']["en"] = "<a href='/Materials/' onClick='AjaxMaterialClick(); return false;' class='ajaxLink' >Add material</a>&nbsp;".$this->locals['SUB_MENU']["en"]."&nbsp;<a href='/Materials/Yours/'>Your materials</a>";
		}
	}
}

?>