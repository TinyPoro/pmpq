<?php
/**
 * Created by PhpStorm.
 * User: Dz
 * Date: 5/17/16
 * Time: 11:15 AM
 */

set_time_limit(0);
ini_set("display_errors", 1);
error_reporting(E_ALL);

require_once 'func.php';
require_once 'includes/Spout/Autoloader/autoload.php';

use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;

setlocale(LC_ALL, 'vi_VN');
date_default_timezone_set('America/Los_Angeles');


$uploadDir = 'tmp';
$uploadFile = $uploadDir . '/data.xlsx';

$task = isset($_REQUEST['task'])?$_REQUEST['task']:'';

if ($task == 'upload'){
	if (!move_uploaded_file($_FILES['inpFile']['tmp_name'], $uploadFile)) {
		header('Location: rolefcc.php?error=1');
	}

	header('Location: rolefcc_step2.php');
}

logs('<pre>');

// Load Matrix

logs('Loading data...');
$aTPB = array();
$reader = ReaderFactory::create(Type::XLSX);
$reader->open($uploadFile);

foreach ($reader->getSheetIterator() as $id => $sheet) {
	foreach ($sheet->getRowIterator() as $j => $row) {
		$row = array_slice($row, 0, 30);

		$aTPB[$id][] = $row;
	}
}

$dataMatrix = $aTPB[1];
$dataReal = $aTPB[2];

if (isset($dataMatrix[0])) unset($dataMatrix[0]);
if (isset($dataMatrix[1])) unset($dataMatrix[1]);
if (isset($dataMatrix[2])) unset($dataMatrix[2]);
if (isset($dataMatrix[3])) unset($dataMatrix[3]);

// Load matrix
logs('Loading matrix...');
$aMatrix = array();

$test1 = $test2 = "";

$roleName = "";
foreach ($dataMatrix as $data){
	if (!empty($data[2])){
		$aRole = array_slice($data, 4, 16);
		foreach ($aRole as $id => $role){
			$aRole[$id] = $role==""?0:1;
		}
		$aMatrix[$roleName."|".$data[2]] = $aRole;
	}else{
		$roleName = $data[1];
	}
}


// Load users
logs('Loading Real...');
$aReal = array();
array_shift($dataReal);
foreach ($dataReal as $data){
	$roleName = $data[1] . "|" . $data[2];
	$aRole = array_slice($data, 6, 16);
	$aReal[$roleName] = $aRole;
}

// Calculate Output
$aRes = array();
$aRes[] = array("ROLE ID", "ROLE FUNCTION", "STATUS", "NEW", "OPEN", "DEL", "CLOSE", "UNLOCK", "REOPEN", "PRINT", "AUTH", "REVERSE", "ROLOVER", "CONFIRM", "LIQUIDATE", "HOLD", "TEMPLATE", "VIEW", "GENERATE");
foreach ($aReal as $role => $data){
	if (isset($aMatrix[$role])){
		$matrix = $aMatrix[$role];
		$status = "TRUE";

		$res = explode("|", $role);
		$res[2] = $status;

		foreach($data as $id=>$val){
			$valRes = "-";
			if ($val==1 && $matrix[$id]==0){
				$status = "FALSE";
				$valRes = 1;
			}
			if ($val==0 && $matrix[$id]==1){
				$status = "FALSE";
				$valRes = 2;
			}
			$res[$id+3] = $valRes;
		}

		$res[2] = $status;

		$aRes[] = $res;
	}else{
		$aRes[$role] = array($role);
		logs("$role not found!");
	}
}

// Writing result
logs('Writing result');

$resFile = 'tmp/rolefcc_result_'.time().'.xlsx';
$writer = WriterFactory::create(Type::XLSX);
$writer->openToFile($resFile);
$writer->addRows($aRes);
$writer->close();

logs('done!');
logs('</pre>');

logs('<script>window.location = "'.$resFile.'";</script>');
