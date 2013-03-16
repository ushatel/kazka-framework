<?

	include_once("includes/common/Lib.GlobalLocals.php");
	
class Project_Local extends GlobalLocals
{
	public $locals = array
	(
	
		'Title' => array
			(
				"ua" => "Проект",
				"ru" => "Проект",
				"en" => "Project"
			),
	
		'Title1' => array
			(
				"ua" => "Проект: Вкажіть ім`я",
				"ru" => "Проект: Укажите имя",
				"en" => "Project: Put the name"
			),
			
		'Title2' => array
			(
				"ua" => "Проект: Додати кроки",
				"ru" => "Проект: Добавить шаги",
				"en" => "Project: Create steps"
			),
			
		'Add_Landmark' => array
			(
				"ua" => "Додати віху",
				"ru" => "Добавить веху",
				"en" => "Add landmark"
			),

		
		'MENU_TOP' => array
			(
				"ua" => '<a class="menu" href="/">Головна</a><a class="menu" href="/News/" >Новини</a><a class="menuSelected" href="/Project/" >Проекти</a><a class="menu" href="/Materials/">Матеріали</a><a class="menu" href="/Companies/">Компанії</a><a  class="menu" href="/Contact/">Зворотній зв`язок</a>',
				"ru" => '<a class="menu" href="/">Главная</a><a class="menu" href="/News/" >Новости</a><a class="menuSelected" href="/Project/">Проекты</a><a class="menu" href="/Materials/">Материалы</a><a class="menu" href="/Companies/">Компании</a><a class="menu" href="/Contact/">Обратная связь</a>',
				"en" => '<a class="menu" href="/">Main</a><a class="menu" href="/News/" >News</a><a class="menuSelected" href="/Project/">Projects</a><a class="menu" href="/Materials/">Materials</a><a class="menu" href="/Companies/">Companies</a><a class="menu" href="/Contact/">Feed back</a>'
			),

		'SUB_MENU' => array
			(
				"ua" => "<a href='/Project/Latest/' >Останні проекти</a>&nbsp;<a href='/Project/All/' >Всі проекти</a>",
				"ru" => "<a href='/Project/Latest/' >Послединие проекты</a>&nbsp;<a href='/Project/All/'>Все проекты</a>",
				"en" => "<a href='/Project/Latest/' >Latest projects</a>&nbsp;<a href='/Project/All/'>All projects</a>"
			),
			
		'Projects_Add_Step_Button' => array
			(
				"ua" => "Додати крок",
				"ru" => "Добавить шаг",
				"en" => "Add step"
			),
			
		'Projects_Name' => array
			(
				"ua" => "Назва проекту",
				"ru" => "Название проекта",
				"en" => "Project name"
			),
			
		'Projects_Type_Name' => array
			(
				"ua" => "Тип проекту",
				"ru" => "Тип проекта",
				"en" => "Type Name"
			),
			
		'Projects_Country' => array
			(
				"ua" => "Країна",
				"ru" => "Страна",
				"en" => "Country"
			),
			
		'Projects_Name_City' => array
			(
				"ua" => "Назва міста",
				"ru" => "Название города",
				"en" => "Cities name"
			),			
			
		'Projects_Sdate' => array
			(
				"ua" => "Дата початку",
				"ru" => "Дата начала",
				"en" => "Start date"
			),
			
		'Projects_Edate' => array
			(
				"ua" => "Дата закінчення",
				"ru" => "Дата окончания",
				"en" => "End date"
			),
			
		'Projects_Step_Create' => array
			(
				"ua" => "1. Створити крок",
				"ru" => "1. Создать шаг",
				"en" => "1. Create step"
			),
		
		'Projects_Step_Second_Create' => array
			(
				"ua" => "2. Другий крок",
				"ru" => "2. Второй шаг",
				"en" => "2. Second step"
			),
			
		'Projects_Step_Third_Create' => array
			(
				"ua" => "3. Третій крок",
				"ru" => "3. Другий крок",
				"en" => "3. Third step"
			),
			
		'Projects_Save_Form' => array
			(
				"ua" => "Додати проект",
				"ru" => "Добавить проект",
				"en" => "Add project"
			),
			
		'Validation_DateTime_Error' => array
			(
				"ua" => "Невірно вказана дата",
				"ru" => "Неверная дата",
				"en" => "Ivalid date value"
			),
			
		'Validation_Name_Error' => array
			(
				"ua" => "Ім`я закоротке",
				"ru" => "Имя слишком коротко",
				"en" => "Name is too short"
			),
			
		'Validation_Country_Error' => array
			(
				"ua" => "Вкажіть країну",
				"ru" => "Выберите страну",
				"en" => "Select the country"
			),
			
		'Project_Is_Successfully_Created' => array
			(	
				"ua" => "Проект успішно створено",
				"ru" => "Проект успешно создан",
				"en" => "Project is created"
			),
			
		'Project_Creation_Error' => array
			(	
				"ua" => "Помилка створення проекту. Будьте ласкаві, повторіть спробу пізніше",
				"ru" => "Ошибка создания проекта. Пожалуйста, повторите попытку позже",
				"en" => "Project creation error. Please, try again later"
			),
			
		'Projects_Comment' => array
			(
				"ua" => "Додаткова інформація",
				"ru" => "Дополнительная информация",
				"en" => "Additional info"
			),	
			
		'Projects_Next_Step' => array
			(
				"ua" => "Наступний крок",
				"ru" => "Следующий шаг",
				"en" => "Next step"
			),
			
		'Validation_DateTime_Diff_Error' => array
			(
				"ua" => "Дата початку перевіщує Дату закінчення",
				"ru" => "Дата начала больше Даты окончания",
				"en" => "Start date is greater then End date"
			),
			
		'Project_Empty_Projects_Id' => array
			(
				"ua" => "Помилка. Пустий ідентифікатор проекту",
				"ru" => "Ошибка. Пустой идентификатор проекта",
				"en" => "Error. Project's identificator is empty"
			)
	);
	
	public function __construct()
	{
		if(Session::GetUserId() > 0)
		{
			$this->locals['SUB_MENU']["ua"] = "<a href='/Project/#Add' onClick='ProjectClick(); return false;' class='ajaxLink' >Додати проект</a>&nbsp;".$this->locals['SUB_MENU']["ua"]."&nbsp;<a href='/Project/Yours/'>Ваші проекти</a>";
			$this->locals['SUB_MENU']["ru"] = "<a href='/Project/#Add' onClick='ProjectClick(); return false;' class='ajaxLink' >Добавить проект</a>&nbsp;".$this->locals['SUB_MENU']["ru"]."&nbsp;<a href='/Project/Yours/'>Ваши проекты</a>";
			$this->locals['SUB_MENU']["en"] = "<a href='/Project/#Add' onClick='ProjectClick(); return false;' class='ajaxLink' >Add project</a>&nbsp;".$this->locals['SUB_MENU']["en"]."&nbsp;<a href='/Project/Yours/'>Your projects</a>";
		}
	}
}

?>