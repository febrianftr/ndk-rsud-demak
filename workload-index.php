<?php
require '../koneksi/koneksi.php';
require 'default-value.php';
require '../model/query-base-workload.php';
require '../model/query-base-order.php';
require '../model/query-base-study.php';
require '../model/query-base-patient.php';

$level = $_SESSION['level'];
$http_referer = $_SERVER['HTTP_REFERER'] ?? '';
$explode = explode('/', $http_referer);
$queryphp = in_array("query.php", $explode);

$waitingCito1hour = mysqli_fetch_assoc(mysqli_query(
	$conn_pacsio,
	"SELECT 
	COUNT(*) AS jumlah
	FROM $table_patient
	JOIN $table_study 
	ON patient.pk = study.patient_fk 
	JOIN $table_workload
	ON study.study_iuid = xray_workload.uid 
	LEFT JOIN $table_order
	ON xray_order.uid = xray_workload.uid 
	WHERE status = 'waiting'
	AND study.study_datetime < DATE_SUB(NOW(), INTERVAL 1 HOUR)
	AND priority = 'cito'
	AND (contrast = 0 || contrast IS NULL)
	AND study.updated_time >= '2023-11-26'
	"
));
$moreThanCito1hour = $waitingCito1hour["jumlah"];

$waiting3hourReguler = mysqli_fetch_assoc(mysqli_query(
	$conn_pacsio,
	"SELECT 
	COUNT(*) AS jumlah
	FROM $table_patient
	JOIN $table_study 
	ON patient.pk = study.patient_fk 
	JOIN $table_workload
	ON study.study_iuid = xray_workload.uid 
	LEFT JOIN $table_order
	ON xray_order.uid = xray_workload.uid 
	WHERE status = 'waiting'
	AND study.study_datetime < DATE_SUB(NOW(), INTERVAL 3 HOUR)
	AND priority = 'normal'
	AND (contrast = 0 || contrast IS NULL)
	AND mods_in_study IN('CR', 'DX')
	AND study_desc_pacsio = 'THORAK'
	AND study.updated_time >= '2023-11-26'
	"
));
$moreThan3hourReguler = $waiting3hourReguler["jumlah"];

$waiting6hourContrast = mysqli_fetch_assoc(mysqli_query(
	$conn_pacsio,
	"SELECT 
	COUNT(*) AS jumlah
	FROM $table_patient
	JOIN $table_study 
	ON patient.pk = study.patient_fk 
	JOIN $table_workload
	ON study.study_iuid = xray_workload.uid 
	LEFT JOIN $table_order
	ON xray_order.uid = xray_workload.uid 
	WHERE status = 'waiting'
	AND study.study_datetime < DATE_SUB(NOW(), INTERVAL 6 HOUR)
	AND priority = 'normal'
	AND contrast = 1
	AND study.updated_time >= '2023-11-26'
	"
));
$moreThan6hourContrast = $waiting6hourContrast["jumlah"];

$waitingCT6hourReguler = mysqli_fetch_assoc(mysqli_query(
	$conn_pacsio,
	"SELECT 
	COUNT(*) AS jumlah
	FROM $table_patient
	JOIN $table_study 
	ON patient.pk = study.patient_fk 
	JOIN $table_workload
	ON study.study_iuid = xray_workload.uid 
	LEFT JOIN $table_order
	ON xray_order.uid = xray_workload.uid 
	WHERE status = 'waiting'
	AND study.study_datetime < DATE_SUB(NOW(), INTERVAL 6 HOUR)
	AND priority = 'normal'
	AND (contrast = 0 || contrast IS NULL)
	AND mods_in_study IN('CT','CT\\\\SR')
	AND study.updated_time >= '2023-11-26'
	"
));
$moreThanCT6hourReguler = $waitingCT6hourReguler["jumlah"];

$waitingUSG1hourReguler = mysqli_fetch_assoc(mysqli_query(
	$conn_pacsio,
	"SELECT 
	COUNT(*) AS jumlah
	FROM $table_patient
	JOIN $table_study 
	ON patient.pk = study.patient_fk 
	JOIN $table_workload
	ON study.study_iuid = xray_workload.uid 
	LEFT JOIN $table_order
	ON xray_order.uid = xray_workload.uid 
	WHERE status = 'waiting'
	AND study.study_datetime < DATE_SUB(NOW(), INTERVAL 1 HOUR)
	AND priority = 'normal' 
	AND (contrast = 0 || contrast IS NULL)
	AND mods_in_study IN('US') 
	AND study_desc NOT IN('USG DOPPLER') 
	AND study.updated_time >= '2023-11-26'
	"
));

$query = "SELECT COUNT(*) AS total
FROM $table_study
JOIN $table_workload
ON study.study_iuid = xray_workload.uid ";

// total studies
$total = mysqli_fetch_assoc(mysqli_query(
	$conn_pacsio,
	$query . 'WHERE DATE(study_datetime) = CURRENT_DATE()'
));

// total waiting
$waiting = mysqli_fetch_assoc(mysqli_query(
	$conn_pacsio,
	$query . 'WHERE DATE(study_datetime) = CURRENT_DATE() AND status = "waiting"'
));

// total approved
$approved = mysqli_fetch_assoc(mysqli_query(
	$conn_pacsio,
	$query . ' WHERE DATE(approved_at) = CURRENT_DATE() AND status = "approved"'
));
$moreThanUSG1hourReguler = $waitingUSG1hourReguler["jumlah"];

$waitingUSGDoppler2hourReguler = mysqli_fetch_assoc(mysqli_query(
	$conn_pacsio,
	"SELECT 
	COUNT(*) AS jumlah
	FROM $table_patient
	JOIN $table_study 
	ON patient.pk = study.patient_fk 
	JOIN $table_workload
	ON study.study_iuid = xray_workload.uid 
	LEFT JOIN $table_order
	ON xray_order.uid = xray_workload.uid 
	WHERE status = 'waiting'
	AND study.study_datetime < DATE_SUB(NOW(), INTERVAL 2 HOUR)
	AND priority = 'normal' 
	AND (contrast = 0 || contrast IS NULL)
	AND mods_in_study IN('US') 
	AND study_desc IN('USG DOPPLER') 
	AND study.updated_time >= '2023-11-26'
	"
));
$moreThanUSGDoppler2hourReguler = $waitingUSGDoppler2hourReguler["jumlah"];
?>

<div class="col-12" style="padding: 0;">
	<nav aria-label="breadcrumb">
		<ol class="breadcrumb">
			<li class="breadcrumb-item active"></li>
		</ol>
	</nav>
</div>
<div class="container-fluid mb-3">
	<div class="row">
		<div class="col-md-4">
			<div class="card like-card d-flex align-items-center justify-content-between mx-auto">
				<div class="like-left d-flex align-items-center">
					<img src="../image/new/users-nd.svg" style="width: 45px;">
					<span>Today Studies</span>
				</div>
				<div class="like-count"> <?= $total['total'] ?></div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card like-card d-flex align-items-center justify-content-between mx-auto">
				<div class="like-left d-flex align-items-center">
					<img src="../image/new/check-nd.svg" style="width: 30px; margin-right: 10px;">
					<span>Approved</span>
				</div>
				<div class="like-count"><?= $approved['total']; ?></div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card like-card d-flex align-items-center justify-content-between mx-auto">
				<div class="like-left d-flex align-items-center">
					<img src="../image/new/clock-nd.svg" style="width: 25px; margin-right: 10px;">
					<span>Waiting</span>
				</div>
				<div class="like-count"><?= $waiting['total']; ?></div>
			</div>
		</div>
	</div>
</div>

<!-- <div class="dropdown custom-dropdown1 dropright">
	<button class="btn filter-btn2 dropdown-toggle" type="button" id="dropdownMenuButton1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		Action
	</button>
	<div class="dropdown-menu dropdown-menu-right dropdown-menu1" aria-labelledby="dropdownMenuButton1">
		<h6 class="dropdown-title1">Expertise</h6>
		<a class="dropdown-item dropdown-item1" href="#"><i class="fas fa-file-alt"></i> PDF Expertise</a>
		<a class="dropdown-item dropdown-item1" href="#"><i class="fas fa-edit"></i> Edit Expertise</a>
		<div class="dropdown-divider"></div>
		<h6 class="dropdown-title1">Viewer</h6>
		<a class="dropdown-item dropdown-item1" href="#"><i class="fas fa-x-ray"></i>Viewer Radiant</a>
		<a class="dropdown-item dropdown-item1" href="#"><i class="fas fa-x-ray"></i>Viewer Inobitect</a>
		<a class="dropdown-item dropdown-item1" href="#"><i class="fas fa-x-ray"></i>Viewer Horos</a>
		<a class="dropdown-item dropdown-item1" href="#"><i class="fas fa-x-ray"></i>Viewer Web</a>
		<a class="dropdown-item dropdown-item1" href="#"><i class="fas fa-x-ray"></i>Viewer HTML</a>
		<div class="dropdown-divider"></div>
		<h6 class="dropdown-title1"> Patient</h6>
		<a class="dropdown-item dropdown-item1" href="#"><i class="fas fa-user-edit"></i> Edit Patient</a>
		<a class="dropdown-item dropdown-item1" href="#"><i class="fas fa-user-check"></i> Edit Patient</a>
		<a class="dropdown-item dropdown-item1" href="#"><i class="fas fa-user-nurse"></i> Choose phycisian</a>
		<a class="dropdown-item dropdown-item1" href="#"><i class="fas fa-history"></i>Edit Expired Date</a>
		<a class="dropdown-item dropdown-item1" href="#"><i class="fas fa-link"></i>Copy Link</a>
		<a class="dropdown-item dropdown-item1" href="#"><i class="far fa-list-alt"></i>Choose Series</a>
		<a class="dropdown-item dropdown-item1" href="#"><i class="fas fa-share-alt-square"></i>Send Image to..</a>
	</div>
</div> -->




<div class="table-view">
	<div class="col-md-12 table-box" style="overflow: scroll; position: relative; padding-top: 50px;  height: 300vh;">
		<?php require_once 'formsearch.php'; ?>
		<table class="table-dicom" id="purchase_order" style="width: 2400px;" cellpadding="8" cellspacing="0">
			<thead class="thead1">
				<?php require 'thead.php'; ?>
			</thead>
		</table>
	</div>
</div>
<?php require 'modal.php'; ?>
<script src="js/3.1.1/jquery.min.js"></script>
<script type="text/javascript" src="js/jquery.datetimepicker.full.js"></script>

<script>
	$('#from_study_datetime').datetimepicker({
		format: 'd-m-Y H:i',
		allowTimes: ['00:00',
			'01:00',
			'02:00',
			'03:00',
			'04:00',
			'05:00',
			'06:00',
			'07:00',
			'08:00',
			'09:00',
			'10:00',
			'11:00',
			'12:00',
			'13:00',
			'14:00',
			'15:00',
			'16:00',
			'17:00',
			'18:00',
			'19:00',
			'20:00',
			'21:00',
			'22:00',
			'23:00',
			'23:59'
		]
	});
	$('#to_study_datetime').datetimepicker({
		format: 'd-m-Y H:i',
		allowTimes: ['00:00',
			'01:00',
			'02:00',
			'03:00',
			'04:00',
			'05:00',
			'06:00',
			'07:00',
			'08:00',
			'09:00',
			'10:00',
			'11:00',
			'12:00',
			'13:00',
			'14:00',
			'15:00',
			'16:00',
			'17:00',
			'18:00',
			'19:00',
			'20:00',
			'21:00',
			'22:00',
			'23:00',
			'23:59'
		]
	});
</script>
<script>
	$(document).ready(function() {
		$(document).keypress(function(e) {
			var keycode = (e.keycode ? e.keycode : e.which);
			if (keycode == '13') {
				properties_data()
			}
		});

		$(document).on('click', '.cboxtombol', function() {
			$('.cbox').prop('checked', this.checked);
		});
		fetch_data('no');

		function fetch_data(is_date_search = 'yes', from_study_datetime = '', to_study_datetime = '', mods_in_study = '', pat_name = '', mrn = '', patientid = '', fill = '') {
			var dataTable = $('#purchase_order').DataTable({
				"processing": true,
				"serverSide": true,
				"order": [],
				"searching": false,
				"ajax": {
					url: "../prosescari-test.php",
					type: "POST",
					data: {
						is_date_search: is_date_search,
						from_study_datetime: from_study_datetime,
						to_study_datetime: to_study_datetime,
						mods_in_study: mods_in_study,
						pat_name: pat_name,
						mrn: mrn,
						patientid: patientid,
						fill: fill
					}
				},
			});
		}

		function properties_data() {
			var from_study_datetime = $('#from_study_datetime').val();
			var to_study_datetime = $('#to_study_datetime').val();
			var pat_name = $('#pat_name').val();
			var mrn = $('#mrn').val();
			var mods_in_study = get_filter('checkbox');
			var patientid = $('#patientid').val();
			var fill = $('#fill').val();
			if (from_study_datetime != '' && to_study_datetime != '') {
				$('#purchase_order').DataTable().destroy();
				fetch_data('yes', from_study_datetime, to_study_datetime, mods_in_study, pat_name, mrn, patientid, fill);
			} else {
				alert("Please Select Date");
			}
		}

		$('#range').click(function() {
			properties_data()
		});

		function get_filter(class_name) {
			var filter = [];
			$('#' + class_name + ':checked').each(function() {
				filter.push($(this).val());
			});
			return filter;
		}
		$('.common_selector').click(function() {
			$('#purchase_order');
		});
	});
</script>
<!-- ------------------hide search di tables--------------------- -->
<script>
	$(document).ready(function() {
		$(".dataTables_filter").hide();
	});
</script>
<!-- ----------------------hide search di tables------------------------ -->