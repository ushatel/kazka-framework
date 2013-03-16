<?php

  if (@preg_match("/pages/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

	include_once("CommonPage.php");

	include_once("Page.Main_Local.php");

	include_once("Block.Login.php");	
    include_once("Block.Projects.php");
    include_once("Block.Materials.php");
    include_once("Block.News.php");
    include_once("Block.Friends.php");

	class Main extends CommonPage
	{
		public $pagesItems = array ("Title", "Login", "Password", 
							"Confirm_password", "Email", "First_Name", "Second_Name", 
							"Third_Name", "Company", "Public_Email", "Submit_Text");
		public $pagesMarkup = '<body></body>';
		
		public $blocks = NULL;
		
		private $fields_array = array("login" => false, 
				"email" => false, "password_field" => false, 
				"first_name" => false, "second_name" => false, 
				"third_name" => false, "company_name" => false,
				"public_email" => false);
	
		private $isValidForm = false;
		
		private $valuesArray = array();
		
		private $usr;
		
		function __construct() 
		{
			$this->localizator = new Main_local();

			parent::__construct();
		}
		
		private function SwitchBlock()
		{
			$mainBlock = NULL;
			
			switch(Request::GetSCode())
			{

				case sha1("UsErFriEndS"):
					
					$mainBlock = new BlockFriends();
					$mainBlock->page = $this;
					$mainBlock->BlockMode = RENDER_FRIENDS_GRID;
					$mainBlock->blockName = "Render users friends";
					$mainBlock->SetUserId(Session::GetUserId());

				break;
				
				case sha1("pRocEsSTheLogOut"):
				case sha1("pRocEsSTheLoGinForM"):
				
					$mainBlock = new Login();
					$mainBlock->page = $this;
					$mainBlock->BlockMode = LOGIN_BUBBLE;
					$mainBlock->blockName = "User Login";
				
				break;	

			}
			
			$mainBlock->BlockInit();

			$this->blocks['MAIN_BLOCK'] = $mainBlock;
			
			$this->pagesAjaxMarkup .= "<:=MAIN_BLOCK=:>";
			
		}

		public function PageInit()
		{
			parent::PageInit();

			if(in_array(strtolower(Request::$identifier), array ("ua", "ru", "en")))
			{
				Session::SetLang(strtolower(Request::$identifier));
			}

			$this->SetTitle($this->GetLocalValue("Title"));

			if(!Request::$IsAJAX)
			{
				// Latest News
				$mainBlock = new BlockNews();
				$mainBlock->page = $this;
				$mainBlock->BlockMode = RENDER_MAIN_NEWS_BLOCK;
				$mainBlock->blockName = "Latest news ";

				$mainBlock->BlockInit();

				$this->blocks['NEWS_BLOCK'] = $mainBlock;

				$this->pagesMarkup .= "<div id='newsPageBlock'><:=NEWS_BLOCK=:></div>";
				
				$mainBlock = new BlockProjects();
				$mainBlock->page = $this;
				$mainBlock->BlockMode = RENDER_MAIN_PROJECTS;
				
				$this->blocks['PROJECTS_BLOCK'] = $mainBlock;
				
				$mainBlock->BlockInit();
				
				$this->pagesMarkup .= "<div id='projectsBlock'><:=PROJECTS_BLOCK=:></div>";
				
				/*

				// Active projects
				$mainBlock = new BlockProjects();
				$mainBlock->page = $this;
				$mainBlock->BlockMode = RENDER_PROJECTS_GRID_IN_WORK;
				$mainBlock->blockName = "User projects in work";
				$mainBlock->SetUserId(Session::GetUserId());

				$mainBlock->BlockInit();

				$this->blocks['PROJECTS_BLOCK'] = $mainBlock;
				
				$this->pagesMarkup .= "<div class=''><:=PROJECTS_BLOCK=:></div><br>";
				*/
				$mainBlock = new BlockMaterials();
				$mainBlock->page = $this;
				$mainBlock->BlockMode = RENDER_MAIN_MATERIALS;
				$mainBlock->blockName = "Render materials";

				$mainBlock->BlockInit();

				$this->blocks['MATERIALS_BLOCK'] = $mainBlock;

				$this->pagesMarkup .= "<div id='materialsBlock'><:=MATERIALS_BLOCK=:></div>";

				//$this->commonScripts .= "<script>alert('I`m from the server - Materials mode');</script>";	
				
			}
			else
			{

			if($this->isValidPost)
				{
					$this->SwitchBlock();
				}
				else
				{
				//	header('HTTP/1.1 500 Internal Server Error');
				}

				//$this->page->pagesAjaxMarkup .= " <script>alert('Hello world!');</script>Have sent from server!";
			}

		}
	}
?>