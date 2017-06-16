<?
defined("DS") 
	|| define("DS", DIRECTORY_SEPARATOR);
// dependiendo del sistema, retorna c:\ o si es mac /
	
// root path
defined("ROOT_PATH") 
	|| define("ROOT_PATH", realpath(dirname(__FILE__) . DS ."..".DS));
require_once(ROOT_PATH.'/classes/Dbase.php');
require_once(ROOT_PATH.'/classes/class.gmailmalier.php');

class meliCRMAPI
{

	public function getItemVisitsJSON($title)
	{
		$db = new Dbase();
		$title = str_replace(' ', '', $title);
		//echo "SELECT * from `v_Items_Visits` where `title` LIKE '%{$title}%' order by `date`";
		$rows= $db->fetchAll("SELECT * from `v_Items_Visits` where REPLACE(`title`, ' ', '') LIKE '%{$title}%' order by `date`");
		$DataArray = array();
		$DataArrayPair = array();
		
		foreach ($rows as $row)
		{
			//$columnkey = $row['date'];
			//$DataArray[$columnkey] = $row['daily_visits'];
			$date = new DateTime($row['date']);
			$d = DateTime::createFromFormat('Y-m-d', $row['date'] , new DateTimeZone('UTC'));
			//echo strtotime($d->format('Y-m-d H:i:s'));
			$DataArrayPair[]=strtotime($d->format('Y-m-d H:i:s'))*1000; //se multiplica por 1000 para llevarlo a milisegundos ya que sera usado en jquery de los graficos y este lo requiere asi
			$DataArrayPair[]=(int)$row['daily_visits'];
			//echo strtotime($d->format('Y-m-d H:i:s'));
			$DataArray[]=$DataArrayPair;
			$DataArrayPair = array();
			$item_name = $row["title"];
			//echo $date->format('Y-m-d') . " UTC";
		}
		$JSONArray = array(
			'label'=>$item_name,
			'data'=>$DataArray);
		//echo "SELECT * from `v_Items_Visits` where TRIM(`title`) LIKE '%{$title}%' order by `date`";
		echo json_encode($JSONArray);
			
		
	}

	public function getItemQSalesJSON($title)
	{
		$db = new Dbase();
		
		$rows= $db->fetchAll("SELECT * from `v_items_sales`  where REPLACE(`title`, ' ', '') LIKE '%{$title}%' order by `date`");
		$DataArray = array();
		$DataArrayPair = array();
		
		foreach ($rows as $row)
		{
			//$columnkey = $row['date'];
			//$DataArray[$columnkey] = $row['daily_visits'];
			$date = new DateTime($row['order_date']);
			$d = DateTime::createFromFormat('Y-m-d', $row['order_date'] , new DateTimeZone('UTC'));
			//echo strtotime($d->format('Y-m-d H:i:s'));
			$DataArrayPair[]=strtotime($d->format('Y-m-d H:i:s'))*1000; //se multiplica por 1000 para llevarlo a milisegundos ya que sera usado en jquery de los graficos y este lo requiere asi
			$DataArrayPair[]=(int)$row['q'];
			//echo strtotime($d->format('Y-m-d H:i:s'));
			$DataArray[]=$DataArrayPair;
			$DataArrayPair = array();
			$item_name = $row["title"];
			//echo $date->format('Y-m-d') . " UTC";
		}
		$JSONArray = array(
			'label'=>$item_name,
			'data'=>$DataArray);
		
		echo json_encode($JSONArray);
	}

	public function getItemRSalesJSON($title)
	{
		$db = new Dbase();
		
		$rows= $db->fetchAll("SELECT * from `v_items_sales`  where REPLACE(`title`, ' ', '') LIKE '%{$title}%' order by `date`");
		$DataArray = array();
		$DataArrayPair = array();
		
		foreach ($rows as $row)
		{
			//$columnkey = $row['date'];
			//$DataArray[$columnkey] = $row['daily_visits'];
			$date = new DateTime($row['order_date']);
			$d = DateTime::createFromFormat('Y-m-d', $row['order_date'] , new DateTimeZone('UTC'));
			//echo strtotime($d->format('Y-m-d H:i:s'));
			$DataArrayPair[]=strtotime($d->format('Y-m-d H:i:s'))*1000; //se multiplica por 1000 para llevarlo a milisegundos ya que sera usado en jquery de los graficos y este lo requiere asi
			$DataArrayPair[]=(int)$row['revenue'];
			//echo strtotime($d->format('Y-m-d H:i:s'));
			$DataArray[]=$DataArrayPair;
			$DataArrayPair = array();
			$item_name = $row["title"];
			//echo $date->format('Y-m-d') . " UTC";
		}
		$JSONArray = array(
			'label'=>$item_name,
			'data'=>$DataArray);
		
		echo json_encode($JSONArray);
	}

	public function getAllItemsJSON()
	{
		$db = new Dbase();
		
		$rows= $db->fetchAll("SELECT distinct title from `items` order by `title`");
		//$DataArray = array();
		//$DataArrayPair = array();
		
		foreach ($rows as $row)
		{
			
			$JSONArray[] = array(
				'title'=>$row['title']);
			
			}
		//return print_r($JSONArray);*/
		$JSON[]=array(
			'id'=>"0",
			'results'=>$JSONArray);
		
		
		echo json_encode(array(
			
			'results'=>$JSONArray));
	}
	
	public function getOrderDetails($orderid) {
		
		$db = new Dbase();
		
		$rows= $db->fetchOne("SELECT *  from `v_orders` where  `id`='{$orderid}'");
		//echo "SELECT *  from `orders` where  `id`='{$orderid}'";
		echo json_encode($rows);
		
		
	}
	
	public function getOrdDetails($orderid) {
		
		$db = new Dbase();
		
		$rows= $db->fetchOne("SELECT *  from `v_orders` where  `id`='{$orderid}'");
		//echo "SELECT *  from `orders` where  `id`='{$orderid}'";
		return json_encode($rows);
		
		
	}
	
	public function paymentCreated($orderid) {
		
		$db = new Dbase();
		//$rows = $db->fetchOne("SELECT * from ");
		
	}
	
	public function orderClosed($orderid){
		$db = new Dbase();
		$rows = $db->fetchOne("SELECT * from `v_orders` where `id` ='{$orderid}'");
		//echo "SELECT * from `v_orders` where `id` ='{$orderid}'";
		//echo $rows["feedback_sale_fulfilled"];
		if ($rows["feedback_sale_fulfilled"]=="1") {
			echo "cerrada";
		} else
		{ echo "abierta";}
			
	}
	
	public function registerOrderPayment($obj) {
		$db = new Dbase();
		
		$orderid = $obj->{'order'}->{'id'};
		$completeOrder = json_decode($this->getOrdDetails($orderid));
		$objPayment = $obj->{'payment'};//date,destiny_bank, type, origin_bank, amount
		$objContact = $obj->{'contact'};//email,twitter,instagram,phone
		$objShipping = $obj->{'shipping'};//customer_name,customer_contact,customer_id,customer_phone,customer_location,customer_address
		
		$paramsOrder = array(
					"id"=>$orderid,
					"color"=>$obj->{'order'}->{'color'},
					"additional"=>$obj->{'order'}->{'additional'}
					);
		
		$db->_insert_keys = array();
		$db->_insert_values = array();
		$db->prepareInsert($paramsOrder);
		$result = $db->insert("orders_addendum");
		//$sql = $db->createInsertSQL("orders_addendum");
		
		if(isset($obj->{'payment'})) {
			$objPayment = $obj->{'payment'};
			$paramsPayment = array(
						"id"=>$orderid,
						"date"=>$objPayment->{'date'},
						"destiny_bank"=>$objPayment->{'destiny_bank'},
						"type"=>$objPayment->{'type'},
						"origin_bank"=>$objPayment->{'origin_bank'},
						"reference"=>$objPayment->{'reference'},
						"amount"=>$objPayment->{'amount'});
			$db->_insert_sets = array();
			$db->prepareInsert($paramsPayment);
			$result .= $db->insert("payments");
			//$sql .= $db->createInsertSQL("payments");
		}
		
				
		$paramsContact = array(
					"id"=>$objContact->{'id'},
					"email"=>$objContact->{'email'},
					"twitter"=>$objContact->{'twitter'},
					"instagram"=>$objContact->{'instagram'},
					"phone"=>$objContact->{'phone'});

			$db->_insert_keys = array();
			$db->_insert_values = array();
			$db->prepareInsert($paramsContact);
			$result .= $db->insert("buyer_contact");
			//$sql .= $db->createInsertSQL("buyer_contact");

		$paramsShipping = array(
					"id"=>$objShipping->{'id'},
					"customer_name"=>$objShipping->{'customer_name'},
					"customer_contact"=>$objShipping->{'customer_contact'},
					"customer_id"=>$objShipping->{'customer_id'},
					"customer_phone"=>$objShipping->{'customer_phone'},
					"customer_location"=>$objShipping->{'customer_location'},
					"customer_address"=>$objShipping->{'customer_address'});
					
			$db->_insert_keys = array();
			$db->_insert_values = array();
			$db->prepareInsert($paramsShipping);
			$result .= $db->insert("buyer_shipping");
			//$sql .= $db->createInsertSQL("buyer_shipping");
			//$result = $db->query($sql);

		$replacements = array(
					'(%Nombre%)' => $completeOrder->{"first_name"} . ' ' . $completeOrder->{"last_name"},
					'(%title%)' => $completeOrder->{"title"},
					'(%order_id%)' => $orderid,
					'(%quantity%)' => $completeOrder->{"quantity"},
					'(%unit_price%)' => $completeOrder->{"unit_price"},
					'(%color%)' => $paramsOrder["color"],
					'(%additional%)' => $paramsOrder["additional"],
					'(%email%)' => $paramsContact["email"],
					'(%twitter%)' => $paramsContact["twitter"],
					'(%instagram%)' => $paramsContact["instagram"],
					'(%phone%)' => $paramsContact["phone"],
					'(%destiny_bank%)' => $paramsPayment["destiny_bank"],
					'(%type%)' => $paramsPayment["type"],
					'(%date%)' => $paramsPayment["date"],
					'(%amount%)' => $paramsPayment["amount"],
					'(%origin_bank%)' => $paramsPayment["origin_bank"],
					'(%reference%)' => $paramsPayment["reference"],
					'(%customer_name%)' => $paramsShipping["customer_name"],
					'(%customer_contact%)' => $paramsShipping["customer_contact"],
					'(%customer_id%)' => $paramsShipping["customer_id"],
					'(%customer_phone%)' => $paramsShipping["customer_phone"],
					'(%customer_location%)' => $paramsShipping["customer_location"],
					'(%customer_address%)' => $paramsShipping["customer_address"]);
		
		$updatePaymentReceived = array(
			"payment_received"=>1);
			
		$db->_update_sets = array(); 
		$db->prepareUpdate($updatePaymentReceived);
		$db->update("orders", "id", $orderid);			

			
			$grt_mail = new gmailEmail();	
			$grt_mail->prepareTemplate(ROOT_PATH.'/emails/paymentreport_ink.html', $replacements);
			
			$mlemail = $completeOrder->{"email"};
			$mlcustomer = $completeOrder->{"first_name"} . ' ' . $completeOrder->{"last_name"};
			
			$grt_mail->_to_email_address=$paramsContact["email"];
			$grt_mail->_to_name=$paramsShipping["customer_name"];
			$grt_mail->_to_email_address2=$mlemail;
			$grt_mail->_to_name2=$mlcustomer;
			$grt_mail->_subject="Hemos recibido tu Reporte de Pago en TiendaVDShop Mercado Libre";
			if($grt_mail->sendEmail())
			{
				echo 1;
			}
			else
			{
				echo 0;
			}		
		
			
	}


}