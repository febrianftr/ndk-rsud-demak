<?php
require 'koneksi/koneksi.php';
require 'log.php';
require __DIR__ . '/radiographer/vendor/autoload.php';

use GuzzleHttp\Client;

$aet = $_POST['aet'];
$uid = $_POST['uid'];
$acc = $_POST['acc'];
$pat_id = $_POST['pat_id'];
$pk_study = $_POST['pk_study'];
$validation = $_POST['validation'];

function prosesApiHtml()
{
    // KETIKA PROSES PENGIRIMAN KE AET DENGAN HTML

    global $conn, $aet, $uid, $acc;

    $inputcd = 'cd C:\dcmsyst\tool-2.0.26\bin &&';
    $inputdcm = ' dcmqr dcmPACS@127.0.0.1:11118 -qStudyInstanceUID=' . $uid . ' -cmove ' . $aet;
    $input = $inputcd . $inputdcm;
    exec($input, $output);

    $input_insert = mysqli_real_escape_string($conn, $input);
    $output_insert = json_encode($output, true);

    mysqli_query(
        $conn,
        "INSERT INTO dicom_router (uid, acc, request, response, created_at, updated_at) 
    VALUES ('$uid', '$acc', '$input_insert', '$output_insert', NOW(), NOW())"
    );

    $message = implode(PHP_EOL, $output);

    logging(PHP_EOL . PHP_EOL . "['uid' : $uid, 'aet' : $aet]" . PHP_EOL . $message, "log/send-dicom.log");

    echo json_encode([
        'status' => 200,
        'output' => implode('<br />', $output),
        'input' => "" // sengaja dikosongin
    ]);
    http_response_code(200);
}

function validationAcc()
{
    global $conn, $aet, $uid, $acc, $pat_id;

    $conn_pacsdb = mysqli_connect("localhost", "root", "efotoadmin", "pacsdb");
    $rowStudyPacsDb = mysqli_fetch_assoc(mysqli_query($conn_pacsdb, "SELECT accession_no, rejection_state FROM study WHERE study_iuid = '$uid'"));
    $accessionNoPacsDb = $rowStudyPacsDb['accession_no'];
    $rejection_state = $rowStudyPacsDb['rejection_state'];
    // CATATAN REJECTION STATE 0 (BELUM DIHAPUS), REJECTION STATE 1 (SUDAH DIHAPUS TETAPI DIRESTORE) REJECTION STATE 2 (SUDAH DIHAPUS)
    if ($accessionNoPacsDb != $acc && ($rejection_state == 0 || $rejection_state == 1)) {
        // KETIKA ACC NUMBER YANG ADA DI PACSDB TIDAK SAMA DENGAN ACC NUMBER YANG ADA DI PACSIO DAN TIDAK DIHAPUS/TIDAK REJECTION.
        echo json_encode([
            'status' => 422,
            'output' => 'konfirmasi accession_number ' . $acc . '. apakah ingin diupdate ?',
            'acc' => $acc
        ]);
        http_response_code(422);
    } else if ($accessionNoPacsDb != $acc && $rejection_state == 2) {
        // KETIKA ACC NUMBER YANG ADA DI PACSDB TIDAK SAMA DENGAN ACC NUMBER YANG ADA DI PACSIO DAN DATA DIHAPUS/DATA REJECTION.
        echo json_encode([
            'status' => 500,
            'output' => 'viewer mobile tidak ada, hubungi tim IT untuk restore/hapus reject gambar dcm****-arc',
            'acc' => $acc
        ]);
        http_response_code(500);
    } else {
        // JIKA TIDAK ADA APERUBAHAN ACC NUMBER MAKA KIRIM KE AET
        prosesApiHtml();
    }
}

function prosesApiOhif()
{
    global $conn, $uid, $aet, $acc, $pat_id, $pk_study;
    try {
        // API OHIF UPDATE ACC NUMBER 
        $clientOhif = new Client([
            'base_uri' => "http://$_SERVER[SERVER_ADDR]:9090",
        ]);

        $responseOhif = $clientOhif->request('POST', "/dcm4chee-arc/aets/DCM4CHEE/rs/studies", [
            'headers' => [
                'Accept' => 'Application/json',
                'Content-Type' => 'Application/json'
            ],
            'json' => [
                [
                    "0020000D" => [
                        "vr" => "UI",
                        "Value" => ["$uid"]
                    ],
                    "00100020" => [
                        "vr" => "LO",
                        "Value" => ["$pat_id"]
                    ],
                    "00080050" => [
                        "vr" => "SH",
                        "Value" => ["$acc"]
                    ],
                ]
            ],
            'http_errors' => false
        ]);
        $bodyOhif = $responseOhif->getBody();
        $dataOhif = json_decode($bodyOhif, true);
        $code = $responseOhif->getStatusCode(); //200

        // JIKA STATUS 200 MAKA :
        if ($code == 200) {

            // API OHIF SEND STUDY KE HTML
            $input = "/dcm4chee-arc/aets/DCM4CHEE/dimse/DCM4CHEE/studies/$uid/export/dicom:DCMROUTER";
            $responseOhif = $clientOhif->request('POST', $input, [
                'headers' => [
                    'Accept' => 'Application/json',
                    'Content-Type' => 'Application/json'
                ],
                'http_errors' => false
            ]);
            $bodyOhif = $responseOhif->getBody();
            $dataOhif = json_decode($bodyOhif, true);
            // $code = $responseOhif->getStatusCode(); //200

            mysqli_query(
                $conn,
                "INSERT INTO dicom_router (uid, acc, request, response, created_at, updated_at) 
                VALUES ('$uid', '$acc', '$input', '$bodyOhif', NOW(), NOW())"
            );

            logging(PHP_EOL . PHP_EOL . "['uid' : $uid, 'aet' : $aet]" . PHP_EOL . $bodyOhif, "log/send-dicom.log");

            echo json_encode([
                'status' => 200,
                'output' => $bodyOhif,
                'input' => "" // sengaja dikosongin
            ]);
            http_response_code(200);
        } else {
            // JIKA ACC NUMBER ATAU STUDY IUID TIDAK ADA
            echo json_encode([
                'status' => $code,
                'output' => $dataOhif['errorMessage'],
            ]);
            http_response_code($code);
        }
    } catch (GuzzleHttp\Exception\GuzzleException $th) {
        $message = $th->getMessage();
        echo json_encode([
            'status' => 500,
            'output' => 'Gagal, tidak konek ke API',
        ]);
        http_response_code(500);
    }
}

// ketika AET DCMROUTER SATUSEHAT 
if ($aet == 'DCMROUTER') {
    // ketika diawal klik, validasi. 
    if ($validation == 'true') {
        validationAcc();
    } else {
        // konfirmasi klik OK lanjut ke kirim menggunakan API OHIF
        prosesApiOhif();
    }
} else {
    // ketika bukan AET DCMROUTER kirim menggunakan API HTML
    prosesApiHtml();
}
