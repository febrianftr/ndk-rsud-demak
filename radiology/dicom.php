<?php
session_start();
require '../koneksi/koneksi.php';
require '../default-value.php';
require '../model/query-base-workload.php';
require '../model/query-base-order.php';
require '../model/query-base-study.php';
require '../model/query-base-patient.php';
require '../model/query-base-dokter-radiology.php';
$username = $_SESSION['username'];
// -----------------xray_exam2--------------

// kondisi jika ada di dicom.php
$row_dokrad = mysqli_fetch_assoc(mysqli_query(
	$conn,
	"SELECT dokradid 
    FROM $table_dokter_radiology 
    WHERE username = '$username'"
));
$dokradid = $row_dokrad['dokradid'];

$kondisi = "WHERE (xray_workload.status = 'waiting' AND xray_order.dokradid = '$dokradid' AND xray_order.priority = 'cito' AND study.study_datetime >= '2000-02-29')
OR (xray_workload.status = 'waiting' AND xray_order.dokradid IS NULL AND xray_order.priority = 'cito' AND study.study_datetime >= '2000-02-29')
ORDER BY xray_order.priority IS NULL, xray_order.priority ASC, study.study_datetime DESC 
LIMIT 3000";

$row = mysqli_fetch_assoc(mysqli_query(
	$conn_pacsio,
	"SELECT 
    COUNT(priority) AS jumlah_cito
    FROM $table_patient
    JOIN $table_study
    ON patient.pk = study.patient_fk
    JOIN $table_workload
    ON study.study_iuid = xray_workload.uid
    LEFT JOIN $table_order
    ON xray_order.uid = xray_workload.uid
    $kondisi"
));

$jumlah_cito = $row['jumlah_cito'];

if ($_SESSION['level'] == "radiology") {
?>
	<!DOCTYPE html>
	<html>

	<head>
		<meta http-equiv="refresh" content="300" />
		<title>Worklist | Radiology</title>
		<?php include('head.php'); ?>
	</head>

	<body>
		<?php include('../sidebar-index.php'); ?>
		<div class="container-fluid" id="content2">
			<div class="row">

				<div id="content1">
					<div class="body">

						<div class="container-fluid">
							<div class="col-12" style="padding: 0;">
								<nav aria-label="breadcrumb">
									<ol class="breadcrumb">
										<li class="breadcrumb-item"><a href="#">Home</a></li>
										<li class="breadcrumb-item active">Worklist</li>
									</ol>
								</nav>
							</div>
							<div class="table-view col-md-12 dashboard-home" style="overflow-x:auto;">
								<?php
								if ($jumlah_cito > 0) { ?>
									<div class="alert alert-danger text-center" style="font-size:large;" role="alert">
										<i class="fa fa-bell" aria-hidden="true"></i>
										<?= $jumlah_cito ?> Pasien Prioritas <i style="color: red;" class="fas fa-circle"></i> CITO
									</div>
								<?php } else { ?>
									<div class="alert alert-success text-center" role="alert">
										<i class="fa fa-bell" aria-hidden="true"></i>
										Semua Pasien Worklist Prioritas Normal
									</div>
								<?php } ?>

								<table class="table-dicom" id="example" border="1" cellpadding="8" cellspacing="0" style="margin-top: 3px; width: 2325px;">
									<thead class="thead1">
										<div class="input-group-sm" style="    margin-bottom: -8px;">
											<input type="text" class="form-control" style="width: 115px; float: right;" id="mrn" placeholder="search MRN">
											<input type="text" class="form-control" style="width: 115px; float: right; margin-right: 6px;" id="name" placeholder="search Name">
										</div>
										<?php include '../thead.php'; ?>
									</thead>
									<tbody>

									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
				<?php require '../modal.php'; ?>
			</div>
		</div>


		<?php include('script-footer.php'); ?>
		<script>
			$(document).ready(function() {
				$("li[id='worklist1']").addClass("active");
			});
		</script>
		<script>
			$(document).ready(function() {
				var table = $('#example').DataTable({
					"ajax": {
						"url": "../getAll.php",
						"dataSrc": ""
					},
					"columns": [{
							"data": "no"
						},
						{
							"data": "report"
						},
						{
							"data": "status"
						},
						{
							"data": "pat_name"
						},
						{
							"data": "mrn"
						},
						{
							"data": "study_datetime"
						},
						{
							"data": "no_foto"
						},
						{
							"data": "pat_birthdate"
						},
						{
							"data": "pat_sex"
						},
						{
							"data": "study_desc"
						},
						{
							"data": "series_desc"
						},
						{
							"data": "mods_in_study"
						},
						{
							"data": "named"
						},
						{
							"data": "name_dep"
						},
						{
							"data": "dokrad_name"
						},
						{
							"data": "radiographer_name"
						},
						{
							"data": "approve_date"
						},
						{
							"data": "spendtime"
						}
					]
				});
				$('#mrn').on('keyup', function() {
					table
						.columns(4)
						.search(this.value)
						.draw();
				});
				$('#name').on('keyup', function() {
					table
						.columns(3)
						.search(this.value)
						.draw();
				});
			});
		</script>

		<script>
			const observer = new MutationObserver(() => {

				// ambil semua icon draft-action yang muncul
				const drafts = document.querySelectorAll(".draft-action");

				drafts.forEach(draft => {

					// cari parent dropdown terdekat
					const dropdown = draft.closest(".dropdown");

					if (dropdown) {
						const btn = dropdown.querySelector(".filter-btn2");
						if (btn) {
							btn.style.backgroundColor = "#d54524";
						}
					}
				});
			});

			// observe seluruh body
			observer.observe(document.body, {
				childList: true,
				subtree: true
			});
		</script>
	</body>

	</html>
<?php } else {
	header("location:../index.php");
} ?>