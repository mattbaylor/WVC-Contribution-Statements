<?php 
setlocale(LC_MONETARY, 'en_US');
//ini_set('log_errors','1');
//ini_set('error_log','/tmp/php_errors.log');
ini_set('display_errors','1');
ini_set('max_execution_time', 3600);

	
$link = mssql_connect('PSQL01RR\PRODUCTION', 'ArenaRO', 'arena');
if (!$link) {
	die('Something went wrong while connecting to PSQL01RR\PRODUCTION');
}
	
if(!isset($_POST['year'])){
	$year = date('Y');
} else {
	$year = $_POST['year'];
}

$attSQL = "SELECT oos.*
			FROM orgn_organization_setting oos
			WHERE oos.[Key] = 'wvc.ContribStatementAttributes';";

$result = mssql_query($attSQL,$link);
$attArr = mssql_fetch_assoc($result);
$attObj = json_decode($attArr['Value']);
$attID = $attObj->$year;

if(isset($_POST['action'])){
	$action = $_POST['action'];	
} else {
	$action = "D";
}

if(isset($_POST['runNum']) && strlen($_POST['runNum']) > 0){
	$runNumStart = ($_POST['runNum']) * 300 - 300;
	$runNumEnd = ($_POST['runNum']) * 300 - 1;
	$postSql = ") AS dtab1
		) AS dtab
		WHERE dtab.RowNum BETWEEN $runNumStart AND $runNumEnd";
} else {
	$postSql = ') AS dtab1 ) AS dtab';
}

if(isset($_POST['person_id']) && strlen($_POST['person_id']) > 0) {
	$where = 'WHERE cc.contribution_date >= \'1/1/'.$year.' 00:00:00\' 
		AND cc.contribution_date <= \'12/31/'.$year.' 23:59:59\' 
		AND ccf.contribution_fund_id IN (
			SELECT ccf.contribution_fund_id
			FROM ctrb_contribution_fund ccf
				INNER JOIN ctrb_fund cf ON ccf.fund_id = cf.fund_id
			WHERE cf.tax_deductible <> 0
			)
		AND cp.person_id NOT IN (
			SELECT cp.person_id
			FROM core_person cp
				INNER JOIN core_family_member cfm ON cp.person_id = cfm.person_id
			WHERE cfm.role_luid <> 29
			)
		AND cp.person_id = ' . $_POST['person_id'];
} else {
	$where = 'WHERE cc.contribution_date >= \'1/1/'.$year.' 00:00:00\' 
		AND cc.contribution_date <= \'12/31/'.$year.' 23:59:59\' 
		AND ccf.contribution_fund_id IN (
			SELECT ccf.contribution_fund_id
			FROM ctrb_contribution_fund ccf
				INNER JOIN ctrb_fund cf ON ccf.fund_id = cf.fund_id
			WHERE cf.tax_deductible <> 0
			)
		AND cp.person_id NOT IN (
			SELECT cp.person_id
			FROM core_person cp
				INNER JOIN core_family_member cfm ON cp.person_id = cfm.person_id
			WHERE cfm.role_luid <> 29
			)';
}

if($action == "D"){
$__headers = array(
		'Content-Type' => 'application/pdf', 
	);	
}



$sql = 'SELECT giving_unit_id
FROM (
	SELECT giving_unit_id, ROW_NUMBER() OVER (ORDER BY giving_unit_id) AS RowNum
	FROM (SELECT DISTINCT cp.giving_unit_id
FROM ctrb_contribution cc
	INNER JOIN ctrb_contribution_fund ccf ON cc.contribution_id = ccf.contribution_id
	INNER JOIN core_person cp ON cc.person_id = cp.person_id ' . $where . ' ' . $postSql;

if(isset($_POST['debug'])){
	if($_POST['debug'] == 'on'){	
		die($sql.'<br>'.$attID);
	}
}
	
$result = mssql_query($sql,$link);

$total = mssql_num_rows($result);

mssql_data_seek($result,0);


	$counter = 0;

	$test_array = array();

	while($id = mssql_fetch_array($result)) {
		
		
		$pdf = new Contrib_Statement(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->createStatement($id['giving_unit_id'],$year,$action,$attID);
		$pdf = '';
		$counter += 1;
		
	}


echo("$total rows selected, $counter rows processed<br/ >");
if(isset($runNumStart)){
	echo("Processed records from $runNumStart to $runNumEnd. If total rows processed is less than 299 then you're probably done");
}



?>

