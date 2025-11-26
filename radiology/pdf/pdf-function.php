<?php
require('fpdf.php');
require('hex.php');
require('html-parser.php');

use GuzzleHttp\Client;

function pdfPage()
{
    // intance object dan memberikan pengaturan halaman PDF
    $pdf = new PDF('P', 'mm', 'A4');

    // membuat halaman baru
    $pdf->SetMargins(15, 23, 15);
    $pdf->SetAutoPageBreak(true, 40);
    $pdf->AddPage();
    // setting jenis font yang akan digunakan
    $pdf->SetFont('Arial', '', 10);
    // mencetak string 

    $pdf->SetTitle('Hasil expertise');

    return $pdf;
}

function pdfProsesExpertise($uid, $pdf)
{
    global $conn, $conn_pacsio, $select_patient, $select_study, $select_order, $select_workload, $select_dokter_radiology, $table_dokter_radiology, $table_patient, $table_study, $table_order, $table_workload;

    $stmt = mysqli_prepare(
        $conn_pacsio,
        "SELECT 
        $select_patient,
        $select_study,
        $select_order,
        $select_workload
        FROM $table_patient
        JOIN $table_study
        ON patient.pk = study.patient_fk
        LEFT JOIN $table_order
        ON xray_order.uid = study.study_iuid
        LEFT JOIN $table_workload
        ON study.study_iuid = xray_workload.uid
        WHERE study.study_iuid = ?"
    );

    mysqli_stmt_bind_param($stmt, "s", $uid);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    $pat_name = substr(removeCharacter(ucwords(strtolower(defaultValue($row['pat_name'])))), 0, 23);
    $pat_sex = $row['pat_sex'];
    $pat_birthdate = $row['pat_birthdate'];
    $age = diffDate($pat_birthdate);
    $study_datetime = defaultValueDateTime($row['study_datetime']);
    $updated_time = defaultValueDateTime($row['updated_time']);
    $study_desc_one = substr(ucwords(strtolower(defaultValue($row['prosedur']))), 0, 32);
    $study_desc_two = substr(ucwords(strtolower(defaultValue($row['prosedur']))), 32, 32);
    $pat_id = defaultValue($row['pat_id']);
    $no_foto = defaultValue($row['no_foto']);
    $address = ucwords(strtolower(defaultValue($row['address'])));
    $name_dep = substr(defaultValue($row['name_dep']), 0, 29);
    $named = substr(defaultValue($row['named']), 0, 29);

    if (!strpos($row['spc_needs'], "~~")) {
        $klinis = str_replace("~~", "", $row['spc_needs']);
    } else {
        $spc_needs_array = explode("~~", $row['spc_needs']);
        $klinis = !empty($spc_needs_array[1]) ? $spc_needs_array[1] : "-";
    }

    $spc_needs_one = ucfirst(substr(defaultValue($klinis), 0, 40));
    $spc_needs_two = substr(defaultValue($klinis), 40, 40);
    $fill = $row['fill'];
    $signature = $row['signature'];
    $status = $row['status'];
    $create_time = $row['create_time'];
    $approved_at = $row['approved_at'];
    $pk_dokter_radiology = $row['pk_dokter_radiology'];
    $study_datetime = $row['study_datetime'];

    // kondisi mencari ditabel dokter radiology
    $row_dokrad = mysqli_fetch_assoc(mysqli_query(
        $conn,
        "SELECT $select_dokter_radiology 
        FROM $table_dokter_radiology 
        WHERE pk = '$pk_dokter_radiology'"
    ));

    $dokradid = defaultValue($row_dokrad['dokradid']);
    $dokrad_name = defaultValue($row_dokrad['dokrad_fullname']);
    $nip = $row_dokrad['nip'];
    $link_dokrad_img = "http://" . $_SERVER['SERVER_NAME'] . ":8000/storage/" . $row_dokrad['dokrad_img'];
    // kondisi ketika dokrad_img null & ketika server laravel error
    $dokrad_img =  $row_dokrad['dokrad_img'] == null ? 'scan-ttd-default.PNG' : (@file_get_contents($link_dokrad_img) === false ? 'scan-ttd-default.PNG' : $link_dokrad_img);

    // kop surat
    $kopSurat = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM kop_surat LIMIT 1"));
    $link_surat_image = "http://" . $_SERVER['SERVER_NAME'] . ":8000/storage/" . $kopSurat['image'];
    // kondisi ketika gambar kop surat null & ketika server laravel error
    $kop_surat_image = $kopSurat['image'] == null ? 'kop-base.jpg' : (@file_get_contents($link_surat_image) === false ? 'kop-base.jpg' : $link_surat_image);

    // qr code ttd dokter radiologi
    $link_qr_code_ttd = '../phpqrcode/ttddokter/' . $signature;
    $qr_code_ttd = @file_get_contents($link_qr_code_ttd) === false ? 'barcode-default.PNG' : $link_qr_code_ttd;

    // qr code hasil pasien
    $link_qr_code_pasien = '../phpqrcode/hasil-pasien/' . $uid . '.png';
    $qr_code_pasien = @file_get_contents($link_qr_code_pasien) === false ? 'barcode-default.PNG' : $link_qr_code_pasien;

    // tabel expertise menampilkan qr code hasil pasien dan signature dokter
    $expertise = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM xray_expertise LIMIT 1"));

    if ($status == "waiting" || $status == '') {
        echo "<script src='https://unpkg.com/sweetalert/dist/sweetalert.min.js'></script>
            <script type='text/javascript'>
                setTimeout(function () { 
                swal({
                        title: 'Pasien belum di expertise',
                        text:  '',
                        icon: 'error',
                        timer: 3000,
                        showConfirmButton: true
                    });  
                }, 10); 
                window.setTimeout(function(){ 
                    window.close();
                }, 1300); 
            </script>";
        exit();
    }


    $pdf->image($kop_surat_image, 4, 5, 212);
    $pdf->MultiCell(0, 15, '', 0, "J", false);

    if ($pat_sex == 'M') {
        $pat_sex_ind = 'Laki-Laki';
    } else if ($pat_sex == 'F') {
        $pat_sex_ind = 'Perempuan';
    } else {
        $pat_sex_ind = defaultValue($pat_sex);
    }

    // ------------------------------------------------------------

    $pdf->Cell(28, 5, 'No RM', 0, 0, 'L');
    $pdf->Cell(3, 5, ':', 0, 0, 'L');
    $pdf->Cell(45, 5, $pat_id, 0, 0, 'L');
    // ------------------
    $pdf->Cell(35, 5, 'Ruangan/Poliklinik', 0, 0, 'L');
    $pdf->Cell(3, 5, ':', 0, 0, 'L');
    $pdf->Cell(65, 5, $name_dep, 0, 1, 'L');
    // -----------------
    $pdf->Cell(28, 5, 'Nama', 0, 0, 'L');
    $pdf->Cell(3, 5, ':', 0, 0, 'L');
    $pdf->Cell(45, 5, $pat_name, 0, 0, 'L');
    // ------------------
    // $pdf->Cell(35, 5, 'Tanggal Pemeriksaan', 0, 0, 'L');
    // $pdf->Cell(3, 5, ':', 0, 0, 'L');
    // $pdf->Cell(65, 5, defaultValueDate($study_datetime), 0, 1, 'L');
    // ------------------
    $pdf->Cell(35, 5, 'Jam Mulai', 0, 0, 'L');
    $pdf->Cell(3, 5, ':', 0, 0, 'L');
    $pdf->Cell(45, 5, defaultValueTime($updated_time), 0, 1, 'L');
    // -----------------
    $pdf->Cell(28, 5, 'Tgl Lahir', 0, 0, 'L');
    $pdf->Cell(3, 5, ':', 0, 0, 'L');
    $pdf->Cell(45, 5, defaultValueDate($pat_birthdate), 0, 0, 'L');
    // -----------------
    $pdf->Cell(35, 5, 'Jam Selesai', 0, 0, 'L');
    $pdf->Cell(3, 5, ':', 0, 0, 'L');
    $pdf->Cell(65, 5, defaultValueTime($approved_at), 0, 1, 'L');
    // -----------------
    $pdf->Cell(28, 5, 'Jenis Kelamin', 0, 0, 'L');
    $pdf->Cell(3, 5, ':', 0, 0, 'L');
    $pdf->Cell(45, 5, $pat_sex_ind, 0, 0, 'L');
    // -------------------
    $pdf->Cell(35, 5, 'Waktu Pemeriksaan', 0, 0, 'L');
    $pdf->Cell(3, 5, ':', 0, 0, 'L');
    $pdf->Cell(65, 5, spendTime($updated_time, $approved_at, $status), 0, 1, 'L');
    //-------------------
    $pdf->Cell(28, 5, 'Klinis', 0, 0, 'L');
    $pdf->Cell(3, 5, ':', 0, 1, 'L');
    $pdf->Cell(55, 5, trim($spc_needs_one), 0, 0, 'L');
    // -----------------
    // $pdf->Cell(35, 5, '', 0, 0, 'L');
    // $pdf->Cell(3, 5, '', 0, 0, 'L');
    // $pdf->Cell(65, 5, '', 0, 1, 'L');
    // -----------------
    // -----------------
    // $pdf->Cell(28, 5, '', 0, 0, 'L');
    // $pdf->Cell(3, 5, '', 0, 0, 'L');
    // $pdf->Cell(55, 5, $spc_needs_one, 0, 0, 'L');
    // -----------------
    // $pdf->Cell(35, 5, 'Jenis Pemeriksaan', 0, 0, 'L');
    // $pdf->Cell(3, 5, ':', 0, 0, 'L');
    // $pdf->Cell(65, 5, $study_desc_one, 0, 1, 'L');
    // -----------------

    // -----------------
    $pdf->Cell(35, 5, '', 0, 0, 'L');
    $pdf->Cell(3, 5, '', 0, 0, 'L');
    $pdf->Cell(65, 5, $study_desc_two, 0, 1, 'L');
    $pdf->Line(16, 65, 198, 65);
    $fill = str_replace("&nbsp;", " ", $fill);
    $fill = str_replace("&ndash;", "-", $fill);
    $fill = str_replace("&agrave;", "->", $fill);
    $fill = str_replace("&hellip;", "..", $fill);
    $fill = str_replace("&plusmn;", chr(177), $fill);
    $fill = str_replace("&deg;", chr(176), $fill);
    $fill = str_replace("&bull;", "-", $fill);
    $fill = str_replace("&ldquo;", '"', $fill);
    $fill = str_replace("&rdquo;", '"', $fill);
    $fill = str_replace(chr(225), chr(186), $fill);
    $fill = str_replace("&rsquo;", "'", $fill);
    $fill = str_replace("&lsquo;", "'", $fill);
    $fill = str_replace("&amp;", "&", $fill);
    $fill = str_replace("&quot;", "\"", $fill);
    $fill = str_replace("&#39;", "'", $fill);
    $fill = str_replace("&middot;", "-", $fill);
    $fill = str_replace("<ul>", "<br />", $fill);
    $fill = str_replace("<li>", "   " . chr(149) . " ", $fill);
    $fill = str_replace("</li>", "<br />", $fill);
    $fill = str_replace("</ul>", "<br />", $fill);
    $fill = str_replace('<div style="text-align: center;">', '<br /><p align="center">', $fill);
    $fill = str_replace('<div style="text-align: left;">', '<br /><p align="left">', $fill);
    $fill = str_replace('<div style="text-align: right;">', '<br /><p align="right">', $fill);
    $fill = str_replace('<div style="text-align:center;">', '<br /><p align="center">', $fill);
    $fill = str_replace('<div style="text-align:left;">', '<br /><p align="left">', $fill);
    $fill = str_replace('<div style="text-align:right;">', '<br /><p align="right">', $fill);

    // $pdf->WriteHTML("<strong><u><p align='center'>Instalasi Radiologi</p></u></strong>");
    // $pdf->WriteHTML("<br>");
    $pdf->ln(2);
    $pdf->WriteHtml($fill);
    $pdf->WriteHTML("<br>");
    $pdf->WriteHTML("<br>");
    $salam = "Cepu, " . defaultValueDate($study_datetime) . "";
    $pdf->Cell(165, 9, $salam, 0, 1, 'R');

    if ($expertise['signature_dokter_radiologi'] == 'qr_code') {
        // jika ttd menggunakan signature QR CODE
        $sign = $pdf->image($qr_code_ttd, 152, $pdf->GetY(), 25);
    } else if ($expertise['signature_dokter_radiologi'] == 'signature_scan') {
        // jika ttd menggunakan signature scan image
        $pdf->image($dokrad_img, 150, $pdf->GetY(), 28);
    } else if ($expertise['signature_dokter_radiologi'] == 'signature_empty') {
        // jika ttd signature empty
    } else {
        // jika ttd tidak menggunakan signature dan image
    }

    if ($expertise['qr_code_pasien'] == 1) {
        // jika menggunakan qr code hasil pasien
        $hasilPasien = $pdf->image($qr_code_pasien, $pdf->GetX(), $pdf->GetY(), 25);
        $pdf->Ln(27);
        $pdf->Cell(0, 0, 'Hasil bisa diakses maximal 90 Hari dari tanggal', 0, 0, 'L');
        $pdf->Cell(0, 0, $dokrad_name, 0, 1, 'R');
        $pdf->Cell(0, 9, 'dokter radiologi melakukan expertise ', 0, 0, 'L');
        $pdf->Cell(0, 9, $nip, 0, 0, 'R');
    } else {
        $pdf->Ln(27);
        $pdf->Cell(0, 0, $dokrad_name, 0, 1, 'R');
        $pdf->Cell(0, 9, $nip, 0, 0, 'R');
    }

    return $pdf;
}

function pdfProsesImage($uid, $series_iuids, $pdf)
{
    global $conn_pacsio, $select_series, $table_series, $select_instance, $table_instance;

    foreach ($series_iuids as $index => $series_iuid) {
        // menampilkan series
        $row_study_series = mysqli_fetch_assoc(mysqli_query(
            $conn_pacsio,
            "SELECT
            $select_series
            FROM $table_series
            WHERE series.series_iuid = '$series_iuid' AND modality NOT IN('SR')"
        ));
        $pk_series = $row_study_series["pk_series"];
        $series_desc = $row_study_series["series_desc"];
        $num_instances = $row_study_series["num_instances"];

        // menampilkan instance
        $series_instance = mysqli_query(
            $conn_pacsio,
            "SELECT
            $select_instance
            FROM $table_instance
            WHERE series_fk = '$pk_series'"
        );
        // untuk mengatur jarak footer
        $pdf->SetAutoPageBreak(true, 20);
        if ($index > 0) {
            $pdf->AddPage();
        }

        $pdf->Cell(100, 5, $series_desc, 0, 1, 'L');
        $i = 1;
        while ($row_series_instance = mysqli_fetch_assoc($series_instance)) {
            try {
                $client = new Client();
                $sop_iuid = $row_series_instance["sop_iuid"];
                $link_dicom_jpg = "http://$_SERVER[SERVER_ADDR]:9090/dcm4chee-arc/aets/DCM4CHEE/wado?requestType=WADO&studyUID=$uid&seriesUID=$series_iuid&objectUID=$sop_iuid";
                // $link_dicom_jpg = "http://118.99.77.50:9090/dcm4chee-arc/aets/DCM4CHEE/wado?requestType=WADO&studyUID=1.3.12.2.1107.5.1.7.106949.30000024042307531537400000008";
                // $dicom_jpg = @file_get_contents($link_dicom_jpg) === false ? $url . 'barcode-default.PNG' : $link_dicom_jpg;

                $response = $client->request(
                    "GET",
                    $link_dicom_jpg,
                );

                if ($response->getHeader("content-type")[0] != "application/dicom") {

                    // cek size image
                    $size_dicom = getimagesize($link_dicom_jpg);
                    $widthImageLink = $size_dicom[0];
                    $heightImageLink = $size_dicom[1];

                    $aspectRatio = $widthImageLink / $heightImageLink; // perhitungan aspect ratio
                    // $optimalWidth = $aspectRatio * $heightImageLink; // mencari width optimal
                    // $optimalHeight = $widthImageLink / $aspectRatio; // mencari height optimal

                    $widthPage = 180; // angka 180cm karena A4 width 210 - 15 (kiri) - 15 (kanan)
                    $heightPage = 240; // angka 240cm karena terpotong dengan footer (normal 297cm)
                    $column = 4; // lebar kolom

                    // ketika 1 series, kurang dari 4 instance
                    if (mysqli_num_rows($series_instance) <= $column) {
                        $widthImage = $widthPage; // width page
                        $heightImage = $widthImage / $aspectRatio; // mencari height optimal

                        // jika tinggi lebih dari $heightPage, maka width dikurangi
                        if ($heightImage > $heightPage) {
                            $widthImage = $aspectRatio * $heightPage; // mencari width optimal
                            $heightImage = $heightPage; // height page
                        }
                        $ln = 1;
                        // tambahkan halaman jika gambar ke-2 dan seterusnya
                        if ($i > 1) {
                            $pdf->AddPage();
                        }
                    } else {
                        $widthImage = $widthPage / $column; // width page / lebar kolom
                        $heightImage = $widthImage / $aspectRatio; // mencari height optimal
                        // setiap $column kolom buat line baru
                        if ($i % $column == 0) {
                            $ln = 1;
                        } else {
                            $ln = 0;
                        }
                        // jika tinggi lebih dari $heightPage, maka buat halaman baru
                        if ($pdf->GetY() > $heightPage) {
                            $pdf->AddPage();
                        }

                        // jika series terakhir, maka buat halaman baru
                        // if ($i == $num_instances) {
                        //     $pdf->AddPage();
                        //     $ln = 1; // maka enter (masih ada bug ketika series selanjutnya hanya 1 series)
                        // }
                    }

                    // cell untuk mengatur jarak width dan height
                    $pdf->Cell(
                        $widthImage,
                        $heightImage,
                        $pdf->image($link_dicom_jpg, $pdf->GetX(), $pdf->GetY(), $widthImage, 0, "jpg"), // image untuk mengatur gambar
                        0,
                        $ln,
                        'L'
                    );

                    $i++;
                }
            } catch (\Throwable $th) {
                echo "<script>
                alert('(koneksi down)');
                window.close();
            </script>";
            }
        }
    }
    return $pdf;
}

function pdfOutput($uid, $pdf, $output)
{
    global $conn_pacsio;

    $stmt = mysqli_prepare(
        $conn_pacsio,
        "SELECT 
        pat_name,
        pat_id
        FROM patient
        JOIN study
        ON patient.pk = study.patient_fk
        WHERE study.study_iuid = ?"
    );
    mysqli_stmt_bind_param($stmt, "s", $uid);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    $pat_name = substr(removeCharacter(ucwords(strtolower(defaultValue($row['pat_name'])))), 0, 23);
    $pat_id = defaultValue($row['pat_id']);

    return $pdf->Output($output, "$pat_name-$pat_id.pdf");
}
