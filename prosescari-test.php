
<?php
session_start();
require 'koneksi/koneksi.php';
require 'viewer-all.php';
require 'default-value.php';
require 'model/query-base-workload.php';
require 'model/query-base-workload-bhp.php';
require 'model/query-base-order.php';
require 'model/query-base-study.php';
require 'model/query-base-patient.php';
require 'model/query-base-dokter-radiology.php';
require 'model/query-base-take-envelope.php';
require 'date-time-zone.php';

$username = $_SESSION['username'];
$level = $_SESSION['level'];
$http_referer = $_SERVER['HTTP_REFERER'] ?? '';
$explode = explode('/', $http_referer);
$queryphp = in_array("query.php", $explode);

// kolom untuk order by 
$columns = array('pk', 'pk', 'status', 'pat_name', 'pat_id', 'study_datetime', 'patientid', 'pat_birthdate', 'pat_sex', 'prosedur', 'pk', 'mods_in_study', 'named', 'name_dep', 'dokrad_name', 'radiographer_name', 'approved_at', 'pk');

$row_dokrad = mysqli_fetch_assoc(mysqli_query(
  $conn,
  "SELECT pk 
  FROM $table_dokter_radiology 
  WHERE username = '$username'"
));
$pk = $row_dokrad['pk'];

// query ketika login radiologi
$kondisi = " WHERE xray_workload.status = 'approved'
AND xray_workload.pk_dokter_radiology = '$pk'";

$query_base = "SELECT 
              pat_id,
              pat_name,
              pat_sex,
              pat_birthdate,
              study_iuid,
              study_datetime,
              mods_in_study,
              study.updated_time,
              status,
              kv,
              mas,
              spc_needs,
              approved_at,
              pk_dokter_radiology,
              patientid AS no_foto,
              named,
              dokradid,
              dokrad_name,
              name_dep,
              radiographer_name,
              priority,
              prosedur,
              contrast,
              fromorder
              FROM $table_patient
              JOIN $table_study
              ON patient.pk = study.patient_fk
              JOIN $table_workload
              ON study.study_iuid = xray_workload.uid
              LEFT JOIN $table_order
              ON xray_order.uid = xray_workload.uid
              LEFT JOIN $table_workload_bhp
              ON xray_workload.uid = xray_workload_bhp.uid";

// kondisi jika login radiology dan link workload.php
if ($level == 'radiology' && !$queryphp) {
  $query =  $query_base . $kondisi . ' AND ';
} else {
  // kondisi jika login selain radiology
  $query = $query_base . ' WHERE ';
  $kondisi = '';
}

if ($_POST["is_date_search"] == "yes") {
  $from = date_create($_POST["from_study_datetime"]);
  $from_study_datetime = date_format($from, "Y-m-d H:i");

  $to = date_create($_POST["to_study_datetime"]);
  $to_study_datetime = date_format($to, "Y-m-d H:i");

  $query .= 'study.study_datetime BETWEEN "' . $from_study_datetime . '" AND "' . $to_study_datetime . '" AND ';
}

// kolom untuk mencari LIKE masing2 kolom (SEARCHING)
if (isset($_POST["search"]["value"])) {
  $query .= '
  (pat_id LIKE "%' . $_POST["search"]["value"] . '%" 
  OR patientid LIKE "%' . $_POST["search"]["value"] . '%" 
  OR pat_name LIKE "%' . $_POST["search"]["value"] . '%" 
  OR pat_birthdate LIKE "%' . $_POST["search"]["value"] . '%"
  OR pat_sex LIKE "%' . $_POST["search"]["value"] . '%"
  OR prosedur LIKE "%' . $_POST["search"]["value"] . '%"
  OR mods_in_study LIKE "%' . $_POST["search"]["value"] . '%" 
  OR named LIKE "%' . $_POST["search"]["value"] . '%"
  OR radiographer_name LIKE "%' . $_POST["search"]["value"] . '%"
  OR study.study_datetime LIKE "%' . $_POST["search"]["value"] . '%"
  OR approved_at LIKE "%' . $_POST["search"]["value"] . '%"
  )';
}

// jika modality disearch
if (isset($_POST['mods_in_study']) && $_POST['mods_in_study'] != "") {
  $mods_in_study = implode("','", $_POST['mods_in_study']);
  $mods_in_study = str_replace('\\', '\\\\', strtoupper($mods_in_study));
  $query .= "AND UPPER(mods_in_study) IN('" . $mods_in_study . "')
 ";
}

// jika keyword nama diketik
if (isset($_POST['pat_name']) && $_POST['pat_name'] != "") {
  $pat_name = strtoupper($_POST['pat_name']);
  $query .= 'AND UPPER(pat_name) LIKE "%' . $pat_name . '%"
 ';
}

// jika mrn diketik
if (isset($_POST['mrn']) && $_POST['mrn'] != "") {
  $mrn = strtoupper($_POST['mrn']);
  $query .= 'AND UPPER(pat_id) LIKE "%' . $mrn . '%"
 ';
}

// jika login radiology mencari berdasarkan fill, else mencari berdasarkan no foto
if ($level == 'radiology') {
  // jika fill
  if (isset($_POST['fill']) && $_POST['fill'] != "") {
    $fill = strtoupper($_POST['fill']);
    $query .= 'AND UPPER(fill) LIKE "%' . $fill . '%"
  ';
  }
} else {
  // jika patientid / nofoto diketik
  if (isset($_POST['patientid']) && $_POST['patientid'] != "") {
    $patientid = strtoupper($_POST['patientid']);
    $query .= 'AND UPPER(patientid) LIKE "%' . $patientid . '%"
  ';
  }
}

// order by
if (isset($_POST["order"])) {
  $query .= 'ORDER BY ' . $columns[$_POST['order']['0']['column']] . ' ' . $_POST['order']['0']['dir'] . ' 
 ';
} else {
  $query .= 'ORDER BY study.study_datetime DESC ';
}

$query1 = '';

if ($_POST["length"] != -1) {
  $query1 = 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
}

// echo $query;

$number_filter_row = mysqli_num_rows(mysqli_query($conn_pacsio, $query));

$result = mysqli_query($conn_pacsio, $query . $query1);


$data = array();
$i = 1;
while ($row = mysqli_fetch_array($result)) {
  $pat_name = defaultValue($row['pat_name']);
  $pat_sex = styleSex($row['pat_sex']);
  $pat_birthdate = diffDate($row['pat_birthdate']);
  $study_iuid = defaultValue($row['study_iuid']);
  $study_datetime = defaultValueDateTime($row['study_datetime']);
  $mods_in_study = defaultValue($row['mods_in_study']);
  $updated_time = defaultValueDateTime($row['updated_time']);
  $pat_id = defaultValue($row['pat_id']);
  $no_foto = defaultValue($row['no_foto']);
  $name_dep = defaultValue($row['name_dep']);
  $named = defaultValue($row['named']);
  $radiographer_name = defaultValue($row['radiographer_name']);
  $dokrad_name = defaultValue($row['dokrad_name']);
  $dokradid = $row['dokradid'];
  $priority = defaultValue($row['priority']);
  $status = styleStatus($row['status'], $study_iuid);
  $fromorder = $row['fromorder'];
  $contrast = $row['contrast'];
  $prosedur = defaultValue($row['prosedur']);
  $approved_at = defaultValueDateTime($row['approved_at']);
  $spendtime = spendTime($study_datetime, $approved_at, $row['status']);
  $blinking = hour($study_datetime, $row['status'], $priority, $mods_in_study, $contrast, $prosedur);
  $pk_dokter_radiology = $row['pk_dokter_radiology'];
  $kv = $row['kv'];
  $mas = $row['mas'];
  $spc_needs = $row['spc_needs'];
  //kondisi status change doctor
  if ($row['status'] == 'approved') {
    $workload_status = 'approved';
  } else {
    $workload_status = 'waiting';
  }
  //menambahkan ippublic link ohif
  $addonlinkohif = "http://" . $hostname['ip_publik'] . ":92/viewer/";
  // kondisi ketika dokter belum ada menggunakan icon berbeda
  if ($pk_dokter_radiology == null && $dokradid == null) {
    $icon_change_doctor = CHANGEDOCTORICONNO;
  } else {
    $icon_change_doctor = CHANGEDOCTORICONYES;
  }

  // kondisi ketika klinis dan kv mas kosong menggunakan icon berbeda
  if ($kv == null || $mas == null || $spc_needs == null) {
    $icon_edit_pasien = EDITPASIENICONNO;
    // $icon_edit_pasien = "no";
  } else {
    $icon_edit_pasien = EDITPASIENICONYES;
    // $icon_edit_pasien = "yes";
  }

  $row_envelope = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT is_taken, name, created_at FROM $table_take_envelope WHERE uid = '$row[study_iuid]'"
  ));
  $is_taken = $row_envelope['is_taken'];
  $name_envelope = $row_envelope['name'];
  $created_at_envelope = $row_envelope['created_at'];

  // kondisi ketika hasil expertise belum diambil menggunakan icon berbeda
  if ($row['status'] == 'waiting') {
    $icon_get_expertise = GETEXPERTISEICONWAITING;
    $href_get_expertise = GETEXPERTISEHREFNO;
  } else if ($is_taken == null && $row['status'] == 'approved' || $is_taken == 0 && $row['status'] == 'approved') {
    $icon_get_expertise = GETEXPERTISEICONNO;
    $href_get_expertise = GETEXPERTISEHREFYES . $study_iuid;
  } else {
    $icon_get_expertise = GETEXPERTISEICONYES;
    $href_get_expertise = GETEXPERTISEHREFYES . $study_iuid;
  }

  // kondisi ketika detail nama lihat detail query (radiographer, referral)
  $detail = '<a href="#" class="hasil-all penawaran-a" data-id="' . $row['study_iuid'] . '">' . removeCharacter(mb_convert_encoding($pat_name, 'UTF-8', 'ISO-8859-1')) . '</a>';

  //kondisi status change doctor
  // kondisi session level ketika login
  $level = $_SESSION['level'];

  // ketika login radiology
  if ($level == 'radiology') {
    if (!$queryphp) {
      // kondisi jika login radiology dan link workload.php
      $detail = '<a href="workload-edit.php?uid=' . $study_iuid . '" class="penawaran-a">' . removeCharacter(mb_convert_encoding($pat_name, 'UTF-8', 'ISO-8859-1')) . '</a>';
      $editworkload = EDITWORKLOADFIRST . $study_iuid . EDITWORKLOADLAST . CHANGEDOCTORFIRST . "'$study_iuid', '$dokradid', '$workload_status'" . CHANGEDOCTORLAST . $icon_change_doctor . CHANGEDOCTORVERYLAST;
    } else {
      // kondisi jika login radiology dan link query.php
      $editworkload = "";
    }
    $level =
      // HOROSFIRST . $study_iuid . HOROSLAST .
      RADIANTFIRST . $study_iuid . RADIANTLAST .
      OHIFNEWFIRST . $study_iuid . OHIFNEWLAST .
      $editworkload;
    // TELEDOKTERPENGIRIMFIRST . $study_iuid . TELEDOKTERPENGIRIMLAST .
    // TELEGRAMSIGNATUREFIRST . $study_iuid . TELEGRAMSIGNATURELAST;
    // ketika login radiographer
  } else if ($level == 'radiographer') {
    // kondisi ketika xray_workload masuk dari trigger
    if ($status != '-') {
      $level = $level = '<h6 class="dropdown-title1">Viewer</h6>' .
        OHIFNEWFIRST . $study_iuid . OHIFNEWLAST .
        HTMLFIRST . $study_iuid . HTMLLAST .
        '<h6 class="dropdown-title1"> Patient</h6>' .
        EDITPASIENFIRST . $study_iuid . EDITPASIENLAST . $icon_edit_pasien . EDITPASIENVERYLAST .
        CHANGEDOCTORFIRST . "'$study_iuid', '$dokradid', '$workload_status'" . CHANGEDOCTORLAST . $icon_change_doctor . CHANGEDOCTORCLASS  . CHANGEDOCTORVERYLAST .
        CHANGEEXPDATEFIRST . $study_iuid . CHANGEEXPDATELAST .
        LINKOHIFFIRST . EXTLINKOHIF . $addonlinkohif . $row['study_iuid'] . EXTLINKOHIF . LINKOHIFLAST .
        CHOOSESERIESFIRST . $study_iuid . CHOOSESERIESLAST .
        PUSHNOTIFICATIONFIRST . $study_iuid . PUSHNOTIFICATIONLAST .
        // COPYUIDFIRST . EXTLINKOHIF . $row['study_iuid'] . EXTLINKOHIF . COPYUIDLAST
        SENDDICOMFIRST . $study_iuid . SENDDICOMLAST;
      // GETEXPERTISEFIRST . $name_envelope . ' ' . defaultValueDateTime($created_at_envelope) . $href_get_expertise . GETEXPERTISELAST . $icon_get_expertise . GETEXPERTISEVERYLAST;
      // TELEDOKTERPENGIRIMFIRST . $study_iuid . TELEDOKTERPENGIRIMLAST;
      // DELETEFIRST . $study_iuid . DELETELAST;

    } else {
      // kondisi ketika xray_workload tidak masuk dari trigger
      $level = OHIFNEWFIRST . $study_iuid . OHIFNEWLAST;
    }
    // ketika login refferal
  } else if ($level == 'refferal') {
    $level = OHIFNEWFIRST . $study_iuid . OHIFNEWLAST .
      HTMLFIRST . $study_iuid . HTMLLAST;
  } else {
    $level = '-';
  }

  // kondisi ketika data dari simrs
  if ($fromorder == 'SIMRS' || $fromorder == 'simrs') {
    $badge = SIMRS;
  } else {
    $badge = '';
  }

  // kondisi jika prioriry normal dan CITO
  if ($priority == 'Normal' || $priority == 'NORMAL' || $priority == 'normal') {
    $priority_style = PRIORITYNORMAL;
  } else if ($priority == 'Cito' || $priority == 'CITO' || $priority == 'cito') {
    $priority_style = PRIORITYCITO;
  } else {
    $priority_style = '';
  }

  // kondisi mencari ditabel dokter radiology
  $row_dokrad = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT CONCAT(xray_dokter_radiology.dokrad_name,' ',xray_dokter_radiology.dokrad_lastname) AS dokrad_fullname
      FROM $table_dokter_radiology 
      WHERE pk = '$pk_dokter_radiology'"
  ));

  if ($row['status'] == 'waiting' || $row['status'] == '') {
    // jika status waiting kalo ada dokradid di xray_order tampilkan di xray_order
    $dokrad_name = defaultValue($row['dokrad_name']);
  } else if ($row['status'] == 'approved') {
    // jika status approved ambil data dari pk_dokter_radiology tabel xray_dokter_radiology
    $dokrad_name = defaultValue($row_dokrad['dokrad_fullname']);
  }

  $sub_array = array();
  $sub_array[] = $i;
  $sub_array[] = '<div class="dropdown custom-dropdown1 dropright">
	<button class="btn filter-btn2 dropdown-toggle" type="button" id="dropdownMenuButton1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
Action</button><div class="dropdown-menu dropdown-menu-right dropdown-menu1" aria-labelledby="dropdownMenuButton1">' . PDFFIRST . $study_iuid . PDFLAST . $level . '</div>';
  $sub_array[] = $status . '&nbsp;' . $badge;
  $sub_array[] = '<div style="text-align: start; padding-left: 5px;">' . $detail . '&nbsp;' . $priority_style . '</div>';
  $sub_array[] = $pat_id;
  $sub_array[] = $study_datetime;
  $sub_array[] = $no_foto;
  $sub_array[] = $pat_birthdate;
  $sub_array[] = $pat_sex;
  $sub_array[] = mb_convert_encoding($prosedur, 'UTF-8', 'ISO-8859-1');
  $sub_array[] = READMORESERIESFIRST . $study_iuid . READMORESERIESLAST;
  $sub_array[] = $mods_in_study;
  $sub_array[] = mb_convert_encoding($named, 'UTF-8', 'ISO-8859-1');
  $sub_array[] = mb_convert_encoding($name_dep, 'UTF-8', 'ISO-8859-1');
  $sub_array[] = mb_convert_encoding($dokrad_name, 'UTF-8', 'ISO-8859-1');;
  $sub_array[] = READMORERADIOGRAPHERFIRST . $study_iuid . READMORERADIOGRAPHERLAST;
  $sub_array[] = $approved_at;
  $sub_array[] = $spendtime;
  $sub_array[]  = $i++;
  $data[] = $sub_array;
}

function get_all_data($val_con, $val_query)
{
  $result = mysqli_query($val_con, $val_query);
  return mysqli_num_rows($result);
}

$output = array(
  "draw"    => intval($_POST["draw"]),
  "recordsTotal"  =>  get_all_data($conn_pacsio, $query_base . $kondisi),
  "recordsFiltered" => $number_filter_row,
  "data"    => $data
);

echo json_encode($output);

mysqli_close($conn);
