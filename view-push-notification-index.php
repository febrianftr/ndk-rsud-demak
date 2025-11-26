<?php
require '../koneksi/koneksi.php';
require '../default-value.php';
require '../model/query-base-workload.php';
require '../model/query-base-order.php';
require '../model/query-base-study.php';
require '../model/query-base-patient.php';
require __DIR__ . '/radiology/vendor/autoload.php';

use GuzzleHttp\Client;

$uid = $_GET['uid'];
$server_name = "127.0.0.1";

$row_auth = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT * FROM notification_push_whatsapp_auth"
));

$row = mysqli_fetch_assoc(mysqli_query(
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
	WHERE study_iuid = '$uid'"
));

// ----------------
$pat_name = removeCharacter(str_replace('^^^^', '', defaultValue($row['pat_name'])));
$pat_sex = defaultValue($row['pat_sex']);
$pat_sex_style = styleSex($row['pat_sex']);
$pat_birthdate = $row['pat_birthdate'] == null ? '' : date('d-m-Y', strtotime($row['pat_birthdate']));
$accession_no = defaultValue($row['accession_no']);
$ref_physician = defaultValue($row['ref_physician']);
$study_desc = defaultValue($row['study_desc']);
$mods_in_study = defaultValue($row['mods_in_study']);
$pat_id = defaultValue($row['pat_id']);
$no_foto = defaultValue($row['no_foto']);
$address = defaultValue($row['address']);
$name_dep = defaultValue($row['name_dep']);
$named = defaultValue($row['named']);
$weight = defaultValue($row['weight']);
$radiographer_name = defaultValue($row['radiographer_name']);
$dokrad_name = defaultValue($row['dokrad_name']);
$study_datetime = defaultValue($row['study_datetime']);
$pat_state = defaultValue($row['pat_state']);
$priority = defaultValue($row['priority']);
$spc_needs = defaultValue($row['spc_needs']);
$payment = defaultValue($row['payment']);
$status = defaultValue($row['status']);
$lastname = $row['lastname'];
$updated_time = defaultValueDate($row['updated_time']);
$link = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM rename_link"));
$hostname = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM xray_hostname_publik"));

function phoneFilter($phone)
{
    $first = $phone[0];
    $second = $phone[1];
    if ($first == 0 || $first == '0') {
        $phone = ltrim($phone, $first);
    } else if ($first . $second == 62 || $first . $second == '62') {
        $phone = ltrim($phone, $first . $second);
    }

    return $phone;
}

if (isset($_POST["whatsapp_url_link"])) {
    date_default_timezone_set('Asia/Makassar');
    $code_phone = 62;
    $phone = phoneFilter($_POST["phone"]);
    $code_us = "@c.us";
    $api_url = $row_auth["api_url"];
    $id_instance = $row_auth["id_instance"];
    $api_token_instance = $row_auth["api_token_instance"];
    $chatId = $code_phone . $phone . $code_us;

    if ($hostname == null) {
        $linkText = 'Domain Tidak Ditemukan! Silahkan input domain RS pada aplikasi RIS';
        $message_input_whatsapp = "-";
    } else {
        $linkText = 'Link hasil radiologi';
        // production
        $message_input_whatsapp = "http://$hostname[ip_publik]:8093/$link[link_simrs_expertise]/pasien.php?uid=$uid";
        // development
        // $message_input_whatsapp = "http://$hostname[ip_publik]:8093/medxa-demo-dev/pasien?uid=$uid";
    }
    try {
        $client = new Client([
            'base_uri' => $api_url,
        ]);

        // Daftar sapaan acak
        $greetings = [
            "Halo *$pat_name*, hasil pemeriksaan radiologi Anda sudah tersedia.",
            "Hai *$pat_name*, hasil radiologi Anda sudah dapat diakses.",
            "Halo *$pat_name*, kami informasikan hasil radiologi Anda telah siap.",
            "Hai *$pat_name*, hasil radiologi Anda telah selesai dan bisa dilihat.",
        ];
        // greeting secara acak
        $selectedGreeting = $greetings[array_rand($greetings)];

        // isi pesan utama
        $message = "*RADIOLOGI RSUD Cilacap*\n"
            . $selectedGreeting . "\n\n"
            . "*Nama:* $pat_name\n"
            . "*No Rekam Medis:* $pat_id\n"
            . "*Tanggal Pemeriksaan:* $study_datetime\n\n"
            . "Anda dapat melihat hasilnya melalui tautan berikut:\n"
            . "$message_input_whatsapp\n\n"
            . "_Hasil hanya bisa diakses selama 1 bulan dari tanggal pemeriksaan._\n"
            . "Terima kasih,\n*RSUD CILACAP*";

        // payload JSON
        $headers = [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'chatId' => $chatId,
                'message' => $message
            ],
            'http_errors' => false
        ];

        if ($status == "waiting") {
            echo "<script>
                setTimeout(function () { 
                    swal({
                            title: 'Error',
                            text:  'Belum dilakukan expertise oleh radiolog',
                            icon: 'error',
                            timer: 1000,
                            showConfirmButton: true
                        });  
                    },10);
            </script>";
        } else {
            $response = $client->request('POST', "/waInstance$id_instance/sendMessage/$api_token_instance", $headers);
            $body = $response->getBody();
            $data = json_decode($body, true);
            $code_whatsapp = $response->getStatusCode();

            if ($code_whatsapp == 200) {
                $msgid = $data["idMessage"];
                $message_output_whatsapp = 'msgid : ' . $msgid;
                // echo $body;
                mysqli_query($conn, "INSERT INTO notification_push_whatsapp (uid, msgid, phone, created_at, updated_at) VALUES ('$uid', '$msgid', '$chatId', NOW(), NOW())");
            } else {
                $message_output_whatsapp = $data["message"];
            }
            echo "<script>
                setTimeout(function () { 
                    swal({
                            title: 'Sukses',
                            text:  '$message_output_whatsapp',
                            icon: 'success',
                            timer: 1000,
                            showConfirmButton: true
                        });  
                    },10);
            </script>";
        }
    } catch (GuzzleHttp\Exception\GuzzleException $th) {
        $message_output_whatsapp = $th->getMessage();
        $code_whatsapp = $th->getCode();
        echo "<script>
                setTimeout(function () { 
                    swal({
                            title: 'Hubungi tim IT',
                            text:  '$code_whatsapp',
                            icon: 'error',
                            timer: 1000,
                            showConfirmButton: true
                        });  
                    },10);
            </script>";
    }
}

// BEGIN API DARI NATIVE KE LARAVEL KEMUDIAN KE WHATSAPP TETAPI BELUM FIX SEMUA KARENA ADA QUEUE DELAY DARI GREEN API
// if (isset($_POST["whatsapp_url_expertise_image_pdf"]) || isset($_POST["whatsapp_url_image_pdf"])) {
//     $dir = isset($_POST["whatsapp_url_expertise_image_pdf"]) ? "expertise-image-share" : (isset($_POST["whatsapp_url_image_pdf"]) ? "image-share" : "");
//     date_default_timezone_set('Asia/Jakarta');
//     $whatsapp = $_POST["phone"];
//     try {
//         $client = new Client([
//             'base_uri' => "http://$server_name:8000/",
//         ]);
//         $headers = [
//             'headers' => [
//                 "Content-Type" => "application/json"
//             ],
//             'json' => [
//                 "whatsapp" => $whatsapp,
//                 "uid" => $uid,
//                 "dir" => $dir
//             ],
//             'http_errors' => false
//         ];

//         $response = $client->request('POST', "api/patient-receive-result-pdf-notification-expertise-image-whatsapp/$uid", $headers);
//         $body = $response->getBody();
//         $data = json_decode($body, true);
//         $code_whatsapp = $data["code"];
//         if ($dir == "expertise-image-share") {
//             if ($status == "waiting") {
//                 echo "<script>
//                 setTimeout(function () { 
//                     swal({
//                             title: 'Error',
//                             text:  '$data[message]',
//                             icon: 'error',
//                             timer: 1000,
//                             showConfirmButton: true
//                         });  
//                     },10);
//             </script>";
//             } else {
//                 $return = 1;
//             }
//         } else if ($dir == "image-share") {
//             $return = 1;
//         }
//         if ($return == 1) {
//             $message_output_whatsapp = $data["message"];
//             echo "<script>
//                     setTimeout(function () { 
//                         swal({
//                                 title: 'Check whatsapp',
//                                 text:  '$message_output_whatsapp',
//                                 icon: 'info',
//                                 timer: 1000,
//                                 showConfirmButton: true
//                             });  
//                         },10);
//                 </script>";
//         }
//     } catch (GuzzleHttp\Exception\GuzzleException $th) {
//         $message_output_whatsapp = $th->getMessage();
//         $code_whatsapp = $th->getCode();
//         echo "<script>
//                 setTimeout(function () { 
//                     swal({
//                             title: 'Hubungi tim IT',
//                             text:  '$code_whatsapp',
//                             icon: 'error',
//                             timer: 1000,
//                             showConfirmButton: true
//                         });  
//                     },10);
//             </script>";
//     }
// }
// END API DARI NATIVE KE LARAVEL KEMUDIAN KE WHATSAPP TETAPI BELUM FIX SEMUA KARENA ADA QUEUE DELAY DARI GREEN API

if (isset($_POST["whatsapp_url_expertise_image_pdf"]) || isset($_POST["whatsapp_url_image_pdf"])) {
    date_default_timezone_set('Asia/Makassar');
    $dir = isset($_POST["whatsapp_url_expertise_image_pdf"]) ? "expertise-image-share" : (isset($_POST["whatsapp_url_image_pdf"]) ? "image-share" : "");
    $code_phone = 62;
    $phone = phoneFilter($_POST["phone"]);
    $code_us = "@c.us";
    $api_url = $row_auth["api_url"];
    $id_instance = $row_auth["id_instance"];
    $api_token_instance = $row_auth["api_token_instance"];
    $chatId = $code_phone . $phone . $code_us;
    // upload file by url harus menggunakan IP publik untuk mengirimkan file ke server whatsapp.
    $message_input_whatsapp = "http://$hostname[ip_publik]:8093/ndk/radiology/pdf/$dir/$uid.pdf";
    try {
        $client = new Client([
            'base_uri' => $api_url,
        ]);

        // Daftar sapaan acak
        $greetings = [
            "Halo *$pat_name*, hasil pemeriksaan radiologi Anda sudah tersedia.",
            "Hai *$pat_name*, hasil radiologi Anda sudah dapat diakses.",
            "Kepada Tn/Ny *$pat_name*, kami informasikan hasil radiologi Anda telah siap.",
            "Hallo *$pat_name*, hasil radiologi Anda telah selesai dan bisa dilihat.",
        ];
        // greeting secara acak
        $selectedGreeting = $greetings[array_rand($greetings)];

        // isi pesan utama
        $message = "*RADIOLOGI RSUD Cilacap*\n\n"
            . $selectedGreeting . "\n\n"
            . "*Nama:* $pat_name\n"
            . "*No Rekam Medis:* $pat_id\n"
            . "*Tanggal Pemeriksaan:* $study_datetime\n\n"
            // . "Anda dapat melihat hasilnya melalui tautan berikut:\n"
            // . "$message_input_whatsapp\n\n"
            . "_Hasil berupa PDF, klik tautan file untuk membuka, Hubungi kami jika ada kesulitan._\n\n"
            . "Terima kasih,\n*RSUD CILACAP*";

        $headers = [
            'headers' => [
                "Content-Type" => "application/json"
            ],
            'json' => [
                "chatId" => $chatId,
                "urlFile" => $message_input_whatsapp,
                "fileName" => "$pat_name-$pat_id.pdf",
                "caption" => $message
            ],
            'http_errors' => false
        ];

        if ($dir == "expertise-image-share") {
            if ($status == "waiting") {
                echo "<script>
                setTimeout(function () { 
                    swal({
                            title: 'Error',
                            text:  'Belum dilakukan expertise oleh radiolog',
                            icon: 'error',
                            timer: 1000,
                            showConfirmButton: true
                        });  
                    },10);
            </script>";
            } else {
                $return = 1;
            }
        } else if ($dir == "image-share") {
            $return = 1;
        }

        if ($return == 1) {
            $response = $client->request('POST', "/waInstance$id_instance/sendFileByUrl/$api_token_instance", $headers);
            $body = $response->getBody();
            $data = json_decode($body, true);
            $code_whatsapp = $response->getStatusCode();

            if ($code_whatsapp == 200) {
                $msgid = $data["idMessage"];
                $message_output_whatsapp = 'msgid : ' . $msgid;
                // echo $body;
                mysqli_query($conn, "INSERT INTO notification_push_whatsapp (uid, msgid, phone, created_at, updated_at) VALUES ('$uid', '$msgid', '$chatId', NOW(), NOW())");
            } else {
                $message_output_whatsapp = $data["message"];
            }
            echo "<script>
                setTimeout(function () { 
                    swal({
                            title: 'Sukses',
                            text:  '$message_output_whatsapp',
                            icon: 'success',
                            timer: 1000,
                            showConfirmButton: true
                        });  
                    },10);
            </script>";
        }
    } catch (GuzzleHttp\Exception\GuzzleException $th) {
        $message_output_whatsapp = $th->getMessage();
        $code_whatsapp = $th->getCode();
        echo "<script>
                setTimeout(function () { 
                    swal({
                            title: 'Hubungi tim IT',
                            text:  '$code_whatsapp',
                            icon: 'error',
                            timer: 1000,
                            showConfirmButton: true
                        });  
                    },10);
            </script>";
    }
}

if (isset($_POST["email_url_link"])) {
    date_default_timezone_set('Asia/Makassar');
    $email = $_POST["email"];
    try {
        $client = new Client([
            'base_uri' => "http://$server_name:8000/",
        ]);
        $headers = [
            'headers' => [
                "Content-Type" => "application/json"
            ],
            'json' => [
                "email" => $email,
                "uid" => $uid
            ],
            'http_errors' => false
        ];

        $response = $client->request('POST', "api/patient-receive-result-link-notification-email/$uid", $headers);
        $body = $response->getBody();
        $data = json_decode($body, true);
        $code_email = $data["code"];
        if ($status == "waiting") {
            echo "<script>
                setTimeout(function () { 
                    swal({
                            title: 'Error',
                            text:  '$data[message]',
                            icon: 'error',
                            timer: 1000,
                            showConfirmButton: true
                        });  
                    },10);
            </script>";
        } else {
            $message_output_email = $data["message"];
            echo "<script>
                    setTimeout(function () { 
                        swal({
                                title: 'Check Email',
                                text:  '$message_output_email',
                                icon: 'info',
                                timer: 1000,
                                showConfirmButton: true
                            });  
                        },10);
                </script>";
        }
    } catch (GuzzleHttp\Exception\GuzzleException $th) {
        $message_output_email = $th->getMessage();
        $code_email = $th->getCode();
        echo "<script>
                setTimeout(function () { 
                    swal({
                            title: 'Hubungi tim IT',
                            text:  '$code_email',
                            icon: 'error',
                            timer: 1000,
                            showConfirmButton: true
                        });  
                    },10);
            </script>";
    }
}

if (isset($_POST["email_url_expertise_image_pdf"]) || isset($_POST["email_url_image_pdf"])) {
    $dir = isset($_POST["email_url_expertise_image_pdf"]) ? "expertise-image-share" : (isset($_POST["email_url_image_pdf"]) ? "image-share" : "");
    date_default_timezone_set('Asia/Makassar');
    $email = $_POST["email"];
    try {
        $client = new Client([
            'base_uri' => "http://$server_name:8000/",
        ]);
        $headers = [
            'headers' => [
                "Content-Type" => "application/json"
            ],
            'json' => [
                "email" => $email,
                "uid" => $uid,
                "dir" => $dir
            ],
            'http_errors' => false
        ];

        $response = $client->request('POST', "api/patient-receive-result-pdf-notification-email/$uid", $headers);
        $body = $response->getBody();
        $data = json_decode($body, true);
        $code_email = $data["code"];
        if ($dir == "expertise-image-share") {
            if ($status == "waiting") {
                echo "<script>
                setTimeout(function () { 
                    swal({
                            title: 'Error',
                            text:  '$data[message]',
                            icon: 'error',
                            timer: 1000,
                            showConfirmButton: true
                        });  
                    },10);
            </script>";
            } else {
                $return = 1;
            }
        } else if ($dir == "image-share") {
            $return = 1;
        }
        if ($return == 1) {
            $message_output_email = $data["message"];
            echo "<script>
                    setTimeout(function () { 
                        swal({
                                title: 'Check Email',
                                text:  '$message_output_email',
                                icon: 'info',
                                timer: 1000,
                                showConfirmButton: true
                            });  
                        },10);
                </script>";
        }
    } catch (GuzzleHttp\Exception\GuzzleException $th) {
        $message_output_email = $th->getMessage();
        $code_email = $th->getCode();
        echo "<script>
                setTimeout(function () { 
                    swal({
                            title: 'Hubungi tim IT',
                            text:  '$code_email',
                            icon: 'error',
                            timer: 1000,
                            showConfirmButton: true
                        });  
                    },10);
            </script>";
    }
}

if (isset($_POST["telegram_url_link"])) {
    date_default_timezone_set('Asia/Makassar');
    $telegram_chat_id = $_POST["telegram_chat_id"];
    try {
        $client = new Client([
            'base_uri' => "http://$server_name:8000/",
        ]);
        $headers = [
            'headers' => [
                "Content-Type" => "application/json"
            ],
            'json' => [
                "telegram_chat_id" => $telegram_chat_id,
                "uid" => $uid
            ],
            'http_errors' => false
        ];

        $response = $client->request('POST', "api/patient-receive-result-link-notification-telegram/$uid", $headers);
        $body = $response->getBody();
        $data = json_decode($body, true);
        $code_telegram = $data["code"];
        if ($status == "waiting") {
            echo "<script>
                setTimeout(function () { 
                    swal({
                            title: 'Error',
                            text:  '$data[message]',
                            icon: 'error',
                            timer: 1000,
                            showConfirmButton: true
                        });  
                    },10);
            </script>";
        } else {
            $message_output_telegram = $data["message"];
            echo "<script>
                setTimeout(function () { 
                    swal({
                            title: 'Check Telegram',
                            text:  '$message_output_telegram',
                            icon: 'info',
                            timer: 1000,
                            showConfirmButton: true
                        });  
                    },10);
            </script>";
        }
    } catch (GuzzleHttp\Exception\GuzzleException $th) {
        $message_output_telegram = $th->getMessage();
        $code_telegram = $th->getCode();
        echo "<script>
                setTimeout(function () { 
                    swal({
                            title: 'Hubungi tim IT',
                            text:  '$code_telegram',
                            icon: 'error',
                            timer: 1000,
                            showConfirmButton: true
                        });  
                    },10);
            </script>";
    }
}

if (isset($_POST["telegram_url_expertise_image_pdf"]) || isset($_POST["telegram_url_image_pdf"])) {
    $dir = isset($_POST["telegram_url_expertise_image_pdf"]) ? "expertise-image-share" : (isset($_POST["telegram_url_image_pdf"]) ? "image-share" : "");
    date_default_timezone_set('Asia/Makassar');
    $telegram_chat_id = $_POST["telegram_chat_id"];
    try {
        $client = new Client([
            'base_uri' => "http://$server_name:8000/",
        ]);
        $headers = [
            'headers' => [
                "Content-Type" => "application/json"
            ],
            'json' => [
                "telegram_chat_id" => $telegram_chat_id,
                "uid" => $uid,
                "dir" => $dir
            ],
            'http_errors' => false
        ];

        $response = $client->request('POST', "api/patient-receive-result-pdf-notification-telegram/$uid", $headers);
        $body = $response->getBody();
        $data = json_decode($body, true);
        $code_telegram = $data["code"];
        if ($dir == "expertise-image-share") {
            if ($status == "waiting") {
                echo "<script>
                setTimeout(function () { 
                    swal({
                            title: 'Error',
                            text:  '$data[message]',
                            icon: 'error',
                            timer: 1000,
                            showConfirmButton: true
                        });  
                    },10);
            </script>";
            } else {
                $return = 1;
            }
        } else if ($dir == "image-share") {
            $return = 1;
        }

        if ($return == 1) {
            $message_output_telegram = $data["message"];
            echo "<script>
                setTimeout(function () { 
                    swal({
                            title: 'Check Telegram',
                            text:  '$message_output_telegram',
                            icon: 'info',
                            timer: 1000,
                            showConfirmButton: true
                        });  
                    },10);
            </script>";
        }
    } catch (GuzzleHttp\Exception\GuzzleException $th) {
        $message_output_telegram = $th->getMessage();
        $code_telegram = $th->getCode();
        echo "<script>
                setTimeout(function () { 
                    swal({
                            title: 'Hubungi tim IT',
                            text:  '$code_telegram',
                            icon: 'error',
                            timer: 1000,
                            showConfirmButton: true
                        });  
                    },10);
            </script>";
    }
}

if (isset($_POST["telegram_update_chatid"])) {
    try {
        $client = new Client([
            'base_uri' => "http://$server_name:8000/",
        ]);

        $response = $client->request('GET', "telegram-update");
        $body = $response->getBody();
        $data = json_decode($body, true);
        $telegram_chat_id = $data;
        $code_telegram = $response->getStatusCode();
        $message_output_telegram = $data["message"];
        echo "<script>
                setTimeout(function () { 
                    swal({
                            title: 'Sukses',
                            text:  '$message_output_telegram',
                            icon: 'success',
                            timer: 1000,
                            showConfirmButton: true
                        });  
                    },10);
            </script>";
    } catch (GuzzleHttp\Exception\GuzzleException $th) {
        $message_output_telegram = $th->getMessage();
        $code_telegram = $th->getCode();
        echo "<script>
                setTimeout(function () { 
                    swal({
                            title: 'Hubungi tim IT',
                            text:  '$code_telegram',
                            icon: 'error',
                            timer: 1000,
                            showConfirmButton: true
                        });  
                    },10);
            </script>";
    }
}

// if (isset($_POST["whatsapp_message_status"])) {
//     date_default_timezone_set('Asia/Makassar');
//     $msgid = $_POST['msgid'];
//     $phone = $_POST["phone"];
//     $api_url = $row_auth["api_url"];
//     $id_instance = $row_auth["id_instance"];

//     try {
//         $client = new Client([
//             'base_uri' => $api_url,
//         ]);
//         $headers = [
//             'headers' => [
//                 "Accept" => "application/json",
//                 "Authorization" => "Bearer $api_url"
//             ],
//             // 'http_errors' => false
//         ];

//         $response = $client->request('GET', "/api/v2/message/$msgid/status", $headers);
//         $body = $response->getBody();
//         $message_output_status_whatsapp = json_decode($body, true);
//         $code_status_whatsapp = $response->getStatusCode();
//         echo "<script>  
//                 setTimeout(function () { 
//                     swal({
//                             title: 'Sukses',
//                             text:  '$code_status_whatsapp',
//                             icon: 'success',
//                             timer: 1000,
//                             showConfirmButton: true
//                         });  
//                     },10);
//             </script>";
//     } catch (GuzzleHttp\Exception\GuzzleException $th) {
//         $message_output_status_whatsapp = $th->getMessage();
//         $code_status_whatsapp = $th->getCode();
//         echo "<script>
//                 setTimeout(function () { 
//                     swal({
//                             title: 'Hubungi tim IT',
//                             text:  '$code_status_whatsapp',
//                             icon: 'error',
//                             timer: 1000,
//                             showConfirmButton: true
//                         });  
//                     },10);
//             </script>";
//     }
// }
if (is_file("../radiology/pdf/expertise-image-share/$uid.pdf")) {
    $textExpertiseImageShare = "";
    $expertiseImageSharedisabled = "";
    $expertiseImageShare = '<a href="../radiology/pdf/expertise-image-share/' . $uid . '.pdf" target="_blank"><span class="btn rgba-stylish-slight btn-inti2" style="box-shadow: none;"><img src="../image/file.svg" data-toggle="tooltip" title="For button send pdf (view expertise & image)" style="width: 100%;"></span></a><a href="view-choose-series.php?uid=' . $uid . '"><span class="btn rgba-stylish-slight darken-1 btn-inti2" style="text-align: left;"><img src="../image/choose-series.svg" data-toggle="tooltip" title="Choose Series" style="width: 50%;"></span></a>';
} else {
    $textExpertiseImageShare = "Send Expertise Image (PDF) /";
    $expertiseImageSharedisabled = "disabled";
    $expertiseImageShare = "";
}

if (is_file("../radiology/pdf/image-share/$uid.pdf")) {
    $textImageShare = "";
    $imageSharedisabled = "";
    $imageShare = '<a href="../radiology/pdf/image-share/' . $uid . '.pdf" target="_blank"><span class="btn rgba-stylish-slight btn-inti2" style="box-shadow: none;"><img src="../image/file.svg" data-toggle="tooltip" title="For button send pdf (view image)" style="width: 100%;"></span></a><a href="view-choose-series.php?uid=' . $uid . '"><span class="btn rgba-stylish-slight darken-1 btn-inti2" style="text-align: left;"><img src="../image/choose-series.svg" data-toggle="tooltip" title="Choose Series" style="width: 50%;"></span></a>';
} else {
    $textImageShare = "Send Image (PDF)";
    $imageSharedisabled = "disabled";
    $imageShare = "";
}

if (is_file("../radiology/pdf/image-share/$uid.pdf") && is_file("../radiology/pdf/expertise-image-share/$uid.pdf")) {
    $notificationShare = "";
} else {
    $notificationShare = "Tombol <b>$textExpertiseImageShare $textImageShare</b> disabled karena belum dipilih gambar radiologi. <br /> Silahkan pilih gambar radiologi pada icon" . '<a href="view-choose-series.php?uid=' . $uid . '"><span class="btn rgba-stylish-slight darken-1 btn-inti2" style="text-align: left;"><img src="../image/choose-series.svg" data-toggle="tooltip" title="Choose Series" style="width: 50%;"></span></a>';
}

?>

<div class="container-fluid">
    <div class="row">
        <div id="content1"><br><br><br>
            <div class="col-md-12 box-change-dokter table-box" style="height: auto;">
                <table>
                    <tr>
                        <td>UID </td>
                        <td>&nbsp;&nbsp;: <?= $uid ?></td>
                    </tr>
                    <tr>
                        <td>Nama Pasien </td>
                        <td>&nbsp;&nbsp;: <?= removeCharacter($pat_name) ?></td>
                    </tr>
                    <tr>
                        <td>MRN </td>
                        <td>&nbsp;&nbsp;: <?= $pat_id ?></td>
                    </tr>
                    <tr>
                        <td>ACCESSION NUMBER </td>
                        <td>&nbsp;&nbsp;: <?= $accession_no ?></td>
                    </tr>
                    <tr>
                        <td>Jenis Kelamin </td>
                        <td>&nbsp;&nbsp;: <?= $pat_sex_style ?></td>
                    </tr>
                    <tr>
                        <td>Pemeriksaan </td>
                        <td>&nbsp;&nbsp;: <?= $study_desc ?></td>
                    </tr>
                    <tr>
                        <td>Modality </td>
                        <td>&nbsp;&nbsp;: <?= $mods_in_study ?></td>
                    </tr>
                </table>
            </div>

            <div class="col-md-12 box-change-dokter table-box" style="height: auto;">
                <!-- Form untuk whatsapp post -->
                <h5>SEND WHATSAPP</h5>
                <form method="POST">
                    <div class="input-group">
                        <div class="input-group-addon">+62</div>
                        <input type="number" class="form-control" id="phone" name="phone" placeholder="Masukkan No handphone pasien" value="<?= isset($phone) ? $phone : $lastname ?>" required>
                    </div>
                    <br>
                    <a class="btn btn-info btn-md" href="workload" style="border-radius: 5px; box-shadow:none">Back</a>
                    <!-- <button class="btn btn-info btn-md" type="submit" id="whatsapp_url_link" name="whatsapp_url_link" style="border-radius: 5px; box-shadow:none">
                            <span class="spinner-grow spinner-grow-sm loading" role="status" aria-hidden="true"></span>
                            <p class="loading" style="display:inline;">Loading...</p>
                            <p class="ubah" style="display:inline;">Send Link</p>
                        </button> -->
                    <button class="btn btn-info btn-md" type="submit" id="whatsapp_url_expertise_image_pdf" name="whatsapp_url_expertise_image_pdf" style="border-radius: 5px; box-shadow:none" <?= $expertiseImageSharedisabled; ?>>
                        <span class="spinner-grow spinner-grow-sm loading" role="status" aria-hidden="true"></span>
                        <p class="loading" style="display:inline;">Loading...</p>
                        <p class="ubah" style="display:inline;">Send Expertise Image (PDF)</p>
                    </button>
                    <?= $expertiseImageShare; ?>
                    <button class="btn btn-info btn-md" type="submit" id="whatsapp_url_image_pdf" name="whatsapp_url_image_pdf" style="border-radius: 5px; box-shadow:none" <?= $imageSharedisabled; ?>>
                        <span class="spinner-grow spinner-grow-sm loading" role="status" aria-hidden="true"></span>
                        <p class="loading" style="display:inline;">Loading...</p>
                        <p class="ubah" style="display:inline;">Send Image (PDF)</p>
                    </button>
                    <?= $imageShare; ?>
                    <?php
                    if ($notificationShare != "") { ?>
                        <div class='alert alert-warning mt-1' role='alert'><?= $notificationShare; ?></div>
                    <?php } ?>

                </form>

                <div class="table-box">
                    <!-- <h6>Input</h6>
                    <p id="input"><?= @$message_input_whatsapp ?></p> -->
                    <h6>Output</h6>
                    <?php if (isset($_POST["whatsapp_url_link"]) || isset($_POST["whatsapp_url_expertise_image_pdf"]) || isset($_POST["whatsapp_url_image_pdf"])) { ?>
                        <p id="output">
                            <?php if (@$code_whatsapp == 200) { ?>
                        <p class="text-success"><?= @$message_output_whatsapp ?></p>
                    <?php } else { ?>
                        <p class="text-danger"><?= @$message_output_whatsapp ?></p>
                    <?php } ?>
                <?php } ?>
                </p>
                </div>
                <br />
                <!-- Form untuk whatsapp get status -->
                <!-- <h5>STATUS WHATSAPP</h5>
                    <form method="POST">
                        <div class="input-group">
                            <div class="input-group-addon">msgid</div>
                            <input type="text" class="form-control" id="msgid" name="msgid" placeholder="Masukkan msgid" value="<?= isset($msgid) ? $msgid : '' ?>" required>
                        </div>
                        <br>
                        <a class="btn btn-info btn-md" href="workload" style="border-radius: 5px; box-shadow:none">Back</a>
                        <button class="btn btn-info btn-md" type="submit" id="whatsapp_message_status" name="whatsapp_message_status" style="border-radius: 5px; box-shadow:none">
                            <span class="spinner-grow spinner-grow-sm loading" role="status" aria-hidden="true"></span>
                            <p class="loading" style="display:inline;">Loading...</p>
                            <p class="ubah" style="display:inline;">Send</p>
                        </button>
                    </form>
                    <br>
                    <div class="table-box">
                        <h6>Input</h6>
                        <p id="input"><?= @$msgid ?></p>
                        <h6>Output</h6>
                        <p id="output">
                            <?php if (isset($_POST["whatsapp_message_status"])) { ?>
                        <p class="text-success">
                            <?php if (@$code_status_whatsapp == 200) { ?>
                        <table class="table table-borderless">
                            <tr>
                                <td>msgid</td>
                                <td>:</td>
                                <td><?= defaultValue(@$message_output_status_whatsapp['msgid']); ?></td>
                            </tr>
                            <tr>
                                <td>phone_number</td>
                                <td>:</td>
                                <td><?= defaultValue(@$message_output_status_whatsapp['phone_number']); ?></td>
                            </tr>
                            <tr>
                                <td>last_status</td>
                                <td>:</td>
                                <td><?= defaultValue(@$message_output_status_whatsapp['last_status']); ?></td>
                            </tr>
                            <tr>
                                <td>history_status</td>
                                <td>:</td>
                                <td>
                                    <table class="table table-borderless">
                                        <thead>
                                            <tr>
                                                <td>timestamp</td>
                                                <td>status</td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach ($message_output_status_whatsapp['history_status'] as $history) { ?>
                                                <tr>
                                                    <td><?= date("d-m-Y H:i:s", strtotime($history['timestamp'])); ?></td>
                                                    <td><?= defaultValue($history['status']); ?></td>
                                                </tr>
                                            <?php
                                            } ?>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td>additional_info</td>
                                <td>:</td>
                            </tr>
                            <tr>
                                <td>code</td>
                                <td>:</td>
                                <td><?= @$message_output_status_whatsapp['additional_info']["code"] == "" ? "-" : @$message_output_status_whatsapp['additional_info']["code"]; ?></td>
                            </tr>
                            <tr>
                                <td>reason</td>
                                <td>:</td>
                                <td>
                                    <p class="text-danger"><?= @$message_output_status_whatsapp['additional_info']["reason"] == "" ? "-" : @$message_output_status_whatsapp['additional_info']["reason"]; ?></p>
                                </td>
                            </tr>
                        </table>
                        </p>
                    <?php } else { ?>
                        <p class="text-danger"><?= var_dump(@$message_output_status_whatsapp) ?></p>
                    <?php } ?>
                <?php } ?>
                </p>
                    </div> -->
            </div>
        </div>
    </div>

</div>