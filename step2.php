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
		header('Location: index.php?error=1');
	}

	header('Location: step2.php');
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
$dataUser = $aTPB[2];
$dataDomain = $aTPB[3];

unset($aTPB);

// Load matrix
logs('Loading matrix...');
$aMatrix = array();
array_shift($dataMatrix);

$test1 = $test2 = "";

foreach ($dataMatrix as $data){
	$aUserRole = array(
		empty($data[0])?0:$data[0],
		empty($data[1])?0:$data[1],
		empty($data[2])?0:$data[2],
		empty($data[4])?0:$data[4]
	);
	$role = strtoupper(vn_str_filter(implode('', $aUserRole)));

	if (empty($role)) continue;

	$i = 5;

	while(!empty($data[$i])){
        $aMatrix[$role][strtoupper($data[$i])] = 1;

        $i++;
    }

//	if (!empty($data[5]))
//	if (!empty($data[6])) $aMatrix[$role][strtoupper($data[6])] = 1;
//	if (!empty($data[7])) $aMatrix[$role][strtoupper($data[7])] = 1;
//	if (!empty($data[8])) $aMatrix[$role][strtoupper($data[8])] = 1;
//	if (!empty($data[9])) $aMatrix[$role][strtoupper($data[9])] = 1;
}


// Load users
logs('Loading users...');
$test = array_shift($dataUser);
$aUser = $aUserNotFound = array();

foreach ($dataUser as $data) {
	if (empty($data[0])) continue;

	$user = strtoupper(array_shift($data));
	foreach ($data as $i => $d) {
		if ($i>3) break;
		$d = empty($d)?0:$d;
		$aUser[$user][] = $d;
	}
}

// Load domains

$aReal = array();
array_shift($dataDomain);

foreach ($dataDomain as $data){
	if ($data[0] == null) continue;

	$user = trim(array_shift($data));
	$roleId = array_shift($data);

	$user = mb_strtoupper($user);

	if (empty($roleId)){
		if (!isset($aReal[$user])){
			$aReal[$user]['real'] = array();
		}
		continue;
	}
	$aReal[$user]['real'][] = mb_strtoupper($roleId);
}


// Map
$aMap = array();
$aCat = array(array(), array(), array(), array(), array(), array(), array());
foreach ($aReal as $user => $real) {
	if (isset($aUser[$user])){
		$role = implode("", $aUser[$user]);
		$role = strtoupper(vn_str_filter($role));

		$aReal[$user]['matrix'] = isset($aMatrix[$role])?array_keys($aMatrix[$role]):array();

		if (sizeof($aReal[$user]['matrix'])==0 && sizeof($aReal[$user]['real'])==0){
			$aCat[6][] = $user;
			$aReal[$user]['cat'] = 6;
			continue;
		}

		$aReal[$user]['diff1'] = array_diff($aReal[$user]['matrix'], $aReal[$user]['real']);
		$aReal[$user]['diff2'] = array_diff($aReal[$user]['real'], $aReal[$user]['matrix']);

		if (sizeof($aReal[$user]['diff1'])==0 && sizeof($aReal[$user]['diff2'])==0){
			$aCat[0][] = $user;
			$aReal[$user]['cat'] = 0;
		}

		if (sizeof($aReal[$user]['diff1'])==0 && sizeof($aReal[$user]['diff2'])>0){
			$aCat[1][] = $user;
			$aReal[$user]['cat'] = 1;
		}

		if (sizeof($aReal[$user]['diff1'])>0 && sizeof($aReal[$user]['diff2'])==0){
			$aCat[2][] = $user;
			$aReal[$user]['cat'] = 2;
		}

		if (sizeof($aReal[$user]['diff1'])>0 && sizeof($aReal[$user]['diff2'])>0){
			if (sizeof($aReal[$user]['diff1'])==sizeof($aReal[$user]['matrix'])){
				$aCat[3][] = $user;
				$aReal[$user]['cat'] = 3;
			}else{
				$aCat[4][] = $user;
				$aReal[$user]['cat'] = 4;
			}
		}
	}else{
		if (!isset($aUserNotFound[$user])){
			$aUserNotFound[$user] = 1;
			$aCat[5][] = $user;
			$aReal[$user]['cat'] = 5;
		}
	}
}

/*
foreach ($aUser as $user=>$val){
	if (!isset($aReal[$user])){
		$role = implode("", $val);
		$role = strtoupper(vn_str_filter($role));
		$matrix = isset($aMatrix[$role])?array_keys($aMatrix[$role]):array();

		if (sizeof($matrix)==0){
			$aCat[6][] = $user;
		}
	}
}
*/

// Writing result
logs('Writing result');
$maxRow = max(sizeof($aCat[0]), sizeof($aCat[1]), sizeof($aCat[2]), sizeof($aCat[3]), sizeof($aCat[4]), sizeof($aCat[5]), sizeof($aCat[6]));
$aRes = array();
$aRes[] = array(
	'NHÓM 0 - đúng quyền ('.sizeof($aCat[0]).' người)',
	'NHÓM 1 - thừa quyền ('.sizeof($aCat[1]).' người)',
	'NHÓM 2 - thiếu quyền ('.sizeof($aCat[2]).' người)',
	'NHÓM 3 - sai quyền hoàn toàn ('.sizeof($aCat[3]).' người)',
	'NHÓM 4 - vừa làm thiếu vừa làm thừa quyển ('.sizeof($aCat[4]).' người)',
	'NHÓM 5 - không tồn tại trên hệ thống  ('.sizeof($aCat[5]).' người))',
	'NHÓM 6 - không có quyền ('.sizeof($aCat[6]).' người))'
);
for ($i=0; $i<$maxRow; $i++){
	$aRes[] = array(
		isset($aCat[0][$i])?$aCat[0][$i]:"",
		isset($aCat[1][$i])?$aCat[1][$i]:"",
		isset($aCat[2][$i])?$aCat[2][$i]:"",
		isset($aCat[3][$i])?$aCat[3][$i]:"",
		isset($aCat[4][$i])?$aCat[4][$i]:"",
		isset($aCat[5][$i])?$aCat[5][$i]:"",
		isset($aCat[6][$i])?$aCat[6][$i]:""
	);
}

$resFile = 'tmp/result_'.time().'.xlsx';
$writer = WriterFactory::create(Type::XLSX);
$writer->openToFile($resFile);
$writer->addRows($aRes);

// Writing Categorize
$writer->addNewSheetAndMakeItCurrent();
$aRes = array();
$aRes[] = array(
	'DOMAIN',
	'MATRIX','','','','',
	'REAL','','','','','','','','',
	'KHỚP LỆCH 1','','','','','','','','',
	'KHỚP LỆCH 2','','','','','','','','',
	'PHÂN LOẠI'
);

foreach ($aReal as $user=>$data){
	$res = array($user);
	for ($i=0; $i<5; $i++){
		$res[] = isset($data['matrix'][$i])?$data['matrix'][$i]:"";
	}
	for ($i=0; $i<9; $i++){
		$res[] = isset($data['real'][$i])?$data['real'][$i]:"";
	}
	for ($i=0; $i<9; $i++){
		$res[] = isset($data['diff2'][$i])?$data['diff2'][$i]:"";
	}
	for ($i=0; $i<9; $i++){
		$res[] = isset($data['diff1'][$i])?$data['diff1'][$i]:"";
	}
	$res[] = $data['cat'];

	$aRes[] = $res;
}
$writer->addRows($aRes);
$writer->close();

logs('done!');
logs('</pre>');

logs('<script>window.location = "'.$resFile.'";</script>');
