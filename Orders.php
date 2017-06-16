<?php
require_once('classes/Error.php');
require_once('classes/class.gmailmalier.php');
require_once('classes/Activity.php');

class Orders {
	
	private $_access_token = "";
	private $_code = "";
	private $_curl_url = "";
	private $_user_id = "";
	
	
	public function getMeliOrder($id)
	{
		$curl = curl_init();
		curl_setopt_array($curl, array(CURLOPT_RETURNTRANSFER => 1, CURLOPT_URL => "https://api.mercadolibre.com/orders/{$id}?access_token=$this->_access_token",   CURLOPT_USERAGENT => 'Codular Sample cURL Request'));
		//echo "https://api.mercadolibre.com/orders/{$id}?access_token=$this->_access_token";
		$resp = curl_exec($curl);
		$obj = json_decode($resp);
		//print_r($obj);
		//echo "https://api.mercadolibre.com/orders/{$id}?access_token=$this->_access_token";
		return $obj;
		
	}
	
	private function GetVETime($ts)
	{
		$t = DateTime::createFromFormat('Y-m-d\TH:i:s.uP', $ts, new DateTimeZone('UTC'));
// set it to whatever you want to convert it
		$t->setTimezone(new DateTimeZone('America/Caracas'));
		
		return $t;	
	}
	
	public function __construct($access_token, $code, $user_id) {
		$this->_access_token = $access_token;
		$this->_user_id = $user_id;
		$this->_code = $code;
	}
	
	private function getResourceNumber($resource)
	{
		//echo $resource;
		$pos = strpos($resource,'/',1);
		//echo $pos . " " . strlen($resource) . " ";
		//echo strlen($resource);
		$id = substr($resource,$pos+1,strlen($resource)-$pos);	
		//echo $id;
		return $id;	
		
	}
	
	public function processOrders(){
		
		
		$db = new Dbase();
		$sql = "Select distinct resource from `notifications` where `topic`='orders' and `processed`='0'";
		//echo $sql;
		$allrecords = $db->fetchAll($sql);
		if (!empty($allrecords)) {
		//echo "hola";
			foreach ($allrecords as $record)
			{
				//echo "hola2";
				//echo $record['resource'];
				//echo $OrderID;
				
				
				//$sql = "Select `id` from `orders` where `id` = '{$this->getResourceNumber($record['resource'])}'";
				//$result = $db->fetchOne($sql);
	
				//Detallo todo el modelo de Datos de MELI para las Ordenes
				//echo $record['resource'] . "\n";
				//echo $this->getResourceNumber($record['resource']);
				$meliOrder = $this->getMeliOrder($this->getResourceNumber($record['resource']));
				
				//print_r($meliOrder);
				$orderItemARR = $meliOrder->{'order_items'};
				//echo $orderItemOBJ["id"];
				$orderItemDetailOBJ = $orderItemARR[0];
				//print_r($orderItemDetailOBJ);
				//print_r($meliOrder->{'order_items'});
				$orderBuyerOBJ = $meliOrder->{'buyer'};
				//print_r($orderBuyerOBJ);
				$BuyerPhoneOBJ = $orderBuyerOBJ->{'phone'};
				$BuyerAlternativeOBJ = $orderBuyerOBJ->{'alternative_phone'};
				$orderFeedbackOBJ = $meliOrder->{'feedback'};
				//print_r($orderFeedbackOBJ);
				$orderShippingOBJ = $meliOrder->{'shipping'};
				
				//Acceso a los datos del pago si ya esta pago en MercadoPago
				if (!($meliOrder->{'payments'})=== NULL) {
					$orderPaymentsARR = $meliOrder->{'payments'};
					$orderPaymentsDetailOBJ = $orderPaymentsARR[0];
					$paymentData = 1;
				}
				else
					{
						$paymentData = 0;
					}
				
				//
				//echo $meliOrder->{'date_created'};
				
				$timestamp_date_created = $this->GetVETime($meliOrder->{'date_created'});
				
				$timestamp_date_closed = $this->GetVETime($meliOrder->{'date_closed'});
				$timestamp_last_updated = $this->GetVETime($meliOrder->{'last_updated'});
				
				if (!(($orderFeedbackOBJ->{'sale'}->{'date_created'})=== NULL))
				{
					$timestamp_feedback_sale_date_created = $this->GetVETime($orderFeedbackOBJ->{'sale'}->{'date_created'});
				}
				else
				{
					$timestamp_feedback_sale_date_created = $this->GetVETime($meliOrder->{'date_created'});
				}
				
				if (!(($orderFeedbackOBJ->{'purchase'}->{'date_created'})=== NULL))
				{
					$timestamp_feedback_purchase_date_created = $this->GetVETime($orderFeedbackOBJ->{'purchase'}->{'date_created'});
				}
				else
				{
					$timestamp_feedback_purchase_date_created = $this->GetVETime($meliOrder->{'date_created'});
				}
				
				//echo $timestamp_date_created->format('Y-m-d H:i:s');
				
				
				$paramsOrder = array(
					"id"=>$meliOrder->{'id'}, 
					"date_created"=>$timestamp_date_created->format('Y-m-d H:i:s'),
					"date_closed"=>$timestamp_date_closed->format('Y-m-d H:i:s'),
					"last_updated"=>$timestamp_last_updated->format('Y-m-d H:i:s'),
					"order_item_id"=>$orderItemDetailOBJ->{'item'}->{'id'},
					"title"=>$orderItemDetailOBJ->{'item'}->{'title'},
					"quantity"=>$orderItemDetailOBJ->{'quantity'},
					"unit_price"=>$orderItemDetailOBJ->{'unit_price'},
					"currency_id"=>$orderItemDetailOBJ->{'currency_id'},
					"total_amount"=>$meliOrder->{'total_amount'},
					"buyer_id"=>$orderBuyerOBJ->{'id'},
					"status"=>$meliOrder->{'status'},
					"feedback_sale_status"=>$orderFeedbackOBJ->{'sale'}->{'status'},
					"feedback_sale_date_created"=>$timestamp_feedback_sale_date_created->format('Y-m-d H:i:s'),
					"feedback_sale_fulfilled"=>$orderFeedbackOBJ->{'sale'}->{'fulfilled'},
					"feedback_sale_rating"=>$orderFeedbackOBJ->{'sale'}->{'rating'},
					"feedback_purchase_status"=>$orderFeedbackOBJ->{'purchase'}->{'status'},
					"feedback_purchase_date_created"=>$timestamp_feedback_purchase_date_created->format('Y-m-d H:i:s'),
					"feedback_purchase_fulfilled"=>$orderFeedbackOBJ->{'purchase'}->{'fulfilled'},
					"feedback_purchase_rating"=>$orderFeedbackOBJ->{'purchase'}->{'rating'});
					
				//print_r($paramsOrder);
				
				if ($paymentData)
				{
					
					if (!($orderPaymentsDetailOBJ->{'date_created'}=== NULL))
					{
						$timestamp_payment_date_created = $this->GetVETime($orderPaymentsDetailOBJ->{'date_created'});
					}
							
					
					
					$paramsPayment = array(
						"id"=>$orderPaymentsDetailOBJ->{'id'},
						"status"=>$orderPaymentsDetailOBJ->{'status'},
						"payment_type"=>$orderPaymentsDetailOBJ->{'payment_method_id'},
						"date_created"=>$timestamp_payment_date_created,
						"transaction_amount"=>$orderPaymentsDetailOBJ->{'total_paid_amount'});
				
						$db->smartInclude($paramsPayment, 'payments', 'id', $orderPaymentsDetailOBJ->{'id'});
				}
				
				$paramsBuyer = array(
					"id"=>$orderBuyerOBJ->{'id'},
					"nickname"=>$orderBuyerOBJ->{'nickname'},
					"first_name"=>$orderBuyerOBJ->{'first_name'},
					"last_name"=>$orderBuyerOBJ->{'last_name'},
					"email"=>$orderBuyerOBJ->{'email'},
					"phone_area_code"=>$BuyerPhoneOBJ->{'area_code'},
					"phone_number"=>$BuyerPhoneOBJ->{'number'},
					"phone_extension"=>$BuyerPhoneOBJ->{'phone_extension'});
					
				//printf("<br>");
				//print_r($paramsBuyer);
				$paramsUpdateNotification = array(
					"processed"=>'1');
					
				$db->_insert_keys = array();
				$db->_insert_values = array();
				$db->_update_sets = array();	
				
				$db->smartInclude($paramsBuyer, 'buyer', 'id', $orderBuyerOBJ->{'id'});
				$op = $db->smartInclude($paramsOrder, 'orders', 'id', $meliOrder->{'id'});
				
				$replacements = array(
					'(%Nombre%)' => $paramsBuyer["first_name"] . ' ' . $paramsBuyer["last_name"],
					'(%producto%)' => $paramsOrder["title"],
					'(%client_id%)' => $paramsBuyer["id"],
					'(%order_id%)' => $paramsOrder["id"]);
					
				$err = new Errors();
				//$err->saveError(print_r($replacements), "0", "221", "Orders.php");
				
				//Si op = 1 es una orden nueva y hay que mandarle correo de Instrucciones
				if ($op)
				{	

					$message=file_get_contents(('emails/Instrucciones_ink.html'), dirname(__FILE__));	
					$message = preg_replace( array_keys( $replacements ), array_values( $replacements ), $message );
					
					$plaintext=$message;
					
					$plaintext = strip_tags( stripslashes( $plaintext ), '<p><br><h2><h3><h1><h4>' );
					$plaintext = str_replace( array( '<p>', '<br />', '<br>', '<h1>', '<h2>', '<h3>', '<h4>' ), PHP_EOL, $plaintext );
					$plaintext = str_replace( array( '</p>', '</h1>', '</h2>', '</h3>', '</h4>' ), '', $plaintext );
					$plaintext = html_entity_decode( stripslashes( $plaintext ) );
					
					//echo $message;
					$grt_mail = new gmailEmail();
					
					$grt_mail->_to_email_address=$paramsBuyer["email"];
					$grt_mail->_to_name=$paramsBuyer["first_name"] . ' ' . $paramsBuyer["last_name"];
					$grt_mail->_subject="Instrucciones para tu Compra en TiendaVDShop Mercado Libre";
					$grt_mail->_html=$message;
					$grt_mail->_text_body=$plaintext;
					$err->saveError($grt_mail->sendEmail(), "0", "221", "Orders.php");
					
					$activity = new Activity();
					$activity->create($paramsOrder["id"],"Email Inicial","Se envia Email con Informacion de Instrucciones para seguir con la compra","melicrm");
					$id = $meliOrder->{'id'};
					$sql = "Update `orders` set `contacted`='1' where `id`='{$id}'";
					$db->query($sql);

				}
				else
				{
					//AQUI SE HACEN TODOS LOS PROCESOS QUE SE REQUIERA CUANDO CAMBIA EL ESTATUS.
					if($paramsOrder["feedback_sale_fulfilled"])
					{
						
						if (($db->fetchOne("Select * from `activities` where `order_id`='{$id}' and `activity`='Calificado'"))===NULL)
						{
						
							$activity = new Activity();
							//PONER LUEGO ACCESO A FULFILLED Y EL TEXTO DE LA CALIFICACION
							
							
							$activity->create($paramsOrder["id"],"Calificado","Calificado","melicrm");
							$db->query($sql);	
						}
						
					}
				}
				
				$db->_update_sets = array();
				$db->prepareUpdate($paramsUpdateNotification);
				$db->update('notifications','resource',$record['resource']);
				
				
				
				
				
				
				
			}
		
		}
		
		
		
	}
	
	
			
}


