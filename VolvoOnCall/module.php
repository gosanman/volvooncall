<?php

    declare(strict_types=1);

    class VolvoOnCall extends IPSModule {
		
        public function Create() {
            	
		parent::Create();
		
		//Erstelle Profile
		if (!IPS_VariableProfileExists('Volvo.Distance')) {
			IPS_CreateVariableProfile('Volvo.Distance', 1);
			IPS_SetVariableProfileValues('Volvo.Distance', 0, 0, 1);
			IPS_SetVariableProfileText('Volvo.Distance', '', ' km');
			IPS_SetVariableProfileIcon('Volvo.Distance', 'Distance');
		}
		if (!IPS_VariableProfileExists('Volvo.FuelAmount')) {
			IPS_CreateVariableProfile('Volvo.FuelAmount', 1);
			IPS_SetVariableProfileValues('Volvo.FuelAmount', 0, 60, 1);
			IPS_SetVariableProfileText('Volvo.FuelAmount', '', ' l');
			IPS_SetVariableProfileIcon('Volvo.FuelAmount', 'Drops');
		}
		if (!IPS_VariableProfileExists('Volvo.FuelLevel')) {
			IPS_CreateVariableProfile('Volvo.FuelLevel', 1);
			IPS_SetVariableProfileValues('Volvo.FuelLevel', 0, 100, 1);
			IPS_SetVariableProfileText('Volvo.FuelLevel', '', ' %');
			IPS_SetVariableProfileIcon('Volvo.FuelLevel', 'Gauge');
		}
			
            	//Erstelle Variablen
		if (!@$this->GetIDForIdent('positionLongitude')) {
			$positionLongitude = $this->RegisterVariableString('positionLongitude', 'positionLongitude', '', 0);
			IPS_SetIcon($positionLongitude, 'Move');
		}
		if (!@$this->GetIDForIdent('positionLatitude')) {
			$positionLatitude = $this->RegisterVariableString('positionLatitude', 'positionLatitude', '', 0);
			IPS_SetIcon($positionLatitude, 'Move');
		}
		if (!@$this->GetIDForIdent('positionTime')) {
			$positionTime = $this->RegisterVariableString('positionTime', 'positionTime', '', 0);
			IPS_SetIcon($positionTime, 'Clock');
		}
		if (!@$this->GetIDForIdent('positionPic')) {
			$positionPic = $this->RegisterVariableString('positionPic', 'positionPic', '~HTMLBox', 0);
			IPS_SetIcon($positionPic, 'Image');
		}
		if (!@$this->GetIDForIdent('fuelAmount')) {
			$this->RegisterVariableInteger('fuelAmount', 'fuelAmount', 'Volvo.FuelAmount', 0);
		}
		if (!@$this->GetIDForIdent('fuelAmountLevel')) {
			$this->RegisterVariableInteger('fuelAmountLevel', 'fuelAmountLevel', 'Volvo.FuelLevel', 0);
		}
		if (!@$this->GetIDForIdent('distanceToEmpty')) {
			$this->RegisterVariableInteger('distanceToEmpty', 'distanceToEmpty', 'Volvo.Distance', 0);
		}
		if (!@$this->GetIDForIdent('odoMeter')) {
			$this->RegisterVariableInteger('odoMeter', 'odoMeter', 'Volvo.Distance', 0);
		}
		if (!@$this->GetIDForIdent('carLocked')) {
			$this->RegisterVariableBoolean('carLocked', 'carLocked', '~Lock', 0);
		}
				
        $this->RegisterPropertyString('GoogleApiKey', "");
        $this->RegisterPropertyString('Username', "");
		$this->RegisterPropertyString('Password', "");
		$this->RegisterPropertyInteger('Interval', 5);
			
		$this->RegisterTimer('UpdateTimer', 0, 'VOC_Update($_IPS[\'TARGET\']);');
        }
	    
	public function Destroy() {
		
		parent::Destroy();
	}

        public function ApplyChanges() {
            	
		parent::ApplyChanges();
		$this->SetTimerInterval('UpdateTimer', $this->ReadPropertyInteger('Interval') * 60 * 1000);
        }

        public function RequestAction($Ident, $Value) {
            $this->Update();
        }

        public function Update() {

			$url = 'https://vocapi.wirelesscar.net/customerapi/rest/v3.0/customeraccounts';
			$data = $this->GetData($url);
			$accountVehicleRelationURL = $data["accountVehicleRelations"];
			//$sumCars = count($accountVehicleRelationURL);
			$accountVehicleRelationURL0 = $accountVehicleRelationURL[0];
			$data = $this->GetData($accountVehicleRelationURL0);
			$vehicle0URL = $data["vehicle"];
			$this->GetPosition($vehicle0URL);
			$this->GetCarStatus($vehicle0URL);
			//$this->SendDebug("Update", $vehicle0URL, 0);
        }
		
		public function GetData($url, $call = "") {

			$username = $this->ReadPropertyString('Username');
			$password = $this->ReadPropertyString('Password');
			
			$curl = curl_init();

			curl_setopt_array($curl, array(
			CURLOPT_URL => $url . $call,
			CURLOPT_USERPWD => $username . ":" . $password,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
				"Accept: */*",
				"Accept-Encoding: gzip, deflate",
				"Connection: keep-alive",
				"Host: vocapi.wirelesscar.net",
				"cache-control: no-cache,no-cache",
				"content-type: application/json",
				"x-device-id: Device",
				"x-originator-type: App",
				"x-os-type: Android",
				"x-os-version: 22"
			),
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);

			if ($err) {
			echo "cURL Error #:" . $err;
			} else {
				$arr = json_decode($response, true);
				return $arr;
			}
		}
		
		public function GetPosition($vehicle0URL) {
			
			$apikey = $this->ReadPropertyString('GoogleApiKey');
			
			$position = $this->GetData($vehicle0URL, "/position");
			$longitude = str_replace(",",".",$position["position"]["longitude"]);
			SetValueString($this->GetIDForIdent('positionLongitude'), $longitude);
			$latitude = str_replace(",",".",$position["position"]["latitude"]);
			SetValueString($this->GetIDForIdent('positionLatitude'), $latitude);

			$dateInUTC = $position["position"]["timestamp"];
			$time = strtotime($dateInUTC.' UTC');
			$dateOutLocal = date("Y-m-d H:i:s", $time);
			SetValueString($this->GetIDForIdent('positionTime'), $dateOutLocal. ' Uhr');
			SetValueString($this->GetIDForIdent('positionPic'), '<iframe frameborder="0" class="map-top" width="100%" height="600px" src="https://www.google.com/maps/embed/v1/place?key='.$apikey.'&q='.$latitude.','.$longitude.'&zoom=19&maptype=roadmap" allowfullscreen=""></iframe>');
		
		}
		
		public function GetCarStatus($vehicle0URL) {

			$status = $this->GetData($vehicle0URL, "/status");

			//$status["tyrePressure"]["frontLeftTyrePressure"];
			//$status["tyrePressure"]["frontRightTyrePressure"];
			//$status["tyrePressure"]["rearLeftTyrePressure"];
			//$status["tyrePressure"]["rearRightTyrePressure"];
			//$status["tyrePressure"]["timestamp"];
		  
			//($status["averageFuelConsumption"]/10);
			//$status["averageFuelConsumptionTimestamp"];
			
			//$status["averageSpeed"];
			//$status["averageSpeedTimestamp"];
			
			//$status["brakeFluid"];
			//$status["brakeFluidTimestamp"];

			//$status["doors"]["tailgateOpen"];
			//$status["doors"]["rearRightDoorOpen"];
			//$status["doors"]["rearLeftDoorOpen"];
			//$status["doors"]["frontRightDoorOpen"];
			//$status["doors"]["frontLeftDoorOpen"];
			//$status["doors"]["hoodOpen"];
			//$status["doors"]["timestamp"];  

			//$status["engineRunning"];
			//$status["engineRunningTimestamp"];

			SetValue($this->GetIDForIdent('carLocked'), $status["carLocked"]);
			//$status["carLocked"];
			//$status["carLockedTimestamp"];

			//$status["serviceWarningStatus"];
			//$status["serviceWarningStatusTimestamp"];

			//$status["washerFluidLevel"];
			//$status["washerFluidLevelTimestamp"];

			SetValue($this->GetIDForIdent('fuelAmount'), $status['fuelAmount']);
			//$status["fuelAmountTimestamp"];
			SetValue($this->GetIDForIdent('fuelAmountLevel'), $status["fuelAmountLevel"]);
			//$status["fuelAmountLevelTimestamp"];
			SetValue($this->GetIDForIdent('distanceToEmpty'), $status["distanceToEmpty"]);
			//$status["distanceToEmptyTimestamp"];

			if ($status["odometer"] != 0) { SetValue($this->GetIDForIdent('odoMeter'), intval(($status["odometer"]/1000))); }
			//$status["odometerTimestamp"];

			//($status["tripMeter1"]/1000);
			//$status["tripMeter1Timestamp"];
		}		
		
    }
