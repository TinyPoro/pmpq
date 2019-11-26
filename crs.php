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
    exec("rm tmp/*.xlsx");

	if (!move_uploaded_file($_FILES['inpFile']['tmp_name'], $uploadFile)) {
		header('Location: crs.php?error=1');
	}

	header('Location: crs.php');
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
		$aMatrix[$roleName][] = $data[2];
	}else{
		$roleName = $data[1];
		$aMatrix[$roleName] = array();
	}
}


// Load users
logs('Loading Real...');
$aReal = array();
array_shift($dataReal);
foreach ($dataReal as $data){
	$roleName = $data[1];
	if (!isset($aReal[$roleName])){
		$aReal[$roleName] = array();
	}
	if (!empty($data[2])){
		$aReal[$roleName][] = $data[2];
	}
}

// Calculate Output
$aRes = array();

foreach ($aMatrix as $roleName=>$aRole){
	if (isset($aReal[$roleName])){
		$aDiff1 = array_diff($aRole, $aReal[$roleName]);
		$aDiff2 = array_diff($aReal[$roleName], $aRole);
		$aRes[] = array_merge(array($roleName, 'THIEU'), $aDiff1);
		$aRes[] = array_merge(array($roleName, 'THUA'), $aDiff2);
	}
}

// Writing result
logs('Writing result');

$resFile = 'tmp/crs_result_'.time().'.xlsx';
$writer = WriterFactory::create(Type::XLSX);
$writer->openToFile($resFile);
$writer->addRows($aRes);
$writer->close();

logs('done!');
logs('</pre>');

logs('<script>window.location = "'.$resFile.'";</script>');
