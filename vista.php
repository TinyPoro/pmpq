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
		header('Location: vista.php?error=1');
	}

	header('Location: vista.php');
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

// Load matrix
logs('Loading matrix...');
$aRole = array(
	'HTTD_HO_NV',
	'HTTD_HO_KSV',
	'HTTD_HO_VIEW',
	'THE_TT_NV',
	'THE_TT_KSV',
	'THE_PH_NV',
	'THE_PH_KSV',
	'THE_QLRR',
	'THE_ATMPOS_NV',
	'THE_ATMPOS_KSV',
	'THE_GD',
	'IT_USER_INPUT',
	'IT_USER_AUTH',
	'IT_SUPPORT',
	'IT_EOD',
	'IT_OPERATION_INPUT',
	'IT_OPERATION_AUTH',
	'PTSP_THE_VIEW',
	'HOTLINE_NV',
	'HOTLINE_KSV',
	'HOTLINE_VIEW',
	'DVKH_KSV',
	'KTNB_QTRR_VIEW',
	'COLL_THUNO_QUAHAN'
);
$aMatrix = array();

$test1 = $test2 = "";

$roleName = "";
foreach ($dataMatrix as $data){
	$sName = isset($data[0])?$data[0]:'';
	if (!empty($sName)){
		for ($i=6; $i<=29; $i++)
		if (isset($data[$i])){
			$val = trim($data[$i]);
			if ($val=='x'){
				if (!isset($aMatrix[$sName])){
					$aMatrix[$sName] = array();
				}
				$aMatrix[$sName][$aRole[$i-6]] = 1;
			}
		}
	}
}


// Load Real
logs('Loading Real...');
$aReal = array(
	array_shift($dataReal)
);
foreach ($dataReal as $data){
	$roleName = trim($data[1]);

	$sName = isset($data[2])?trim($data[2]):"";
	$bStatus = isset($aMatrix[$sName][$roleName]);

	$aReal[] = array(
		$data[0],
		$roleName,
		$sName,
		$data[3],
		$bStatus
	);
}

// Writing result
logs('Writing result');

$resFile = 'tmp/vista_result_'.time().'.xlsx';
$writer = WriterFactory::create(Type::XLSX);
$writer->openToFile($resFile);
$writer->addRows($aReal);
$writer->close();

logs('done!');
logs('</pre>');

logs('<script>window.location = "'.$resFile.'";</script>');
