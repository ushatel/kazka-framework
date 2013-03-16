<?

	include_once("pages/commonpage.php");
	include_once("pages/page.testpage_local.php");
	include_once("includes/common/Tag/Lib.Select.php");
	include_once("includes/common/Tag/Lib.Form.php");
	include_once("Block.Search.php");
	include_once("Block.Materials.php");
	include_once("Block.Upload.php");

	
	class Testpage extends CommonPage
	{
		public $pageItems = array("Title");
		
		public $pageMarkup = "";
		
		public $blocks;
		
		private $fields_array = array ();
		
		private $isValidForm = false;
		
		private $valuesArray = array();
		
		private $company;
		
		public function __construct()
		{
			if($_SERVER['REMOTE_ADDR'] != "127.0.0.1")
				return NULL;
			
			$this->localizator = new TestPage_Local();
			
			$block = new BlockUpload();
			$block->page = $this;
			$block->BlockMode = RENDER_UPLOAD_FILE_BUTTON;
			$block->blockName = "Render upload file button";
			
			$this->blocks['UPLOAD_CONTROL'] = $block;
			
			parent::__construct();
		}
		
		public function PageInit()
		{
			parent::PageInit();
			
			$sCodes = BlockSearch::GetAllowedSCodes();
			array_push($sCodes, BlockUpload::GetAllowedSCodes());
			
			/*
			if($this->isAjaxRequest)
			{
				$this->isAjaxJSON = true;
				$this->pagesAjaxMarkup .= "ddd_".(int)(Request::GetIsValidPost())." ".print_r($_POST, true);
				return;
			}
			*/
			
			if(!$this->isAjaxRequest)
			{
				//$this->pagesMarkup = "<img src='/image/"./*Security::EncodeUrlData('533f8e19-325b-11e0-92b3-e2f528e50773')*/Security::EncodeUrlData(11)."/DSC02286.jpg' alt='yyyyyy' />";

				$this->pagesMarkup .= "<:=UPLOAD_CONTROL=:>";

				$materials = new BlockMaterials();
				$materials->page = $this;
				$materials->BlockMode = ADD_MATERIALS;
				$materials->BlockInit();

				$this->blocks['BLOCK_MATERIAL'] = $materials;
	
				$this->pagesMarkup .= "<:=BLOCK_MATERIAL=:>";
			
				$this->formTag = new Form();
						
				$this->pagesMarkup .= "<div id='search_container' >";
				$this->pagesMarkup .= $this->formTag->RenderTop();
				$this->pagesMarkup .= "<input type='text' name='searchText' id='searchText' value='test test' onclick='' /><input type='button' name='ttt' value='Submit' onclick='myBtnClick(\"".$sCodes['ajax_search']."\")' />";
				$this->pagesMarkup .= "<div id='div_search' style='position:relative;top:1;left:2;z-index:50;border: 1px solid #DDDDEE;display:none'>Search Text<br>fdffdf</div>";
				$this->pagesMarkup .= $this->formTag->RenderBottom();
				$this->pagesMarkup .= "</div>";
			}
			else
			{	
				//$this->pagesClientScripts .= "<script>alert('fdsfdsfds');</script>";

				if($this->isValidPost && Request::GetSCode() == $sCodes['ajax_search'] )
				{
					$searchText = substr(StaticDatabase::CleanupTheField($_POST['searchText']), 0, 100);
					
					$this->isAjaxJSON = true;
					$this->pagesAjaxMarkup = $responseObj;
				}
				elseif(Request::GetSCode() != $sCodes['save_file'])
				{
					//header('HTTP/1.1 500 Internal Server Error');
				}
				
				//	$this->pagesAjaxMarkup = "fdsfsdf";
			}
			
//			$result = preg_match_all("/(?<name>.+);(?<code>\w+)/i", $str, $matches);
			
/*			echo "<pre>";
			print_r($matches); 
			echo "</pre>";
			
			echo "fdsfsd ___ rewrwe ".(float)5777;
			echo $var = (bool)1;
			
			if($var === true)
				echo "123";
			
			echo " gggg ";
*/
			//$result = StaticDatabase::SqlQuery("UPDATE <%prefix%>`COUNTRIES` SET `ORD` = `ORD` + 1 ");
			
			//foreach($matches["name"] as $key => $value)
			//{
				//StaticDatabase::SqlQuery("INSERT INTO <%prefix%>`COUNTRIES` (`NAME`, `NAME_ENG`, `COUNTRY_CODE`) VALUES ('".$value."', '".$value."', '".$matches["code"][$key]."'); ");
			//}
		//	echo "ttrtrtr";
		}

	}

?>