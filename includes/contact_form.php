<?

if(preg_match("/includes/i",$_SERVER['PHP_SELF']))
	die("You don't have permissions to access this dir!");

function DrawForm() 
{
	global $MsgTitle, $MsgBody, $MsgContactUs, $MsgValidationError, $MsgThankYou, $MsgEmail, $MsgTel, 
	$MsgResume, $MsgResumeLink, $mySessionVar, $langVar;
	
	$contactFormText = "";
	
	$contactFormText = '<form method="post" >'.
		'<input type="hidden" name="__SVar" value="'.$mySessionVar.'" >'.
		'<input type="hidden" name="__SCode" value="'.sha1("sendMsGfRomMartiS").'" >'.
		$MsgResume.'<a onclick="SLinkClick(this, \''.sha1("reNdeRTheResuMeFile").'\'); return false;" href="'.$_SERVER['SCRIPT_NAME']."?__SCode=".sha1("reNdeRTheResuMeFile").'"  title="'.$MsgResumeLink.'" >'.$MsgResumeLink.'</a><br><br>'.
		'<table border="0px">'.
		'<tr><td>'.$MsgTitle.':</td><td style="width:300px"><input type="text" name="mtitle" value="" size="100" maxlength="100" style="width:98%" ></td></tr>'.
		'<tr><td>'.$MsgEmail.':</td><td><input type="text" name="memail" value="" size="100" maxlength="100" style="width:98%" ></td></tr>'.
		'<tr><td>'.$MsgTel.':</td><td><input type="text" name="mtel" value="" size="100" maxlength="100" style="width:98%" ></td></tr>'.
		'<tr><td style="vertical-align:top">'.$MsgBody.':</td><td><textarea cols="30" rows="5" name="mbody" style="width:98%"></textarea></td></tr>'.
		'<tr><td colspan="2" style="width:100%; text-align:right"><input type="button" onclick="SButtonClick(this);" title="Contact us" value="'.$MsgContactUs.'"></td></tr>'.
		'</table></form>';
	
	return $contactFormText;
}

if(!isset($_POST['__SCode']) ) {
	$contactFormText = DrawForm();
}
else {

	switch(StripPostVar($_POST['__SCode'])) 
		{
			case sha1("sendMsGfRomMartiS"): // Sending the message to email + sms + db

				$mtitle = mysql_real_escape_string(StripPostVar($_POST['mtitle']));
				$memail = mysql_real_escape_string(StripPostVar($_POST['memail']));
				$mtel   = mysql_real_escape_string(StripPostVar($_POST['mtel']));
				$mbody  = mysql_real_escape_string(StripPostVar($_POST['mbody']));
				
				if(isset($mtitle) && isset($mbody) && (strlen($mtitle) > 0) && (strlen($mbody) > 0)) 
				{
					SecureLogEvent("");
					
					$mime_boundary = "----MARTIS-UA.COM----".md5(time());

					$to = "sysprog@martis-ua.com";
					
					//$to = "duminda@dumidesign.com";
					//$to = "info@msashipping.com";
					$subject  = "Request from Martis-UA.com";
					$headers  = "From: Martis-UA.com <webmaster@Martis-UA.com>\n";
					$headers .= "Reply-To: Martis-UA.com <webmaster@Martis-UA.com>\n";
					$headers .= "MIME-Version: 1.0\n";
					$headers .= "Content-Type: multipart/alternative; boundary=\"$mime_boundary\"\n";
					$message .= "--$mime_boundary\n";
					$message .= "Content-Type: text/html; charset=UTF-8\n";
					$message .= "Content-Transfer-Encoding: quoted-printable\n\n";	
					$message .= "<html><body>\n";
					$message .= "<b>Title</b>: ".$mtitle."<br>\n";
					$message .= "<b>Email</b>: ".$memail."<br>\n";
					$message .= "<b>Tel</b>:   ".$mtel."<br>\n";
					$message .= "<b>Body</b>:<br>".$mbody."\n";
					$message .= "</body></html>\n";
					$message .= "--$mime_boundary\n\n";

					@mail($to, $subject, $message, $headers);

					$contactFormText = "<center>".$MsgThankYou."</center>";
				}
				else {
					$contactFormText =  $MsgValidationError."<br><br>";
					$contactFormText .= DrawForm(); 
				}
			break;
			
			case sha1("reNdeRTheResuMeFile"): // Sending back the resume file
				SecureLogEvent("Downloading The file. Language: "+$langVar);			

				/*
				echo "<pre>";
				print_r($_SERVER);
				echo "</pre>";
				
				die("fdsds");
				*/
				
				$filename = $_SERVER['DOCUMENT_ROOT']."/includes/docs/";
				switch($langVar) 
				{
					default:
					case "en":
						$filename .= "/resume_en.zip";						
					break;
					
					case "ru":
						$filename .= "/resume_ru.zip";
					break;
					
					case "ua":
						$filename .= "/resume_ua.zip";
					break;
				}


					$mime_boundary = "----MARTIS-UA.COM----".md5(time());

					$to = "sysprog@martis-ua.com";
					
					//$to = "duminda@dumidesign.com";
					//$to = "info@msashipping.com";
					$subject  = "Downloading the file from Martis-UA.com";
					$headers  = "From: Martis-UA.com <webmaster@Martis-UA.com>\n";
					$headers .= "Reply-To: Martis-UA.com <webmaster@Martis-UA.com>\n";
					$headers .= "MIME-Version: 1.0\n";
					$headers .= "Content-Type: multipart/alternative; boundary=\"$mime_boundary\"\n";
					$message .= "--$mime_boundary\n";
					$message .= "Content-Type: text/html; charset=UTF-8\n";
					$message .= "Content-Transfer-Encoding: quoted-printable\n\n";	
					$message .= "<html><body>\n";
					$message .= basename($filename)."<br>".$_SERVER[HTTP_REFERER]."<br>".$_SERVER[REMOTE_ADDR]."\n";
					$message .= "</body></html>\n";
					$message .= "--$mime_boundary\n\n";

					@mail($to, $subject, $message, $headers);

				
				if(isset($filename) && file_exists($filename)) {
					ob_clean();

					header('Content-Description: File Transfer from Martis-UA.com');
					header("Content-type: application/force-download");
				    header('Content-Transfer-Encoding: binary');
					header("Content-Disposition: attachment; filename=".basename($filename)."");
					header("Content-length: ".(string)(filesize($filename)));
					header("Expires: ".gmdate("D, d M Y H:i:s", mktime(date("H")+2, date("i"), date("s"), date("m"), date("d"), date("Y")))." GMT");
					header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
					header("Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0");
					header("Pragma: no-cache"); 
					
					flush();
					readfile($filename);
					exit();
				}
		}
	
	}

?>