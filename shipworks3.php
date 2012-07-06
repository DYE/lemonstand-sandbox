<?php 

	//security - set REQUIRE_SECURE true for production
	define('REQUIRE_SECURE', false);
	$secure = getenv($_SERVER['HTTPS']); 

	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	
	// HTTP/1.1
	header("Content-Type: text/xml;charset=utf-8");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);

	// HTTP/1.0
	header("Pragma: no-cache");	

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
        $loginOK = false;

        if (isset($_REQUEST['username']) && isset($_REQUEST['password']))
        {
            $Login = $_REQUEST['username'];
            $Password = md5($_REQUEST['password']);
       
            $sql = "select * from users where login = '" . $Login . "' ";
            $return = Db_DbHelper::object($sql); 
            echo $return->password; 
            echo '<br><br>'.$Password;

			if (strcmp($return->password, $Password) != 0)
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

	//access LemonStand API
	$Phpr_InitOnly = true;
	include '../lemonstand/index.php';

	//ShipWorks req vars
	$moduleVersion = "3.0.0.0";
	$schemaVersion = "1.0.0";


	//set start/end times & maxcount
	$end = date("Y-m-d H:i:s", time() - 2);
	if (isset($_REQUEST['start']))
	{
		$start = $_REQUEST['start'];
	}
	else $start = '1900-01-01 00:00:00';

	if (isset($_REQUEST['maxcount'] ))
	{
		$maxcount = $_REQUEST['maxcount'];
	}
	else $maxcount = 50;
	$start = toLocalSqlDate($start);
	

	// Enforse SSL
    if (!$secure && REQUIRE_SECURE)
    {
        outputError(10, 'A secure (https://) connection is required.');
        exit; 
    }
    else
    {
		//LemonStand objects required
		$shop_order = Shop_Order::create();
		
		//get orders in $start-$end range and within max limit
		$orders = $shop_order->where('order_datetime > "' . $start . '" OR status_update_datetime > "' . $start . '" AND order_datetime <= "' . $end .'"')->limit($maxcount)->find_all(); 

		//begin processing response
		writeXmlDeclaration();

		writeStartTag("ShipWorks", array("moduleVersion" => $moduleVersion, "schemaVersion" => $schemaVersion));
		
        // If the admin module is installed, we make use of it
        if (checkAdminLogin())
        {
            $action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : ''); 
            switch (strtolower($action)) 
            {
				case 'getmodule': Action_GetModule(); break;
				case 'getstore': Action_GetStore(); break;
				case 'getcount': Action_GetCount(); break;
				case 'getorders': Action_GetOrders(); break;
				case 'getstatuscodes': Action_GetStatusCodes(); break;
				case 'updatestatus': Action_UpdateStatus(); break;
				default:
					outputError(20, "'$action' is not supported.");
				}
        }
    }

	writeCloseTag("ShipWorks");

	//Write module data
	function Action_GetModule()
	{
		writeStartTag("Module");
			writeElement("Platform", "LemonStand");
			writeElement("Developer", "Define:YourEdge (info@cuppyyarrish.com)");
			writeStartTag("Capabilities");
				writeElement("DownloadStrategy", "ByModifiedTime");
				writeFullElement("OnlineCustomerID", "", array("supported" => "true", "dataType" => "numeric"));
				writeFullElement("OnlineStatus", "", array("supported" => "true", "dataType" => "numeric", "supportsComments" => "true" ));
				writeFullElement("OnlineShipmentUpdate", "", array("supported" => "false"));
			writeCloseTag("Capabilities");
	   writeCloseTag("Module");			
	}
	
	// Write store data
	function Action_GetStore()
	{
		writeStartTag("Store");
			writeElement("Name", ' Zip Line Gear');
			writeElement("CompanyOrOwner", ' Zip Line Gear');
			writeElement("Email", 'info@zipline.com');
			writeElement("State", 'OR');
			writeElement("Country", 'USA');
			writeElement("Website", 'http://http://www.ziplinegear.com');
		writeCloseTag("Store");
	}
	
	// Get the count of orders greater than the start ID
	function Action_GetCount()
	{	  
		global $orders;	
		
		writeStartTag("Parameters");
			writeElement("Start", $start);
		writeCloseTag("Parameters");
		
		$count = 0;
		
		if ($orders) 
		{
			foreach($orders as $increment)
			{
				$count++; 
			}
		}

		writeElement("OrderCount", $count);
	}

	//Get all orders greater than the given start id, limited by max count
	function Action_GetOrders()
	{
		global $orders, $start, $end, $maxcount;		
		
	    writeStartTag("Parameters");
	        writeElement("StartGMT", $start);
		    writeElement("StartLocal", $start);
		    writeElement("End", $end);
		    writeElement("MaxCount", $maxcount);
		writeCloseTag("Parameters");

		writeStartTag("Orders");
			$lastModified = null;
			foreach($orders as $order)
			{
				$lastModified = !is_null($order->payment_processed) ? $order->payment_processed : $order->status_update_datetime;	        
		        WriteOrder($order, $lastModified);
		    }
	    writeCloseTag("Orders");
	}
	
	function WriteNote($noteText, $dateAdded, $public)
	{
		if (strlen($noteText) > 0)
		{
			$attributes = array("public" => $public ? "true" : "false",
								"date" => toGmt($dateAdded));
		
			writeFullElement("Note", $noteText, $attributes);
		}
	}
	
	function WriteOrder($order, $lastModified)
	{       
		global $secure; 

		$currencyFactor = '';
		$customer = $order->customer;


		//The customer comment will be the first history item
		writeStartTag("Order");
		
			writeElement("OrderNumber", $order->id);
			writeElement("OrderDate", toGmt($order->order_datetime));
			writeElement("LastModified", toGmt($lastModified));
			writeElement("ShippingMethod", $order->shipping_method->name);
			writeElement("StatusCode", $order->status_id);
			
			// See if the customer actually exists
			writeElement("CustomerID", $customer ? $order->customer_id : "-1");
			
			writeStartTag("Notes");
				WriteNote($order->customer_notes, $order->order_datetime, "true");
			writeCloseTag("Notes");
			
            
			writeStartTag("ShippingAddress");
				writeElement("FullName", $order->shipping_first_name. ' ' . $order->shipping_last_name);
				writeElement("Company", $order->shipping_company);
				writeElement("Street1", $order->shipping_street_addr);
				writeElement("City", $order->shipping_city);
				writeElement("State", $order->shipping_state->name);
				writeElement("PostalCode", $order->shipping_zip);
				writeElement("Country", $order->shipping_country->name);
				writeElement("Phone", $order->shipping_phone);
				writeElement("Email", $order->shipping_email);
			writeCloseTag("ShippingAddress");
			
			writeStartTag("BillingAddress");
				writeElement("FullName", $order->billing_first_name . ' ' . $order->billing_last_name);
				writeElement("Company", $order->billing_company);
				writeElement("Street1", $order->billing_street_addr);
				writeElement("City", $order->billing_city);
				writeElement("State", $order->billing_state->name);
				writeElement("PostalCode", $order->billing_zip);
				writeElement("Country", $order->billing_country->name);
				writeElement("Phone", $order->billing_phone);
				writeElement("Email", $order->billing_email);
			writeCloseTag("BillingAddress");
			
			writeStartTag("Payment");
				writeElement("Method", $order->payment_method->name);
				
				writeStartTag("CreditCard");
					writeElement("Type", "");
					writeElement("Owner", "");
    				
					if ($secure)
					{
						writeElement("Number", "");
					}
					else
					{
						writeElement("Number", "*******");
					}
    				
					writeElement("Expires", "");
				writeCloseTag("CreditCard");
				
			writeCloseTag("Payment");

			WriteOrderItems($order->items, $currencyFactor);
			
			WriteOrderTotals($order);
			
			writeStartTag("Debug");
				writeElement("LastModifiedLocal", $lastModified);
			writeCloseTag("Debug");
		writeCloseTag("Order");
	}
	
	// writes a single order total
    function WriteOrderTotal($name, $value, $class, $impact = "add")
    {
		if ($value > 0)
		{
			writeFullElement("Total", $value, array("name" => $name, "class" => $class, "impact" => $impact));
		}
    }
	// Write all totals lines for the order
	function WriteOrderTotals($order)
	{
		writeStartTag("Totals");
        	WriteOrderTotal("Shipping and Handling", $order->shipping_quote, "shipping", "none");
        	WriteOrderTotal("Shipping Tax", $order->shipping_tax, "tax", "add");
        	WriteOrderTotal("Tax Total", $order->tax_total, "tax", "add");
        	WriteOrderTotal("Order Subtotal", $order->subtotal, "subtotal", "none");
        	WriteOrderTotal("Subtotal Tax Included", $order->stubtotal_tax_incl, "subtotal", "none"); 
        	WriteOrderTotal("Order Total", $order->total, "total", "none"); 		
		writeCloseTag("Totals");
	}

	// // Write XML for all products for the given order
	function WriteOrderItems($items, $currencyFactor)
	{
	    //$imageRoot = HTTP_CATALOG_SERVER . DIR_WS_CATALOG . DIR_WS_IMAGES;	
		writeStartTag("Items");
		foreach($items as $item)
		{
			$product = $item->product; 
			// Build fully qualified image url
			// $imageUrl = $product['products_image'];
			// if (isset($imageUrl) and strlen($imageUrl) > 0)
			// {
			//     $imageUrl = $imageRoot . $imageUrl;
			// }
			
			writeStartTag("Item");
				writeElement("ItemID", $item->id);
				writeElement("ProductID", $product->id);
				writeElement("Code", $item->product_sku);
				writeElement("Name", $product->name);
				writeElement("Quantity", $item->quantity);
				writeElement("UnitPrice", $item->price * $currencyFactor);
				//writeElement("Image", $imageUrl);

				$weight = $product->weight;
				if ($weight)
				{
					writeElement("Weight", $weight);
				}
				else
				{
					writeElement("Weight", "0");
				}
				
				// Write attributes
				WriteItemAttributes($item->product, $currencyFactor);
				
			writeCloseTag("Item");
		}
		
		writeCloseTag("Items");
	}
	
	// // Write all attributes for the item
	function WriteItemAttributes($product, $currencyFactor)
	{
		writeStartTag("Attributes");
			writeStartTag("Attribute");
				writeElement("AttributeID", $product->id);
				writeElement("Name", $product->name);
				//writeElement("Value", $att['products_options_values']);
				writeElement("Price", $product->price * $currencyFactor);
			writeCloseTag("Attribute");
		writeCloseTag("Attributes");
	}
	
	function Action_GetStatusCodes()
	{
		writeStartTag("StatusCodes");
		$statObj = new Shop_OrderStatus(); 
		$statuses = $statObj->list_all_statuses(); 
		foreach($statuses as $status)
		{
			writeStartTag("StatusCode");
				writeElement("Code", $status->code);
				writeElement("Name", $status->name);
			writeCloseTag("StatusCode");
		}
				
		writeCloseTag("StatusCodes");
	}

	function Action_UpdateStatus()
	{	    
	    if (!isset($_REQUEST['order']) || !isset($_REQUEST['status']) || !isset($_REQUEST['comments']))
	    {
	        outputError(40, "Not all parameters supplied.");
	        return;
	    }
	    
	    $order_id = (int) $_REQUEST['order'];
	    $code = mysql_escape_string($_REQUEST['status']);
	    $comments = mysql_escape_string($_REQUEST['comments']);

	    $dbhelper = new Db_DbHelper(); 

	    $sql = "insert into shop_order_notes (order_id, created_at, note) values ('" . $order_id . "', now(), '" . $comments . "')";
	    $dbhelper->query($sql);
	    echo $sql; exit; 
	   
        $sql = "update shop_orders set status_id = (select id from shop_order_statuses where code = '" . $code . "') where id = '" . $order_id."'"; 
        $dbhelper->query($sql);

		echo "<UpdateSuccess/>";	
	}
	
?>