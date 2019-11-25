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
        header('Location: diff.php?error=1');
    }

    header('Location: diff.php');
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

$dataOld = $aTPB[1];
$dataNew = $aTPB[2];

$header = array_shift($dataOld);
//array_shift($header);
array_shift($dataNew);

// Load matrix
logs('Loading Old...');
$aMatrix = array();

foreach ($dataOld as $data){
    //unset($data[0]);
    $sName = $data[2] . $data[3];
    $aMatrix[$sName] = $data;
}


// Load Real
logs('Loading New...');
array_push($header, 'Diff');
$aRes = array($header);
foreach ($dataNew as $data){
    //unset($data[0]);
    $sName = $data[2] . $data[3];

//	if ($sName == 'CV_HTTD_AQueries/Customer Accounts/BalancesACDABLQY'){
//		var_dump($data);
//		var_dump($aMatrix[$sName]);
//	}

    if (!isset($aMatrix[$sName])){
        array_push($data, 'N/A');
    }else{
        $diff = false;
        $old = $aMatrix[$sName];
        unset($aMatrix[$sName]);

        $data[4] = (trim($data[4])==trim($old[4]))?'DUNG':'SAI';

        for ($i = 5; $i<=20; $i++){
            $data[$i] = trim($data[$i]);
            $old[$i] = trim($old[$i]);

            $val = '';

            if ($data[$i]=='' && $old[$i]=='x'){
                $val = 'THIEU';
            }

            if ($data[$i]=='x' && $old[$i]==''){
                $val = 'THUA';
            }

            if ($data[$i]=='x' && $old[$i]=='x'){
                $val = 'DUNG';
            }

//			if ($sName == 'CV_HTTD_AQueries/Customer Accounts/BalancesACDABLQY') {
//				printf("%s-%s-%s\r\n", $data[$i], $data[$i], $val);
//			}

            $diff |= ($data[$i]<>$old[$i]);

            $data[$i] = $val;
        }

        array_push($data, $diff?'SAI':'DUNG');
//		if ($sName == 'CV_HTTD_AQueries/Customer Accounts/BalancesACDABLQY') {
//			var_dump($data);
//			exit();
//		}
    }

    $aRes[] = $data;
}

while (count($aMatrix)>0){
    $data = array_shift($aMatrix);
    array_push($data, 'N/A2');
    $aRes[] = $data;
}

// Writing result
logs('Writing result');

$resFile = 'tmp/diff_result_'.time().'.xlsx';
$writer = WriterFactory::create(Type::XLSX);
$writer->openToFile($resFile);
$writer->addRows($aRes);
$writer->close();

logs('done!');
logs('</pre>');

logs('<script>window.location = "'.$resFile.'";</script>');
