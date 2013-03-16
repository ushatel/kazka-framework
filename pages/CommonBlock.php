<?php
	
  if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

	include_once("includes/common/Lib.ILocalizator.php");
	include_once("includes/common/Lib.GlobalLocals.php");
	include_once("includes/common/Lib.IControl.php");

/**
 * class CommonPage
 *
 * Головний клас сторінки.
 *
 * The main CommonPage
 * 
 * @package Pages.pkg
 */


class CommonBlock implements ILocalizator, IControl
{
	private $language = "";

	public $blocksItems = NULL; // Використовуйте позначки - <:=елемент_сторінки=:>
	public $blocksMarkup = "";
	public $blocksAjaxMarkup = "";
	public $blocksContent = "";
	
	public $blocks = NULL;
	
	public $visible = true;
	
	public $blockName = "";
	public $blockId = "";

	public $needDrawId = false;
	
	public $request = NULL;
	
	public $localizator = NULL;
	
	public $isValidPost = false;
	public $isAjaxRequest = false;
	
	public $commonTop = "";
	
	public $commonTitle = '<:=Block_Title=:>';
	
	public $commonHead = '';
	
	public $commonBottom = "";
	
	/**
	 * Головний конструктор 
	 *
	 * Main constructor
	 */
	public function __construct() 
	{
		$this->isValidPost = (bool) Request::GetIsValidPost();
		$this->page->isAjaxJSON = $this->isAjaxRequest = (bool) Request::$IsAJAX;		
	}
	
	public static function GetAllowedSCodes()
	{
		return NULL;
	}
	
	public function BlockInit() 
	{
		$blockId = "/<:={$this->blockId}=:>/i"; 
		if($this->needDrawId && $this->isAjaxRequest && strlen($this->blockId) > 0 && !preg_match($blockId, $this->page->pagesAjaxMarkup) && !preg_match($blockId, $this->blocksAjaxMarkup))
		{
			$this->page->pagesAjaxMarkup .= "<:={$this->blockId}=:>";
		}	
	
		if($this->blocks != NULL)
		{
			foreach($this->blocks as $block)
			{
				if($block != NULL)
				{
					$block->page = $this->page;
					$block->BlockInit();
				}
			}
		}
	}
	
	public function FlushTitle() 
	{
		return "".$this->commonTitle."";
	}
	
	public function FlushHead() 
	{
		//return "<head>".$this->FlushTitle()."\r\n".$this->commonHead."</head>";
		return $this->commonHead;
	}
	
	/**
	 * Render the top of the page
	 */	 
	public function FlushTop() 
	{
		return $this->commonTop."\r\n".$this->FlushHead();
	}

	/**
	 * Render the main part of the page
	 */	
	public function FlushHTML() 
	{
		$return = self::FlushTop()."".$this->blocksMarkup.self::FlushBottom();
		if($this->isAjaxRequest)
		{
			$return = $this->FlushAJAX();
		}
		
		return $return;
	}
	
	/**
	 * AJAX blocks content flush
	 */
	public function FlushAJAX()
	{
		$result = "";

		if(!$this->page->isHalted)
		{
			$result = $this->blocksAjaxMarkup;
		}
		return $result;
	}

	/**
	 * Зачинення тагу </html>
	 */	
	public function FlushBottom () 
	{
		return $bottom;
	}
	
	/**
	 * 	Parsing Pages Content from the markup with the pageItems array
	 */
	public function ParseBlocksContent() 
	{
		if(!$this->isAjaxRequest)
		{
			$this->blocksContent = self::FlushHTML();
		}
		else
		{
			$this->blocksContent = self::FlushAJAX();
		}

		if($this->blocksItems != NULL)
		{
			foreach( $this->blocksItems as $key => $value ) 
			{
				if(!is_array($value)) 
				{
					$local_value = $this->localizator->locals[$value][Session::GetLang()];
					$this->blocksContent = preg_replace("/<:=".$value."=:>/", $local_value , $this->blocksContent );
				}
			}
		}

		//echo $this->blocksContent;
	}

	/**
	 * 	Parsing Pages Content from the markup with the pages locals array
	 */
	public function ParseBlocksLocalsContent() 
	{
		if(strlen($this->blocksContent) == 0) 
		{
			if(!$this->isAjaxRequest)
			{
				$this->blocksContent = self::FlushHTML();
			}
			else 
			{
				$this->blocksContent = self::FlushAJAX();
			}
		}

		

		if($this->localizator->locals != NULL) {

			$needCommonScriptsCheck = false;
			if(strlen($this->page->commonScripts) > 0)
			{
				$needCommonScriptsCheck = true;
			}

			foreach( $this->localizator->locals as $key => $value ) 
			{
				$local_value = $this->localizator->locals[$key][Session::GetLang()];
				$this->blocksContent = preg_replace("/<:=".$key."=:>/", $local_value , $this->blocksContent );

				if($needCommonScriptsCheck)
				{
					$this->page->commonScripts = preg_replace("/<:=".$key."=:>/", $local_value, $this->page->commonScripts);
				}
			}
		}
	}
	
	public function ParseBlocks()
	{
		$this->ParseBlocksContent();
		$this->ParseBlocksLocalsContent();

		if(count($this->blocks))
		{
			// Parse and render blocks content
			foreach($this->blocks as $key => $block)
			{	
				$this->blocksContent = preg_replace("/<:=".$key."=:>/i", $block->ParseBlocks(), $this->blocksContent );
				//echo $this->blocksContent;
			}
		} 
		return $this->blocksContent;
	}
	
	/**
	 *  Returns the value for the localizable property
	 */
	public function GetLocalValue($property, $lang = "")
	{
		if($lang == "") 
		{
			$lang = Session::GetLang();
		}
		
		return $this->localizator->locals[$property][$lang];
	}

	public static function GetStaticLocalValue($property, $lang = "", $local_class = "")
	{
		$result = NULL;
		
		if($lang == "")
		{
			$lang = Session::GetLang();
		}

		$class = NULL;
		if($local_class != "")
		{
			include_once("/pages/Block.".$local_class."_Local.php");

			eval("\$class = new Block".$local_class."_Local();");

			if($class != NULL)
			{
				$result = $class->locals[$property][$lang];
			}
		}
		
		return $result;
	}
	
	/**
	 * Повертає локалізоване значення глобальної змінної
	 */
	public function GetGlobalValue($property, $lang = "")
	{
		return GlobalLocals::GetStaticGlobalValue($property, $lang);
	}
}

?>