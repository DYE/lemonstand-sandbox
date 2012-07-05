<?php
	function toUtf8($string)
	{
		return iconv("ISO-8859-1", "UTF-8//TRANSLIT", $string);
	}
	
	function writeXmlDeclaration()
	{
		echo "<?xml version=\"1.0\" standalone=\"yes\" ?>";
	}
	
	function writeStartTag($tag, $attributes = null)
	{
		echo '<' . $tag;
		
		if ($attributes != null)
		{
			echo ' ';
			
			foreach ($attributes as $name => $attribValue)
			{
				echo toUtf8($name. '="'. htmlspecialchars($attribValue). '" ');
			}
		}
		
		echo '>';
	}
	
	function writeCloseTag($tag)
	{
		echo toUtf8('</' . $tag . '>');
	}

	// Output the given tag\value pair
	function writeElement($tag, $value)
	{
		writeStartTag($tag);
		echo toUtf8(htmlspecialchars($value));
		writeCloseTag($tag);
	}
	
	// Outputs the given name/value pair as an xml tag with attributes
    function writeFullElement($tag, $value, $attributes)
    {
		echo toUtf8('<'. $tag. ' ');
		
		foreach ($attributes as $name => $attribValue)
		{
			echo toUtf8($name. '="'. htmlspecialchars($attribValue). '" ');
		}
		echo '>';
		echo toUtf8(htmlspecialchars($value));
		writeCloseTag($tag);
    }
	
    // Function used to output an error and quit.
    function outputError($code, $error)
    {	
		writeStartTag("Error");
		writeElement("Code", $code);
		writeElement("Description", $error);
		writeCloseTag("Error");
	} 	

	function toGmt($dateSql)
	{
        $pattern = "/^(\d{4})-(\d{2})-(\d{2})\s+(\d{2}):(\d{2}):(\d{2})$/i";

        if (preg_match($pattern, $dateSql, $dt)) 
        {
            $dateUnix = mktime($dt[4], $dt[5], $dt[6], $dt[2], $dt[3], $dt[1]);
            return gmdate("Y-m-d\TH:i:s", $dateUnix);
        }
        
        return $dateSql;
    }
    
    function toLocalSqlDate($sqlUtc)
    {   
	   $pattern = "/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})$/i";

       if (preg_match($pattern, $sqlUtc, $dt)) 
       {
            $unixUtc = gmmktime($dt[4], $dt[5], $dt[6], $dt[2], $dt[3], $dt[1]);  
                       
            return date("Y-m-d H:i:s", $unixUtc);
       }
        
       return $sqlUtc;
    }

    function checkAdminLogin()
    {
        $loginOK = true;

        if (isset($_REQUEST['username']) && isset($_REQUEST['password']))
        {
            $Login = $_REQUEST['username'];
            $Password = $_REQUEST['password'];
            $userObj = new Phpr_User(); 
            $user = $userObj->findUser($Login, $Password);
			if (is_null($user))
			{
				outputError(50, "The username or password is incorrect.");
            	exit; 
			}
			else{
				$loginOK = true; 
			}
		}
        return $loginOK;
    }
    
?>