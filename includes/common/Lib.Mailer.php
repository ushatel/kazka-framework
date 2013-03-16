<?php

  if (@preg_match("includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

	/** 
	 *	Library is the package with the common library functionality
	 *
	 *  @package Library.pkg
	 */


	/** 
	 *	Class Mailer
	 *
	 *  Class implements the common functions for operating with the post
	 *
	 *  @package Library.pkg
	 */

	
	class Mailer 
	{	
		public $to = "";
		
		public $headersFrom =  "From: MyXata <webmaster@myxata.com>";
		public $headersReply = "Reply-To: No Reply <noreply@myxata.com>";
		
		public $subject = "Registration confirmation";

		/**
		 * Send the simple Html letter
		 */		
		public function SendHtmlLetter($body) 
		{
			$mime_boundary = "----BUILDER----".md5(time());

			$subject  = $this->subject;
			$headers  = $this->headersFrom."\n";
			$headers .= $this->headersReply."\n";
			$headers .= "MIME-Version: 1.0\n";
			$headers .= "Content-Type: multipart/alternative; boundary=\"$mime_boundary\"\n";
			$message .= "--$mime_boundary\n";
			$message .= "Content-Type: text/html; charset=utf-8\n";
			$message .= "Content-Transfer-Encoding: Quoted-printable\n"; 
			$message .= "Content-Desposition: inline\n\n";
			$message .= "<html>\n<body style=3D\"font-family:Verdana, Geneva, sans-serif; font-size:12px; color:#666333;\">\n"; //--???
			$message .= $body;
			$message .= "</body></html> \n";
			$message .= "--$mime_boundary--\n\n";

			return mail($this->to, $subject, $message, $headers);
		}
	}

?>