<?php

require '../js/proses/function.php';
require '../model/query-base-dokter-radiology.php';

session_start();
$uid = $_GET['uid'];
$dokradid = $_GET['dokradid'];
$status = $_GET['status'];

$query_changedoctor = mysqli_query(
	$conn,
	"SELECT $select_dokter_radiology 
	FROM $table_dokter_radiology WHERE dokradid NOT LIKE '$dokradid' AND username NOT IN ('hardian_dokter', 'sarah', 'drdemo')"
);


if (isset($_POST["submit"])) {
	if ($_POST['status'] == 'waiting') {
		if (ubahdokterworklist($_POST)) {
			echo "<script type='text/javascript'>
				setTimeout(function () { 
				swal({
						title: 'Berhasil diubah!',
						text:  '',
						icon: 'success',
						timer: 1000,
						showConfirmButton: true
					});  
				},10); 
				window.setTimeout(function(){ 
				window.location.replace('dicom.php');
				} ,1000); 
			</script>";
		} else {
			echo "<script type='text/javascript'>
			setTimeout(function () { 
			swal({
					title: 'Gagal Diubah!',
					text:  '',
					icon: 'error',
					timer: 1000,
					showConfirmButton: true
				});  
			},10); 
			window.setTimeout(function(){ 
			window.location.replace('changedoctorworklist.php?dokradid=$dokradid');
			} ,1000); 
		</script>";
		}
	} else {
		if (ubahdokterworkload($_POST)) {
			echo "<script type='text/javascript'>
				setTimeout(function () { 
				swal({
						title: 'Berhasil diubah!',
						text:  '',
						icon: 'success',
						timer: 1000,
						showConfirmButton: true
					});  
				},10); 
				window.setTimeout(function(){ 
				window.location.replace('dicom.php');
				} ,1000); 
			</script>";
		} else {
			echo "<script type='text/javascript'>
			setTimeout(function () { 
			swal({
					title: 'Gagal Diubah!',
					text:  '',
					icon: 'error',
					timer: 1000,
					showConfirmButton: true
				});  
			},10); 
			window.setTimeout(function(){ 
			window.location.replace('changedoctorworklist.php?dokradid=$dokradid');
			} ,1000); 
		</script>";
		}
	}
}

if ($_SESSION['level'] == "radiology" || $_SESSION['level'] == "radiographer") {
?>
	<!DOCTYPE html>
	<html lang="en">

	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Ubah</title>
		<script type="text/javascript" src="../js/sweetalert.min.js" />
		</script>
		<?php include('head.php'); ?>
	</head>

	<body style="background-color: #1f69b7;">
		<?php include('../sidebar-index.php'); ?>
		<div class="container-fluid" id="content2">
			<div class="row">
				<div id="content1">

					<style>
						.box-change-dokter {
							background-color: #1862b0;
							padding: 25px;
							border-radius: 10px;
							margin: 10px;
						}

						.radiobtn1 {
							position: relative;
							display: block;
						}

						.radiobtn1 label {
							display: block;
							background: #c3e2fe;
							color: #1862b0;
							border-radius: 5px;
							padding: 10px 20px;
							border: 2px solid #91d5fd;
							margin-bottom: 5px;
							cursor: pointer;
							font-weight: bold;
						}

						.radiobtn1 label:after,
						.radiobtn1 label:before {
							content: "";
							position: absolute;
							right: 11px;
							top: 11px;
							width: 20px;
							height: 20px;
							border-radius: 3px;
							background: #77befd;
						}

						.radiobtn1 label:before {
							background: transparent;
							transition: 0.1s width cubic-bezier(0.075, 0.82, 0.165, 1) 0s, 0.3s height cubic-bezier(0.075, 0.82, 0.165, 2) 0.1s;
							z-index: 2;
							overflow: hidden;
							background-repeat: no-repeat;
							background-size: 13px;
							background-position: center;
							width: 0;
							height: 0;
							background-image: url("../image/check.svg");
						}

						.radiobtn1 input[type="radio"] {
							display: none;
							position: absolute;
							width: 100%;
							appearance: none;
						}

						.radiobtn1 input[type="radio"]:checked+label {
							background: #fdcb77;
							animation-name: blink;
							animation-duration: 1s;
							border-color: #fcae2c;
						}

						.radiobtn1 input[type="radio"]:checked+label:after {
							background: #fcae2c;
						}

						.radiobtn1 input[type="radio"]:checked+label:before {
							width: 20px;
							height: 20px;
						}

						@keyframes blink {
							0% {
								background-color: #fdcb77;
							}

							10% {
								background-color: #fdcb77;
							}

							11% {
								background-color: #fdd591;
							}

							29% {
								background-color: #fdd591;
							}

							30% {
								background-color: #fdcb77;
							}

							50% {
								background-color: #fdd591;
							}

							45% {
								background-color: #fdcb77;
							}

							50% {
								background-color: #fdd591;
							}

							100% {
								background-color: #fdcb77;
							}
						}
					</style>

					<div class="d-flex justify-content-center align-items-center">
						<div class="col-md-6 box-change-dokter">
							<form action="" method="post">
								<input type="hidden" name="uid" value="<?= $uid ?>">
								<input type="hidden" name="status" value="<?= $status ?>">
								<?php while ($row_changedoctor = mysqli_fetch_assoc($query_changedoctor)) { ?>
									<div class="radiobtn1">
										<input type="radio" id="<?php echo $row_changedoctor['dokradid'] ?>" name="dokradid" value="<?= $row_changedoctor['dokradid'] ?>" required>
										<label for="<?php echo $row_changedoctor['dokradid'] ?>">
											<?= ucwords($row_changedoctor['dokrad_fullname']); ?>
										</label>
									</div>
								<?php } ?>
								<div class="radiobtn1">
									<input type="radio" id="0" name="dokradid" value="0">
									<label for="0">
										ALL
									</label>
								</div>
								<button type="submit" class="btn-worklist3 btn-lg" style="margin: 10px 0; float:right;" name="submit"><i class="fas fa-user-friends"></i> Change</button>
							</form>
						</div>
					</div>



					<!-- <div class="">
						<div class="about-inti col-md-6 col-md-offset-3" style="background-color: #f2f2f2;padding: 10px 85px;">
							<form action="" method="post">
								<?php while ($row = mysqli_fetch_assoc($query)) { ?>
									<input type="hidden" name="uid" value="<?= $uid ?>">
									<div class="custom-control custom-radio">
										<input type="radio" class="custom-control-input" id="<?php echo $row['dokradid'] ?>" name="dokradid" value="<?= $row['dokradid'] ?>" required>
										<label class="custom-control-label" for="<?php echo $row['dokradid'] ?>">
											&nbsp;
											<h3><?= ucwords($row['dokrad_fullname']); ?> </h3>
										</label>
									</div>
								<?php } ?>
								<input type="submit" class="btn btn-primary btn-lg" value="Pilih" name="submit">
						</div>
						</form>
					</div> -->
				</div>
			</div>
		</div>

		<?php include('script-footer.php'); ?>
	</body>

	</html>
<?php } else {
	header("location:../index.php");
} ?>