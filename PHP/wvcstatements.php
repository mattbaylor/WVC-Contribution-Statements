<?php
require_once('tcpdf/config/lang/eng.php');
require_once('tcpdf/tcpdf.php');
 
class Contrib_Statement extends TCPDF {
	public $year = '';
	public $DBServer = 'PSQL01RR\PRODUCTION';
	public function Header() {
		$this->SetY(-300);
		$this->setJPEGQuality(90);
		$this->Image('C:\inetpub\utils\public_html\images\whitelogo.jpg', 7, 0, 35, 0, 'JPG', 'http://woodmenvalley.org');
		//$this->CreateTextBox('Thank you for your gifts and continuing contributions that support the ministries of Woodmen Valley. Your financial gifts and offerings continue to be vital to the ministries of Woodmen, and together may we rejoice in God\'s bounty. Listed below are the contributions you have made. They will help us to further the purposes of the church and continue to see God\'s amazing work accomplished through this community of believers!',25,0,40,40,10,'','L');
		$this->SetFont(PDF_FONT_NAME_MAIN,'','9');
		$this->SetTextColor(0,0,0,60);
		$this->SetXY(45, 10);
		$this->MultiCell(150,10,'Thank you for your gifts and continuing contributions that support the ministries of Woodmen Valley. Your financial gifts and offerings continue to be vital to the ministries of Woodmen, and together may we rejoice in God\'s bounty. Listed below are the contributions you have made. They will help us to further the purposes of the church and continue to see God\'s amazing work accomplished through this community of believers!',0,'L', 0, 1, '', '', true, null, true);
		$this->SetXY(45, 30);
		$this->MultiCell(150,10,'If you should have any questions, please feel free to call the Finance Department at 719.388.5000.',0,'L', 0, 1, '', '', true, null, true);
		$this->SetXY(45, 35);
		$this->SetTextColor(100,72,0,32);
		$this->SetFont(PDF_FONT_NAME_MAIN,'B','9');
		$this->MultiCell(150,10,'No goods or services have been rendered for these contributions.',0,'L', 0, 1, '', '', true, null, true);
	}
	public function setMeta($author = 'Woodmen Valley Chapel',$title = "Contribution Statement", $subject = "Contribution Statement", $keywords='') {
		$this->SetCreator(PDF_CREATOR);
		$this->SetAuthor($author);
		$this->SetTitle($title);
		$this->SetSubject($subject);
		$this->SetKeywords($keywords);
	}
	public function Footer() {
		$this->Image('C:\inetpub\utils\public_html\images\bottomart.jpg', -1, 270, 0, 31, 'JPG', 'http://woodmenvalley.org');
		$this->SetY(-55);
		$this->SetFont(PDF_FONT_NAME_MAIN, '', 10);
		$this->Image('C:\inetpub\utils\public_html\images\address.jpg', 140, 245, 0, 25, 'JPG', 'http://woodmenvalley.org');
	}
	public function CreateTextBox($textval, $x = 0, $y, $width = 0, $height = 10, $fontsize = 10, $fontstyle = '', $align = 'L') {
		$this->SetXY($x+20, $y); // 20 = margin left
		$this->SetFont(PDF_FONT_NAME_MAIN, $fontstyle, $fontsize);
		$this->Cell($width, $height, $textval, 0, false, $align);
	}
	public function setAddress($greeting = '', $street1 = '', $street2 = '', $cityStateZip = '') {
		if(strlen(trim($street2)) == 0) {
			$street2 = $cityStateZip;
			$cityStateZip = '';	
		}
		$this->CreateTextBox($greeting, -10, 65, 80, 10, 10, 'B');
		$this->CreateTextBox($street1, -10, 70, 80, 10, 10);
		$this->CreateTextBox($street2, -10, 75, 80, 10, 10);
		$this->CreateTextBox($cityStateZip, -10, 80, 80, 10, 10);	
	}
	public function setAddressFromDB($givingUnitId) {
		$link = mssql_connect($this->DBServer, 'ArenaRO', 'arena');
		if (!$link) {
    		die('Something went wrong while connecting to '.$this->DBServer);
		}
		$sql = '
			SELECT TOP 1 cp.last_name, [ArenaDB].[dbo].[wvc_funct_givingGreeting](\''.$givingUnitId.'\') AS greeting,ca.street_address_1, ca.street_address_2, ca.city, ca.state, ca.postal_code
			FROM core_person cp
				INNER JOIN core_person_address cpa ON cp.person_id = cpa.person_id
				INNER JOIN core_address ca ON cpa.address_id = ca.address_id
				INNER JOIN core_family_member cfm ON cp.person_id = cfm.person_id
			WHERE cpa.primary_address = 1
				AND cfm.role_luid = 29
				AND cp.giving_unit_id = \''.$givingUnitId.'\';';
		$result = mssql_query($sql,$link);
		$obj = mssql_fetch_object($result);
		if(is_object($obj)) {
			$this->setAddress($obj->greeting,$obj->street_address_1,$obj->street_address_2,$obj->city . ', ' . $obj->state . '   ' . $obj->postal_code);
			return $obj->last_name;
			mssql_free_result($result);
		}
		//odbc_close($link);
	}
	public function createDetailSection($givingUnitId,$year) {
		if(!isset($year)){
			$year = date('Y');	
		}
		$link = mssql_connect($this->DBServer, 'ArenaRO', 'arena');
		if (!$link) {
    		die('Something went wrong while connecting to '.$this->DBServer);
		}
		$sql = '
			SELECT cp.giving_unit_id, cc.contribution_date, cc.transaction_number, [ArenaDB].[dbo].[wvc_funct_fundfromFundId](ccf.fund_id) AS fund, ccf.amount, cp.nick_name + \' \' + cp.last_name AS name
			FROM ctrb_contribution cc
				INNER JOIN ctrb_contribution_fund ccf ON cc.contribution_id = ccf.contribution_id
				INNER JOIN core_person cp ON cc.person_id = cp.person_id
			WHERE cc.contribution_date >= \'1/1/'.$year.' 00:00:00\' 
				AND cc.contribution_date <= \'12/31/'.$year.' 23:59:59\' 
				AND ccf.contribution_fund_id IN (
					SELECT ccf.contribution_fund_id
					FROM ctrb_contribution_fund ccf
						INNER JOIN ctrb_fund cf ON ccf.fund_id = cf.fund_id
					WHERE cf.tax_deductible <> 0
					)
				AND cp.giving_unit_id = \''.$givingUnitId.'\'
			ORDER BY cp.nick_name + \' \' + cp.last_name, [ArenaDB].[dbo].[wvc_funct_fundfromFundId](ccf.fund_id), cc.contribution_date;';
		$result	 = mssql_query($sql,$link);
		
		//Summary Page
		$this->SetTextColor(0,0,0,60);
		$this->CreateTextBox(strtoupper('summary of contributions jan 1 - dec 31, '.$year),0,85,'',10,'','','C');

		$currY = 95;
		$total = 0;
		$arr = mssql_fetch_assoc($result);
		$curPerson = $arr['name'];
		mssql_data_seek($result,0);
		$personTotal = 0;
		// list headers
		$this->SetTextColor(100,72,0,32);
		//$this->CreateTextBox('Date', 0, $currY, 20, 10, 10, 'B', 'L');
		$this->CreateTextBox('Name', 20, $currY, 90, 10, 10, 'B');
		//$this->CreateTextBox('Total', 90, $currY, 30, 10, 10, 'B', 'L');
		$this->CreateTextBox('Total', 125, $currY, 30, 10, 10, 'B', 'R');
		$currY += 7;
		$this->Line(20, $currY, 195, $currY, array('color' => array(27,3,0,13)));
		$this->SetTextColor(0,0,0,100);
		while($row = mssql_fetch_array($result)) {
			if($currY >= 222) {
				$this->AddPage();
				$currY = 50;				
			}
			if($curPerson != $row['name']){
				$this->CreateTextBox($curPerson, 20, $currY, 30, 10, 10, 'B', 'L');
				$this->CreateTextBox('$'.number_format($personTotal,2), 125, $currY, 30, 10, 10, 'B', 'R');
				$currY += 5;
				$curPerson = $row['name'];
				$personTotal = 0;	
			} 
			$total += $row['amount'];
			$personTotal += $row['amount'];
		}
		$this->CreateTextBox($curPerson, 20, $currY, 30, 10, 10, 'B', 'L');
		$this->CreateTextBox('$'.number_format($personTotal,2), 125, $currY, 30, 10, 10, 'B', 'R');
		$currY += 5;
		
		$this->Line(20, $currY+4, 195, $currY+4);

		// output the total row
		$this->CreateTextBox('Total', 10, $currY+5, 20, 10, 10, 'B', 'R');
		$this->CreateTextBox('$'.number_format($total,2), 125, $currY+5, 30, 10, 10, 'B', 'R');
		//mssql_free_result($result);
		//odbc_close($link);
		
		//reset($result);
		$this->AddPage();
		$currY = 50;
		mssql_data_seek($result,0);
		
		//Detail List	
		$this->SetTextColor(0,0,0,60);
		$this->CreateTextBox(strtoupper('list of contributions jan 1 - dec 31, '.$year),0,85,'',10,'','','C');

		$currY = 90;
		$total = 0;
		$arr = mssql_fetch_assoc($result);
		$curFund = $arr['fund'];
		$curPerson = $arr['name'];
		mssql_data_seek($result,0);
		$fundTotal = 0;
		$personTotal = 0;
		// list headers
		$this->SetTextColor(100,72,0,32);
		$this->CreateTextBox('Date', 0, $currY, 20, 10, 10, 'B', 'L');
		$this->CreateTextBox('Transaction Details', 20, $currY, 90, 10, 10, 'B');
		$this->CreateTextBox('Fund', 90, $currY, 30, 10, 10, 'B', 'L');
		$this->CreateTextBox('Amount', 140, $currY, 30, 10, 10, 'B', 'R');
		$currY += 7;
		$this->Line(20, $currY, 195, $currY, array('color' => array(27,3,0,13)));
		$this->SetTextColor(0,0,0,100);
		while($row = mssql_fetch_array($result)) {
			if($currY >= 222) {
				$this->AddPage();
				$currY = 50;				
			}
			if($curPerson != $row['name']){
				$this->CreateTextBox($curFund.' Total for '.$curPerson, 10, $currY, 30, 10, 10, 'B', 'L');
				$this->CreateTextBox('$'.number_format($fundTotal,2), 140, $currY, 30, 10, 10, 'B', 'R');
				$currY += 5;
				$this->CreateTextBox($curPerson.' Total', 10, $currY, 30, 10, 10, 'B', 'L');
				$this->CreateTextBox('$'.number_format($personTotal,2), 140, $currY, 30, 10, 10, 'B', 'R');
				$this->AddPage();
				$currY = 50;
				$curFund = $row['fund'];
				$fundTotal = 0;	
				$curPerson = $row['name'];
				$personTotal = 0;	
			} else if($curFund != $row['fund']){
				$this->CreateTextBox($curFund.' Total for '.$curPerson, 10, $currY, 30, 10, 10, 'B', 'L');
				$this->CreateTextBox('$'.number_format($fundTotal,2), 140, $currY, 30, 10, 10, 'B', 'R');
				$currY += 10;
				$curFund = $row['fund'];
				$fundTotal = 0;
			}
			$this->CreateTextBox(date('m/d/Y',strtotime($row['contribution_date'])), 0, $currY, 20, 10, 10, '', '');
			$this->CreateTextBox($row['transaction_number'], 20, $currY, 90, 10, 10, '');
			$this->CreateTextBox($row['fund'], 90, $currY, 30, 10, 10, '', '');
			$this->CreateTextBox('$'.number_format($row['amount'],2), 140, $currY, 30, 10, 10, '', 'R');
			$currY += 5;
			$total += $row['amount'];
			$fundTotal  += $row['amount'];
			$personTotal += $row['amount'];
		}
		$this->CreateTextBox($curFund.' Total for '.$curPerson, 10, $currY, 30, 10, 10, 'B', 'L');
		$this->CreateTextBox('$'.number_format($fundTotal,2), 140, $currY, 30, 10, 10, 'B', 'R');
		$currY += 10;
		$this->CreateTextBox($curPerson.' Total', 20, $currY, 10, 10, 10, 'B', 'L');
		$this->CreateTextBox('$'.number_format($personTotal,2), 140, $currY, 30, 10, 10, 'B', 'R');
		$currY += 5;
		$this->Line(20, $currY+4, 195, $currY+4);

		// output the total row
		$this->CreateTextBox('Total', 10, $currY+5, 135, 10, 10, 'B', 'R');
		$this->CreateTextBox('$'.number_format($total,2), 140, $currY+5, 30, 10, 10, 'B', 'R');
		mssql_free_result($result);
		//odbc_close($link);
		
	}
	public function addtoDB($givingUnitId,$filePath,$year,$attID) {
		$this->cleanUpDB($givingUnitId,$year,$attID);
		
		$link = mssql_connect($this->DBServer, 'ArenaRW', 'write');
		if (!$link) {
    		die('Something went wrong while connecting to '.$this->DBServer);
		}
		
		
		
		$sql = "
			SELECT cp.last_name, cp.person_id 
			FROM core_person cp
				INNER JOIN core_family_member cfm ON cp.person_id = cfm.person_id
			WHERE cp.giving_unit_id = '$givingUnitId' 
				AND cfm.role_luid = 29;";
		
		$persons = mssql_query($sql,$link);
		$fileArr = explode("\\",$filePath);
		$filename = $fileArr[count($fileArr)-1];
		$filename = str_replace('.pdf','',$filename);
		
		$this->Output($filePath,'F');
		
		$fileStream = file_get_contents($filePath);
		$fileArr = unpack("H*hex", $fileStream);
		$fileHex = "0x".$fileArr['hex']; 
		
		
		while($personsRow = mssql_fetch_array($persons)) {
			$guid = $this->gen_uuid();
			$sql = "
				INSERT INTO util_blob (blob,guid,date_created,date_modified,created_by,modified_by,file_ext,mime_type,original_file_name,title,description,document_type_id)
						VALUES ($fileHex,'".$guid."','".date('Y-m-d H:i:s')."','".date('Y-m-d H:i:s')."','Contribution Statement Generator','Contribution Statement Generator','pdf','application/pdf','".$filePath."','".$filename."','$year Contribution Statement',7);";
			mssql_query($sql,$link);
			$sql = "SELECT blob_id FROM util_blob WHERE guid = '$guid'";
			$result = mssql_query($sql,$link);
			$blobobj = mssql_fetch_object($result);

			$sql = '
				INSERT INTO core_person_document (person_id, blob_id)
					VALUES ('.$personsRow['person_id'].','.$blobobj->blob_id.');';
			mssql_query($sql,$link);
			$sql = '
				INSERT INTO core_person_attribute (person_id, attribute_id, int_value, date_created, date_modified, created_by, modified_by)
					VALUES ('.$personsRow['person_id'].','.$attID.','.$blobobj->blob_id.',\''.date('Y-m-d H:i:s').'\',\''.date('Y-m-d H:i:s').'\',\'Contribution Statement Generator\',\'Contribution Statement Generator\');';
			mssql_query($sql,$link);
		}
			
		//$sql = 'SELECT * FROM util_blob WHERE blob_id = 65987';
		//$result = mssql_query($sql,$link);
		//print_r(odbc_fetch_object($result));
		mssql_free_result($result);
		
	}
	public function gen_uuid() {
    	return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			// 32 bits for "time_low"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
	
			// 16 bits for "time_mid"
			mt_rand( 0, 0xffff ),
	
			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand( 0, 0x0fff ) | 0x4000,
	
			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand( 0, 0x3fff ) | 0x8000,
	
			// 48 bits for "node"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	}
	public function createStatement($givingUnitId,$year,$action,$attID) {
		if (!isset($year)){
			$year = date('Y');	
		}
		$this->$year=$year;
		$this->setMeta();
		$this->AddPage();
		$last_name = $this->setAddressFromDB($givingUnitId);
		$this->createDetailSection($givingUnitId,$year);
		
		$link = mssql_connect($this->DBServer, 'ArenaRO', 'arena');
		$sql = '
			SELECT cp.last_name, cp.person_id 
			FROM core_person cp
				INNER JOIN core_family_member cfm ON cp.person_id = cfm.person_id
			WHERE cp.giving_unit_id = \''.$givingUnitId.'\' 
				AND cfm.role_luid = 29;';
		
		$persons = mssql_query($sql,$link);
		$arr = mssql_fetch_assoc($persons);
		mssql_free_result($persons);
		$lastName = $arr['last_name'];
		
		$lastName = str_replace('-','',$lastName);
		
		$filename = $lastName .'-'.$givingUnitId.'-'.$year;
		
		$filename = str_replace('/','',$filename);
		$filename = str_replace(' ','',$filename);
		$filename = str_replace('\\','',$filename);
		$filename = str_replace('&','',$filename);
		$filename = str_replace('\'','',$filename);
		$filename = str_replace('"','',$filename);
		$filename = str_replace(',','',$filename);
		$filename = str_replace('.','',$filename);
		$filename = str_replace('*','',$filename);
		
		
		if($action=="D"){
			$this->Output($filename.'.pdf', $action);
		} else {
			$this->addtoDB($givingUnitId,'C:\windows\temp\cstatements\\'.$filename.'.pdf',$year,$attID);
		}
	}
	public function cleanUpDB($givingUnitID,$year,$attID){
		$link = mssql_connect($this->DBServer, 'ArenaRW', 'write');
		if (!$link) {
    		die('Something went wrong while connecting to '.$this->DBServer);
		}
		
		$sql = "
			SELECT cp.last_name, cp.person_id 
			FROM core_person cp
				INNER JOIN core_family_member cfm ON cp.person_id = cfm.person_id
			WHERE cp.giving_unit_id = '$givingUnitID' 
				AND cfm.role_luid = 29;";
		
		$persons = mssql_query($sql,$link);
		
		while($personsRow = mssql_fetch_array($persons)) {
			$sql = "
				SELECT int_value
				FROM core_person_attribute
				WHERE attribute_id =".$attID." 
					AND person_id IN (
						SELECT cp.person_id 
							FROM core_person cp
								INNER JOIN core_family_member cfm ON cp.person_id = cfm.person_id
							WHERE cp.giving_unit_id = '$givingUnitID' 
								AND cfm.role_luid = 29
					)";
			$docIDs = mssql_query($sql,$link);
			
			while($doc = mssql_fetch_array($docIDs)) {
				$sql = "
						DELETE
						FROM core_person_document
						WHERE blob_id = ". $doc['int_value'];
				mssql_query($sql,$link);
				$sql = "
						DELETE
						FROM util_blob
						WHERE blob_id = ". $doc['int_value'];
				mssql_query($sql,$link);
			}
			
		}
		$sql = "
				DELETE
				FROM core_person_attribute
				WHERE attribute_id = ".$attID." 
					AND person_id IN (
						SELECT cp.person_id 
						FROM core_person cp
							INNER JOIN core_family_member cfm ON cp.person_id = cfm.person_id
						WHERE cp.giving_unit_id = '$givingUnitID' 
							AND cfm.role_luid = 29
					)";
		mssql_query($sql,$link);
			
	}
}
?>