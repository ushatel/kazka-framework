<?php

class BlockAlbum_Local extends GlobalLocals
{
	public $locals = array(
	
		'Title' => array (
			"ua" => "Завантаження картинок", 
			"en" => "Upload pictures",
			"ru" => "Загрузка картинок"
			),
			
		'Album_Add_New' => array (
			"ua" => "Додати",
			"ru" => "Добавить",
			"en" => "Add"
			),
		 
		'Album_Name' => array (
			"ua" => "Ім`я альбому",
			"ru" => "Имя альбома",
			"en" => "Album name"
			),
			
		'Album_Confirm_Delete' => array
		(
			"ua" => "Ви дійсно бажаєте видалити файл?",
			"ru" => "Вы точно хотите удалить файл?",
			"en" => "Are you sure to delete the file?"
		),
		
		"Album_Confirm_Album_Delete" => array
		(
			"ua" => "Чи ви впевнені, що бажаєте видалити альбом?",
			"ru" => "Вы уверены, что хотите удалить альбом?",
			"en" => "Are you sure to delete the album?"
		)
			
			
	);
	
	public function __construct() 
	{
	}
}

?>