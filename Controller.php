<?php

$langVar = "en";

include_once("includes/common_init.php");

/**
 * Pages selector
 */
 
include_once("includes/common/Main.Security.php");

$page = NULL;

if(Request::$module != "Image")
{
	switch(Request::$module)
	{
		case "Main":
			include_once("pages/Page.Main.php");
			$page = new Main();
		break;

		default:
	
			include_once("pages/Page.".Request::$page.".php");

			
			try // Should allways handle fatal errors!! No exceptions!
			{
				//!!!Should not get any page not passed the Security::PageIsAllowed() test!!!
				eval("\$page = new ".Request::$page."();");
			}
			catch(Exception $ex)
			{
				echo "bad code ".$ex->GetMessage();
			}
		break;
	}
	
	$page->PageInit();
	$page->ParsePage();
	
	if(!$page->isHalted)
	{
		Response::SendResponseToClient(array($page->pagesContent, $page->pagesClientScripts, $page->pagesAjaxObject));
	}
}
elseif(Request::$module == "Image")
{
	include_once("includes/common/Main.Image.php");
	
	$img = new Image();
	$img->GetFileContents();

	Response::SetContentType($img->mimeType);
	
	Response::SendResponseToClient(array($img->PrepareOutputImage()));
}


?>
