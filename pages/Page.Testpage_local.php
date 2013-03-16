<?

	include_once("includes/common/Lib.GlobalLocals.php");
	
class Testpage_Local extends GlobalLocals
{
	public $locals = array
	(
		'Text' => array
			(
				"ua" => "тест",
				"ru" => "тест",
				"en" => "text"
			),
			
		"Title" => array
			(
				"ua" => "Тестова сторінка",
				"ru" => "Тестовая страница",
				"en" => "Test page"
			)
	);
	
}

?>