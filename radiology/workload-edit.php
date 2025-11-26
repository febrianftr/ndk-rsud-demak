<?php
require 'function_radiology.php';
require '../viewer-all.php';
require '../default-value.php';
require '../model/query-base-study.php';
require '../model/query-base-patient.php';
require '../model/query-base-order.php';
require '../model/query-base-workload.php';
require '../model/query-base-template.php';
require '../model/query-base-selected-dokter-radiology.php';

session_start();
// menampilkan data xray exam
$uid = $_GET['uid'];
$username = $_SESSION['username'];

$file_function = explode('\\', __FILE__);
$file = end($file_function);

// kondisi jika mapping dokter diaktifkan
$selected_dokter_radiology = mysqli_fetch_assoc(mysqli_query(
	$conn,
	"SELECT $select_selected_dokter_radiology 
    FROM $table_selected_dokter_radiology"
));

$row = mysqli_fetch_assoc(mysqli_query(
	$conn,
	"SELECT $select_patient,
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
$pat_name = defaultValue($row['pat_name']);
$pat_sex = styleSex($row['pat_sex']);
$pat_birthdate = diffDate($row['pat_birthdate']);
$study_iuid = defaultValue($row['study_iuid']);
$study_datetime = defaultValueDateTime($row['study_datetime']);
$accession_no = defaultValue($row['accession_no']);
$ref_physician = defaultValue($row['ref_physician']);
$study_desc = defaultValue($row['study_desc']);
$mods_in_study = defaultValue($row['mods_in_study']);
$num_series = defaultValue($row['num_series']);
$num_instances = defaultValue($row['num_instances']);
$updated_time = defaultValueDateTime($row['updated_time']);
$pat_id = defaultValue($row['pat_id']);
$no_foto = defaultValue($row['no_foto']);
$address = defaultValue($row['address']);
$name_dep = defaultValue($row['name_dep']);
$named = defaultValue($row['named']);
$dokraid_order = $row['dokradid'];
$radiographer_name = defaultValue($row['radiographer_name']);
$dokrad_name = defaultValue($row['dokrad_name']);
$create_time = defaultValueDateTime($row['create_time']);
$pat_state = defaultValue($row['pat_state']);
$priority = defaultValue($row['priority']);
$priority_doctor = $row['priority_doctor'];
$spc_needs = defaultValue($row['spc_needs']);
$payment = defaultValue($row['payment']);
$fromorder = $row['fromorder'];
$status = styleStatus($row['status'], $study_iuid);
$fill = $row['fill'];
$approved_at = defaultValueDateTime($row['approved_at']);
$spendtime = spendTime($study_datetime, $approved_at, $row['status']);
$pk_dokter_radiology = $row['pk_dokter_radiology'];
$pk_study = $row['pk_study'];
$detail_uid = '<a href="#" class="hasil-all penawaran-a" data-id="' . $uid . '">' . removeCharacter($pat_name) . '</a>';

// query mencari berdasarkan pat_id (mrn)
$query_mrn = mysqli_query(
	$conn,
	"SELECT $select_patient,
	$select_study,
    $select_workload 
	FROM $table_patient
	JOIN $table_study
	ON patient.pk = study.patient_fk
	JOIN $table_workload
    ON study.study_iuid = xray_workload.uid 
	WHERE pat_id = '$row[pat_id]'
	AND study.study_iuid != '$uid'
	ORDER BY study.study_datetime DESC"
);

// query mencari berdasarkan username dokter
$dokter_radiologi = mysqli_fetch_assoc(mysqli_query(
	$conn,
	"SELECT dokradid, dokrad_name, dokrad_lastname FROM xray_dokter_radiology WHERE username = '$username'"
));
$dokradid = $dokter_radiologi['dokradid'];
$dokrad_fullname = $dokter_radiologi['dokrad_name'] . ' ' . $dokter_radiologi['dokrad_lastname'];

if (isset($_POST["save_template"])) {
	$insert = insert_template_workload($_POST);
	if ($insert) {
		echo "
			<script>
				alert('Report Telah Di Simpan ke template');
				document.location.href= 'workload-edit.php?uid=$uid&template_id=$insert';
			</script>";
	} else {
		echo "
			<script>
				alert('Report Gagal Di Simpan ke template');
				history.back();
			</script>";
	}
}
if (isset($_POST["save_edit"])) {
	if (update_workload($_POST)) {
		echo "
			<script>
				document.location.href= 'workload.php';
				win = window.open('pdf/expertise.php?uid=$uid', '_blank');
				win.focus();
				win.print();
			</script>";
	} else {
		echo "
			<script>
				alert('Report Gagal Di Simpan ke Draft');
				history.back();
			</script>";
	}
}

if ($_SESSION['level'] == "radiology") {
	if (($dokraid_order == $dokradid && $selected_dokter_radiology['is_active'] == 1) or
		($dokraid_order == $dokradid && $selected_dokter_radiology['is_active'] == 0) or
		($dokraid_order == null && $selected_dokter_radiology['is_active'] == 0)
	) { ?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">

		<head>
			<?php include('head.php'); ?>
			<title>Expertise | Radiology Physician</title>
			<script type="text/javascript" src="js/jquery1.10.2.js"></script>
		</head>
		<style>
			.fill {
				padding: 50px;
			}

			.card-custom {
				background-color: #2a2a2a;
				border: 1px solid #3a3a3a;
				border-radius: 10px;
				padding: 20px;
				box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
				max-width: 340px;
				margin: 5px;
			}

			.card-header-custom {
				font-size: 16px;
				font-weight: 600;
				color: #ffffff;
				border-bottom: 1px solid #3a3a3a;
				padding-bottom: 10px;
				margin-bottom: 15px;
			}

			.info-item {
				margin-bottom: 8px;
				font-size: 13px;
			}

			.info-label {
				color: #aaa;
				font-weight: 500;
				margin-right: 8px;
			}

			.btn-group-custom .btn {
				border-radius: 6px;
				min-width: 140px;
				font-size: 13px;
				padding: 10px;
			}

			.btn-pdf {
				background-color: #007bff;
				color: #fff;

			}

			.btn-pdf:hover {
				background-color: #0069d9;
			}

			.btn-image {
				background-color: #28a745;
				color: #fff;
			}

			.btn-image:hover {
				background-color: #218838;
			}
		</style>

		<body>
			<?php include('../sidebar-index.php');
			require '../modal.php'; ?>
			<div class="container-fluid" id="content2">
				<div class="row">
					<div id="content1">
						<div class="container-fluid">
							<div class="row">
								<div class="col-12" style="padding: 0;">
									<nav aria-label="breadcrumb">
										<ol class="breadcrumb">
											<li class="breadcrumb-item"><a href="#">Home</a></li>
											<li class="breadcrumb-item"><a href="dicom.php">Worklist</a></li>
											<li class="breadcrumb-item active">Expertise</li>
										</ol>
									</nav>
								</div>


								<div class="col-lg-6 mb-3 padding-rl-nd">
									<div class="table-box">
										<div class="info-container-nd">
											<div class="info-row-nd">
												<div class="info-col-nd">
													<div class="info-label-nd">Name</div>
													<div class="info-value-nd"><?= $detail_uid; ?></div>
												</div>
												<div class="info-col-nd">
													<div class="info-label-nd">MRN</div>
													<div class="info-value-nd"><?= $pat_id; ?></div>
												</div>
												<div class="info-col-nd">
													<div class="info-label-nd">Departmen</div>
													<div class="info-value-nd"><?= $name_dep; ?></div>
												</div>
											</div>

											<div class="info-row-nd">
												<div class="info-col-nd">
													<div class="info-label-nd">Age</div>
													<div class="info-value-nd"><?= $pat_birthdate; ?></div>
												</div>
												<div class="info-col-nd">
													<div class="info-label-nd">Special Needs</div>
													<div class="info-value-nd"><?= $spc_needs; ?></div>
												</div>
												<div class="info-col-nd">
													<div class="info-label-nd">Procedure</div>
													<div class="info-value-nd"><?= $prosedur; ?></div>
												</div>
											</div>

											<div class="info-row-nd" style="border-bottom: unset;">
												<div class="info-col-nd">
													<div class="info-label-nd">Study Date</div>
													<div class="info-value-nd"><?= $study_datetime; ?></div>
												</div>
												<div class="info-col-nd">
													<div class="info-label-nd">Refferal Physician</div>
													<div class="info-value-nd"><?= $named; ?></div>
												</div>
												<div class="info-col-nd">
													<div class="info-label-nd">Sex</div>
													<div class="info-value-nd"><?= $pat_sex; ?></div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-lg-4 mb-3 padding-rl-nd">
									<div class="table-box">
										<div class="container-fluid">
											<div class="row">
												<!-- <div class="col-lg-2">
													<div class="div-left">
														<div class="data-patient">
															<div class="content2-adm li-adm">
																<h4 style="margin: 0px;">Viewer</h4>
																<hr style="margin: 10px 0px;">
																<div class="buttons1">
																	<?php if ($username == "hardian_dokter") {
																		echo
																		DICOMNEWWORKLISTFIRST . $uid . DICOMNEWWORKLISTLAST .
																			RADIANTWORKLISTFIRST . $uid . RADIANTWORKLISTLAST .
																			OHIFNEWWORKLISTFIRST . $uid . OHIFNEWWORKLISTLAST;
																	} else {
																		echo
																		HOROSWORKLISTFIRST . $uid . HOROSWORKLISTLAST .
																			RADIANTWORKLISTFIRST . $uid . RADIANTWORKLISTLAST .
																			OHIFNEWWORKLISTFIRST . $uid . OHIFNEWWORKLISTLAST;
																	} ?>
																</div>
															</div>
														</div>
													</div>
												</div> -->
												<div class="col-md-12"><label>Viewer :</label></div>
												<div class="col-sm-6">
													<!-- <a href="<?= LINKHOROSFIRST . $study_iuid . LINKHOROSLAST; ?>" class="btn-viewer-nd btn-ohif-nd mb-2">
														<img src="../image/new/horos.png" style="width: 20px">
														<p class="text-viewer-nd">Horos</p>
													</a> -->
													<a href="<?= LINKRADIANTFIRST . $study_iuid . LINKRADIANTLAST; ?>" target="_blank" class="btn-viewer-nd btn-radiant-nd mb-2">
														<img src="../image/radiAnt.png" style="width: 20px">
														<p class="text-viewer-nd">Radiant</p>
													</a>
													<!-- <a class="btn-viewer-nd btn-ino-nd mb-2" onclick="<?= LINKINOBITECFIRST . "'" . $study_iuid . "'" . LINKINOBITECLAST; ?>">
														<img src="../image/new/inobitec.png" style="width: 20px">
														<p class="text-viewer-nd">Inobitech</p>
													</a> -->
													<a href="<?= LINKOHIFNEWFIRST . $study_iuid . LINKOHIFNEWLAST; ?>" class="btn-viewer-nd btn-ohif-nd mb-2">
														<img src="../image/new/ohif-nd.svg" style="width: 20px">
														<p class="text-viewer-nd">Web</p>
													</a>
												</div>
												<div class="col-sm-6">

													<a href="<?= LINKHTMLFIRST . $study_iuid . LINKHTMLLAST; ?>" class="btn-viewer-nd btn-html-nd mb-2">
														<img src="../image/new/html-nd.svg" style="width: 20px">
														<p class="text-viewer-nd">HTML</p>
													</a>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-lg-2 mb-3 padding-rl-nd">
									<div class="table-box">
										<div class="col-md-12">
											<label>Information Patient :</label>
											<div class="radio-group">
												<input type="radio" class="radio-input" id="normal" name="priority_doctor" value="normal" checked required>
												<label class="radio-label-nd" for="normal">
													<span class="radio-inner-circle"></span>
													Normal
												</label>

												<input type="radio" class="radio-input" id="cito" name="priority_doctor" value="cito" required>
												<label class="radio-label-nd" for="cito">
													<span class="radio-inner-circle"></span>
													Cito
												</label>
											</div>
										</div>
									</div>
								</div>
								<div class="col-md-9 padding-rl-nd">
									<div class="table-box">
										<!-- <div class="collapse" id="ohif"> -->
										<iframe src="<?= "$urlnew$uid" ?>" frameborder="0" marginheight="0" marginwidth="0" width="100%" height="670px"></iframe>
										<!-- </div> -->
										<br>
										<!-- history pasien berdasarkan mrn pat_iid-->
										<b class="title-history">History Patient</b><br>
										<div class="data-order">
											<?php
											$i = 1;
											while ($mrn = mysqli_fetch_assoc($query_mrn)) {
												$study_iuid = $mrn['study_iuid'];
												$detail_mrn = '<a href="#" class="hasil-all penawaran-a" data-id="' . $study_iuid . '">' . removeCharacter($pat_name) . '</a>';
											?>
												<div class="card-custom">
													<div class="card-header-custom">
														<?= defaultValue($mrn['prosedur']); ?>
													</div>

													<div class="info-item">
														<span class="info-label">Date:</span> <?= defaultValueDateTime($mrn['study_datetime']); ?>
													</div>
													<div class="info-item">
														<span class="info-label">Name:</span> <?= $detail_mrn . ' ' . styleStatus($mrn['status'], $study_iuid); ?>
													</div>
													<div class="info-item">
														<span class="info-label">MRN:</span> <?= $pat_id; ?>
													</div>

													<hr style="border-color: #3a3a3a;">

													<div class="btn-group-custom d-flex justify-content-between">
														<a href="<?= LINKPDFFIRST . $study_iuid . LINKPDFLAST; ?>" class="btn btn-pdf">
															<i class="fas fa-file-pdf"></i> Expertise
														</a>
														<a href="<?= LINKOHIFNEWFIRST . $study_iuid . LINKOHIFNEWLAST; ?>" class="btn btn-image">
															<i class="fas fa-image"></i> Web Viewer
														</a>
													</div>
												</div>

												<!-- <table>
													<tbody>
														<p class="text-center"><?= $i; ?></p>
														<tr>
															<td><span class="table-left">Name</span></td>
														</tr>
														<tr>
															<td><?= $detail_mrn . ' ' . styleStatus($mrn['status'], $study_iuid); ?></td>
														</tr>
														<tr>
															<td><span class="table-left">MRN</span></td>
														</tr>
														<tr>
															<td><?= $pat_id; ?></td>
														</tr>
														<tr>
															<td><span class="table-left">Pemeriksaan</span></td>
														</tr>
														<tr>
															<td><?= defaultValue($mrn['prosedur']); ?></td>
														</tr>
														<tr>
															<td><span class="table-left">Waktu Pemeriksaan</span></td>
														</tr>
														<tr>
															<td><strong class="text-center"><?= defaultValueDateTime($mrn['study_datetime']); ?></strong></td>
														</tr>
														<tr>
															<td>
																<?= PDFFIRST . $study_iuid . PDFLAST .
																	HOROSFIRST . $study_iuid . HOROSLAST .
																	RADIANTFIRST . $study_iuid . RADIANTLAST .
																	OHIFNEWFIRST . $study_iuid . OHIFNEWLAST;
																?>
																<a href="#" class="view-history-expertise" data-id="<?= $study_iuid;  ?>">
																	<i data-toggle="tooltip" title="View History Expertise" class="fa fa-file-archive-o fa-lg"></i>
																</a>
															</td>
														</tr>
													</tbody>
												</table> -->
											<?php $i++;
											} ?>
										</div>
									</div>
								</div>
								<div class="col-lg-3 mb-3 padding-rl-nd">
									<div class="table-box">
										<form action="" method="post">
											<div class="padding-rl-less">
												<div class="container-fluid padding-rl-less mt-2">
													<div class="row">
														<div class="col-sm-4 pr-0">
															<button class="btn btn-worklist-nd btn-apr-nd m-0" id="save_edit" name="save_approve"><i class="fas fa-check-square"></i> Approve</button>
														</div>
														<div class="col-sm-4 pr-0">
															<!-- Button to Open the Modal -->
															<button class="btn btn-worklist-nd btn-work-nd m-0" type="button" data-toggle="modal" data-target="#modal-insert-template"><i class="fas fa-file-export"></i> Save Template
															</button>
														</div>
														<div class="col-sm-4">
															<button class="btn btn-worklist-nd btn-work-nd m-0" id="save_draft" name="save_draft" onclick="return confirm('Are you sure save draft?');"><i class="fas fa-save"></i> Save Draft</button>
														</div>
													</div>
												</div>
												<div class="">
													<div class="work-patient6">
														<input type="hidden" name="uid" value="<?= $uid; ?>">
														<input type="hidden" name="username" value="<?= $username; ?>">
														<?php
														@$template_id = $_GET['template_id'];
														$template = mysqli_fetch_assoc(mysqli_query(
															$conn,
															"SELECT $select_template 
												FROM $table_template
												WHERE template_id = '$template_id'"
														));
														if ($template_id == "") {
															$fill = $row['fill'];
														} else {
															$fill = $template['fill'];
														}
														?>
														<br>
														<div class="textarea-ckeditor" style="border: none;">
															<textarea class="ckeditor" name="fill" id="ckeditor">
														<?= $fill; ?>
													</textarea>
														</div>
														<div class="kotak">
															<!---POP UP -->
															<div class="container">
																<!-- Modal -->
																<div class="modal fade" id="modal-insert-template" role="dialog">
																	<div class="modal-dialog">
																		<div class="modal-content">
																			<div class="modal-header">
																				<h4 class="modal-title">Insert Title</h4><br />
																				<button type="button" class="close" data-dismiss="modal">&times;</button>
																			</div>
																			<div class="modal-body-template" style="padding: 10px;">
																				<input class="form-control" type="text" name="title" value="" placeholder="Insert Tittle">
																			</div>
																			<div class="modal-footer">
																				<button type="button" class="btn btn-close" data-dismiss="modal">Close</button>
																				<button style="border-radius: 5px; font-weight: bold; margin-bottom:4px;" class=" btn btn-success" id="save_template" name="save_template">Save</button>
																			</div>
																		</div>
																	</div>
																</div>
															</div>
														</div>
														<!-- END OF POP UP -->
													</div>
												</div>
											</div>
										</form>
										<div class="template-normal-nd">
											<input type="text" class="form-control" placeholder="search by tittle.. " id="myInput" style="margin: 0 0 7px 0; width: 100%;">
											<div class="template-save" id="container-template">
												<!-- <div id="content"></div> -->

												<table border="1" id="mytemplate" class="type-choice mytemplate" style="width: 100%; background: #363636;">
													<?php
													$query_template = mysqli_query(
														$conn,
														"SELECT $select_template 
												FROM $table_template 
												WHERE username = '$username'"
													);
													while ($template = mysqli_fetch_assoc($query_template)) { ?>
														<thead class="myTable">
															<td class="td1">
																<a class="template_name" data-template-id="<?= $template['template_id']; ?>" value="<?= $template['fill']; ?>" href="<?= $file; ?>?uid=<?= $uid; ?>&template_id=<?= $template['template_id']; ?>"><?= $template['title']; ?></a>
															</td>
															<td style="text-align: center;">
																<a href="#" class="view-template" data-id="<?= $template['template_id'];  ?>">
																	<i data-toggle="tooltip" title="View Template" class="fas fa-eye fa-lg"></i>
																</a>
															</td>
															<td style="text-align: center;">
																<a href="hapustemplate.php?uid=<?= $uid; ?>&template_id=<?= $template['template_id']; ?>&halaman=worklist" data-id="<?= $template['template_id'];  ?>" onclick="return confirm('Teruskan Menghapus Data?');">
																	<i data-toggle="tooltip" title="Delete Template" class="fas fa-trash fa-lg"></i>
																</a>
															</td>
														<?php } ?>
														</thead>
												</table>
											</div>
										</div>
									</div>
								</div>

							</div>
						</div>
					</div>
					<!-- Modal -->
					<div class="modal fade" id="view-template" role="dialog">
						<div class="modal-dialog modal-lg">
							<!-- Modal content-->
							<div class="modal-content">
								<div class="modal-header">
									<h4 class="modal-title">Report</h4>
									<button type="button" class="close" data-dismiss="modal">&times;</button>
								</div>
								<div class="modal-body">
									<textarea style="width: 100%; height: 320px;"><?= $template['template_id'];  ?></textarea>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
								</div>
							</div>
						</div>
					</div>
					<!-- Modal -->
				</div>
			</div>

			<!-- SCRIPT -->
			<?php include('script-footer.php'); ?>
			<script>
				$(document).ready(function() {
					$("li[data-target='#service']").addClass("active");
					$("ul[id='service'] li[id='workload1']").addClass("active");
				});
			</script>
			<script>
				// untuk menampilkan data popup
				$(function() {
					$(document).on('click', '.view-template', function(e) {
						e.preventDefault();
						$("#view-template").modal('show');
						$.post('hasil-template.php', {
								template_id: $(this).attr('data-id')
							},
							function(html) {
								$(".modal-body").html(html);
							}
						);
					});
				});
				// end untuk menampilkan data popup
			</script>
			<script>
				// copy template normal tanpa refresh
				$(".template_name").off("click").on("click", function(e) {
					e.preventDefault();
					let fill = $(this).attr("value");
					let template_id = $(this).data("template-id");

					CKEDITOR.instances['ckeditor'].setData(fill);

					const currentUrl = new URL(window.location.href);

					const params = currentUrl.searchParams;
					let newUrl = params.set("template_id", template_id);
					window.history.pushState(null, null, currentUrl.toString());
				});

				CKEDITOR.replace('ckeditor', {
					enterMode: CKEDITOR.ENTER_BR
				});
			</script>
			<script>
				var save = false;
				$('#save_edit').click(function() {
					save = true;
				});

				$('#save_template').click(function() {
					save = true;
				});

				// ketika dokter input 1 kata, dan close browser atau pindah halaman akan muncul pop up.
				$(document).ready(function() {
					CKEDITOR.instances['ckeditor'].on('change', function(e) {
						var fill = CKEDITOR.instances['ckeditor'].getData();

						window.addEventListener('beforeunload', function(e) {
							if (fill !== '' && save !== true) {
								e.preventDefault();
								e.returnValue = '';
							}
						});
					});
				});
			</script>
			<!-- -------------------javascript select template-------------- -->
			<script>
				$(document).ready(function() {
					$(".type-choice").show();
				});
				$(function() {
					$('#selector1').change(function() {
						$('.type-choice').hide();
						$('#' + $(this).val()).show();
					});
				});
			</script>
			<!-- -------------------javascript select temlate-------------- -->
			<script>
				// $(document).ready(function() {
				// 	$(".data-order").hide();
				// 	$(".work-patient").css("background", "#68b399");
				// 	$(".work-order").css("background", "#f1f1f1");
				// 	$(".work-patient a").css("color", "#fff");
				// 	$(".work-order a").css("color", "#68b399");
				// 	$(".button-work-order").click(function() {
				// 		$(".work-order").css("background", "#68b399");
				// 		$(".work-patient").css("background", "#f1f1f1");
				// 		$(".work-order a").css("color", "#fff");
				// 		$(".work-patient a").css("color", "#68b399");
				// 		$(".data-order").show();
				// 		$(".data-patient").hide();
				// 	});
				// });
				// $(document).ready(function() {
				// 	$(".button-work-patient").click(function() {
				// 		$(".work-patient").css("background", "#68b399");
				// 		$(".work-order").css("background", "#f1f1f1");
				// 		$(".work-patient a").css("color", "#fff");
				// 		$(".work-order a").css("color", "#68b399");
				// 		$(".data-patient").show();
				// 		$(".data-order").hide();
				// 	});
				// });
			</script>
			<script>
				$(document).ready(function() {
					$(".dokteravail").toggle();
					$(".btn-info").click(function() {
						$(".dokteravail").hide();
					});
				});
			</script>
		</body>

		</html>
<?php } else {
		echo "<script>
				alert('Bukan pasien dokter $dokrad_fullname');
				document.location.href= 'dicom.php';
			</script>";
	}
} else {
	header("location:../index.php");
} ?>