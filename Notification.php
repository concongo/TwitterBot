<?php


class meliNotification {
	
	private $_user_id = "";
	private $_resource = "";
	private $_topic = "";
	private $_received = "";
	private $_sent = "";
	
	public function __construct($user_id, $resource, $topic, $received, $sent) {
		$this->_user_id = $user_id;
		$this->_resource = $resource;
		$this->_topic = $topic;
		$this->_received = $received;
		$this->_sent = $sent;
		//echo "buenisimo!";
		//echo $this->_sent;
	}
	
	public function saveNotification()
	{
		$db = new Dbase();
		
		
		$timestamp_received = DateTime::createFromFormat('Y-m-d\TH:i:s.ue', $this->_received, new DateTimeZone('UTC'));
// set it to whatever you want to convert it
		$timestamp_received->setTimezone(new DateTimeZone('America/Caracas'));

		$timestamp_sent = DateTime::createFromFormat('Y-m-d\TH:i:s.ue', $this->_sent, new DateTimeZone('UTC'));
		// set it to whatever you want to convert it
		$timestamp_sent->setTimezone(new DateTimeZone('America/Caracas'));
		//"sent"=>$this->_sent

		//ajuste a Hora Venezuela
		$params = array("user_id"=>$this->_user_id, "resource"=>$this->_resource, "topic"=>$this->_topic,"received"=>$timestamp_received->format('Y-m-d H:i:s'),"sent"=>$timestamp_sent->format('Y-m-d H:i:s'));

		$db->prepareInsert($params);	
		return $db->insert("notifications");	
		
	}
/*
	public function responseNotificacionOK()
	{
		//header("HTTP/1.1 200 OK");
		//http_response_code(200);
        static $code = 200;

            header('X-PHP-Response-Code: '.$newcode, true, $newcode);
            if(!headers_sent())
                $code = $newcode;
        }       
        return $code;
    }
}
	*/	
	
	
}


?>