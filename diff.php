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
//$aMatrix = array();
//
//foreach ($dataOld as $data){
//
//    $sName = $data[1] . $data[2];
//
//    $aMatrix[$sName] = $data;
//}


// Load Real
logs('Loading New...');
array_push($header, 'Diff');
$aRes = array($header);

foreach ($dataNew as $data){
//    $sName = $data[1] . $data[2];

//    if (!isset($cloneaMatrix[$sName])){
//        array_push($data, 'THUA');
//    }else{
//        $old = $cloneaMatrix[$sName];
//        unset($aMatrix[$sName]);
//
//        for ($i = 2; $i < count($data); $i++){
//            $data[$i] = trim($data[$i]);
//            $old[$i] = trim($old[$i]);
//
//            $val = 'DUNG';
//
//            if ($data[$i]=='' && $old[$i]!=''){
//                $val = 'THIEU';
//            }
//
//            if ($data[$i]!='' && $old[$i]==''){
//                $val = 'THUA';
//            }
//
//            if($val != 'DUNG') {
//                break;
//            }
//        }
//
//        array_push($data, $val);
//    }

    $diff = 'THUA';

    foreach ($dataOld as $k => $oldData){
        $val = true;

        for($i = 1; $i < count($data); $i++){
            $data[$i] = trim($data[$i]);
            $compare = trim($oldData[$i]);

            if ($data[$i] == $compare){
                continue;
            } else {
                $val = false;
                break;
            }
        }

        if($val == true){
            $diff = 'DUNG';

            unset($dataOld[$k]);
            break;
        } else {
            continue;
        }
    }


    array_push($data, $diff);

    $aRes[] = $data;
}

while (count($dataOld) > 0){
    $data = array_shift($dataOld);
    array_push($data, 'THIEU');
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
