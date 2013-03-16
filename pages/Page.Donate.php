<?php

  if (@preg_match("/pages/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

	include_once("CommonPage.php");

	include_once("Page.Donate_Local.php");

	class Donate extends CommonPage
	{
		public $pagesItems = array ("Title", "Login", "Password", 
							"Confirm_password", "Email", "First_Name", "Second_Name", 
							"Third_Name", "Company", "Public_Email", "Submit_Text");
		public $pagesMarkup = '';
		
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

			if(!Request::$IsAJAX)
			{
			}
			elseif($this->isValidPost)
			{
			}

			parent::__construct();
		}
		
		public function PageInit()
		{
			parent::PageInit();
			
			$subpage = Request::$identifier;
			if(!Request::$IsAJAX && strlen($subpage) == 0 )
			{	
				$link = new Anchor();
				$link->SCode = sha1("DoNatEbuTtOnCliCk");
				$link->title = "<:=Donate_Button=:>";
				$link->hrefAJAX = Request::$url;
				$link->isTraditionalHref = false;
				$link->refreshElementId = "passFields";
				$link->applyScripts = false;
				$link->getParamValues = true;
				$link->params = array("payment_ammount" => "", "payment_phone" => "", "payment_comment" => "");
				$link->class = "ajaxLink";
				$link->onClick = "Donate(); return false;";
											
				$this->pagesMarkup .= "<table class='commonForm'><tr><td><:=Donate_Ammount=:></td><td ><input type='text' value='30' id='payment_ammount'  name='payment_ammount' style='width:85%;' /><span style='font-size: 15px;'>&nbsp;UAH</span></td></tr>".
											 "<tr><td><:=Donate_Phone=:></td><td><input name='payment_phone' id='payment_phone' type='text' value='' style='width:100%;'/></td></tr>".
											 "<tr><td><:=Donate_Comment=:></td><td><textarea name='payment_comment' id='payment_comment' cols='30' rows='5'></textarea></td></tr>".
											 "<tr><td style='text-align:right;' colspan='2'>".$link->OpenTag()."<:=Donate_Button=:>".$link->CloseTag()."</td></tr>".
											 "</table>";
											 
				//$this->commonScripts = "</script><script>".$link->appendClientScript."; </script>";
				$this->commonScripts = "</script><script>function Donate() { 

					var parameters = { 	
						__SVar  		: Objects.Security.secureServerVar, 
		  				__SCode 		: '".sha1("DoNatEbuTtOnCliCk")."', 
				  		__ClientVar 	: Objects.Security.createSecureVar(),
				  		IS_AJAX 		: 'TRUE',
						payment_ammount : $('payment_ammount').value,
						payment_phone 	: $('payment_phone').value,
						payment_comment : $('payment_comment').value
				  	}

					var rq = new Ajax.Request(document.location.href, {
	
						parameters : parameters,
				  	
			  			onCreate: function ()
			  			{
			  			},

			  			onFailure: function ()
			  			{	
			  			},

			  			onSuccess: function(response) 
			  			{
			  				try
			  				{	 
				  				var rObject = Objects.Security.validateResponse(response.responseJSON);
								
				  				if(rObject != null && rObject.isSecured)
				  				{
					  				Objects.BubbleDiv.refresh(rObject.text);
									
					  				if(rObject.scripts.length > 0)
						  				eval(rObject.scripts[0]); 

									if(this.successMethod)
										eval(this.successMethod);
								}
								else
								{
									alert(rObject + '_' + response.responseText);
								}
		
							}
							catch(ex)
							{
								FireError(ex);
							}
		    			}
    				});				
				}</script>";
				
				$this->pagesMarkup .= '<div style="padding-left: 20px;"><img width="50" height="25" src="https://liqpay.com/images/VBvisa.png" />&nbsp;<img width="60" height="25" src="https://liqpay.com/images/mcSC.png" />&nbsp;<a style="outline:none; border: 0px;" target="_blank" href="http://www.privat24.ua/"><img width="25" height="25" src="https://liqpay.com/images/p24.png"  /></a>&nbsp;<img width="48" height="25" src="https://liqpay.com/images/visa_mt.png" /></div>';
					 
				$action = 'https://www.liqpay.com/?do=clickNbuy';
				
				$this->pagesMarkup .= '
					<form action="'.$action.'" method="POST" id="liqpayForm" />
						<input type="hidden" id="operation_xml" name="operation_xml" value="{$xml_encoded}" />
						<input type="hidden" id="signature" 	name="signature" 	 value="{$sign}" />
					</form>';

			}
			elseif($this->isValidPost && Request::GetSCode() == sha1("DoNatEbuTtOnCliCk"))
			{			
				//echo (string)StaticDatabase::CleanupTheField($_POST['payment_ammount']);
				$xml= '<request>      
					<version>1.2</version>
					<merchant_id>i7681047123</merchant_id>
					<result_url>'.Request::GetRoot().'/Donate/Thanks/</result_url>
					<server_url>'.Request::GetRoot().'/Donate/Payment/</server_url>
					<order_id>orderid</order_id>
					<amount>'.(string)StaticDatabase::CleanupTheField($_POST['payment_ammount']).'</amount>
					<currency>UAH</currency>
					<description>'.substr(StaticDatabase::CleanupTheField($_POST['payment_comment']), 0, 5000).'</description>
					<default_phone>'.substr(StaticDatabase::CleanupTheField($_POST['payment_phone']), 0, 13).'</default_phone>
					<pay_way>card</pay_way>
					<goods_id>777</goods_id>
					</request>';
					
				$xml_encoded=base64_encode($xml); 
				
				$sign=base64_encode(sha1(Security::MercSignature.$xml.Security::MercSignature,1));

				$this->commonScripts = "</script><script>$('operation_xml').value = '".$xml_encoded."'; $('signature').value = '".$sign."'; /*alert($('operation_xml').value +' '+$('signature').value);*/ $('liqpayForm').submit(); </script>";

				//header('HTTP/1.1 500 Internal Server Error');	
			}
			elseif($this->isValidPost && Request::GetSCode() == sha1("DoNatEbuTtOnCliCk") && $subpage == "Payment")
			{
				$xml = substr($_POST['operation_xml'], 0, 200);
				$sign_income = substr($_POST['signature'], 0, 200);

				$sign=base64_encode(sha1(Security::MercSignature.$xml.Security::MercSignature,1)); 
				
				if($sign_income == $sign)
				{
					$xml_decoded=base64_decode($xml); 
					
					$p = xml_parser_create();
					xml_parse_into_struct($p, $xml_decoded, $vals, $index);
					xml_parser_free($p);

					trigger_error(Logger::LOG_EVENT_CONST." ".print_r($vals)." ".print_r($index), E_USER_ERROR);
						/*
					print_r($vals);
					print_r($index);
					*/
				}
				else
				{
					trigger_error(Logger::LOG_EVENT_CONST." Error VeriSign", E_USER_ERROR);
				}
				
			}
			elseif($subpage == "Thanks")
			{
				$this->pagesAjaxMarkup .= "Thank you!";
			}

		}
	}
?>