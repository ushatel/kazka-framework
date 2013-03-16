<?php

  if (@preg_match("/pages/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

  include_once("includes/common/Lib.php");
  
  include_once("includes/common/Tag/Lib.Select.php");
  
  include_once("CommonPage.php");
  include_once("Page.Contact_Local.php");
  include_once("includes/DatabaseClasses/Parts.Messages.php");
  include_once("includes/common/Tag/Lib.CountriesList.php");
  

/**
 * class Contact
 *
 * Цей класс сторінки регистрації нового користувача.
 *
 * @package Pages.pkg
 */

class Contact extends CommonPage
{
	public $pageItems = array("Title");
	
	public $pagesMarkup = "";
	
	public $blocks;
	
	private $fieldsArray = array 
			(
				'message_title' => false,
				'message_email' => false,
				'message_body'  => false
			);
	
	private $isValidForm = false;
	
	private $valuesArray = array();

	private $messages = NULL;
			
	public function __construct()
	{
		$this->localizator = new Contact_Local();
		$this->messages = new Messages();
		
		parent::__construct();
	}
	
	private function ValidateForm()
	{
		$this->isValidForm = false;
		
		if(Request::GetSCode() != sha1("SeNdMesSegE"))
		{
			return $this->isValidForm;
		}

		if($this->messages->sender_id = Session::GetUserId() > 0)
		{
			$this->messages->sender_name = Session::GetUserLogin();
		}
		else
		{
			$this->messages->sender_name = StaticDatabase::CleanupTheField($_POST['message_login']);
		}

		$this->messages->message_title = substr(StaticDatabase::CleanupTheField($_POST['message_title']), 0, 45);
		if(strlen($this->messages->message_title) < 3)
		{
			$this->fieldsArray['message_title'] = "<:=Validation_Title_Error=:>";
			$this->isValidForm = false;	
		}
		else {
			$this->isValidForm = true;
		}

		$this->messages->message_body = StaticDatabase::CleanupTheField($_POST['message_text']);
		
		if(Session::GetUserId() == 0)
		{
			$this->messages->message_email = StaticDatabase::CleanupTheField($_POST['message_email']);
			//echo "fdsfs ".!preg_match("/@/",$this->message->message_email)." ".$this->messages->message_email;
			if(strlen($this->messages->message_email) < 7 || !preg_match("/@/", $this->messages->message_email))
			{
				$this->fieldsArray['message_email'] = "<:=Validation_Email_Error=:>";
				$this->isValidForm = false;
			}
			else
			{
				$this->messages->message_body = $this->messages->message_email." ".$this->messages->message_body;	
				$this->isValidForm = $this->isValidForm & true;
			}
		}

		return $this->isValidForm;
	}

	private function MessageForm()
	{
		$this->formTag = new Form();

		//$this->pagesMarkup = $formTag->RenderTop();
		
		if(!$this->isAjaxRequest)
			$this->pagesMarkup .= "<table id='messageFields'>";

 		$this->pagesMarkup .= "<tr><td><:=Message_Title=:></td><td><input type='hidden' id='step' value='jjj'><input type='text' id='message_title' name='message_title' value='".$this->messages->message_title."'>".$this->formTag->RenderAsterisks($this->fieldsArray['message_title'])."</td></tr>";

		if(Session::GetUserId() == 0)
		{
			$this->pagesMarkup .= "<tr><td><:=Message_Login=:></td><td><input type='text' id='message_login' name='message_login' value='".$this->messages->sender_name."'>".$this->formTag->RenderAsterisks($this->fieldsArray['message_login'])."</td></tr>";
			$this->pagesMarkup .= "<tr><td><:=Message_Email=:></td><td><input type='text' id='message_email' name='message_email' value='".$this->messages->message_email."'>".$this->formTag->RenderAsterisks($this->fieldsArray['message_email'])."</td></tr>";
		}

		$this->pagesMarkup .= "<tr><td><:=Message_Text=:></td><td><textarea name='message_text' id='message_text' cols='30' rows='5'>".$this->messages->message_text."</textarea></td></tr>";

		if(!$this->isAjaxRequest) 
		{
			$this->pagesMarkup .= "</table>";

			$link = new Anchor();
			$link->refreshElementId = "messageFields";
			$link->SCode = sha1("SeNdMesSegE");
			$link->title = '<:=Add_Message_Text=:>';
			$link->href = Request::$url;
			$link->hrefAJAX = Request::$url; 
			$link->isTraditionalHref = false;
			$link->applyScripts = true; 
			$link->getParamsValues = true;
			$link->params = array("message_title" => "", "message_email" => "", "message_text" => "", "step" => "");
			$link->class = "ajaxLink";

			$this->pagesMarkup .= "<div >".$link->OpenTag()."<:=Add_Message_Text=:>".$link->CloseTag()."</div>";
			}
		
		if($this->isAjaxRequest)
		{
			$this->pagesAjaxMarkup = $this->pagesMarkup;
		}
	}
		
	public function PageInit()
	{
		parent::PageInit();

		$hdn = (bool)($_POST['step']);
		if(!$this->isValidPost || $this->isValidPost && (Request::GetSCode() == sha1("SeNdMesSegE") ) && !$hdn || 
			$this->isValidPost && (Request::GetSCode() == sha1("SeNdMesSegE") ) && $hdn && !$this->ValidateForm())
		{ // First time valid loaded from the address bar
			$this->MessageForm(); 
		}
		elseif($this->isValidPost && $this->isValidForm)
		{
			$this->messages->sender_ip = Request::$ip;
			if($id = $this->messages->CreateMessage() > 0)
			{	
				$mailer = new Mailer();
				$mailer->to = "office@myxata.com";
				$mailer->from = $this->messages->message_email;
				$mailer->subject = $this->GetLocalValue('Registration_Letter_Subject');

				$body = "Title:&nbsp;".$this->messages->message_title."<br>Name:&nbsp;".$this->messages->sender_name."<br>Email:&nbsp;".$this->messages->message_email."<br>Body:&nbsp;".$this->messages->message_body;
				// Send letter
				$mailer->SendHtmlLetter($body);
				
				$this->pagesAjaxMarkup = "<:=Message_Is_Sent=:>";
			}
		}
	}
	
}

?>