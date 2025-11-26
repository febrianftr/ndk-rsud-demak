<?php
require 'model/query-base-workload.php';
require 'model/query-base-study.php';

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

?>
<style type="text/css">
  .chart_index {
    background-color: #f7f7f7;
    padding: 10px;
    opacity: 0.9;
    border-radius: 0 0 5px 5px;
  }

  .footerindex {
    position: fixed;
  }
</style>
<meta http-equiv="refresh" content="600" />
<br>

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

<div class=" container-fluid ovrflow scroller-itwd">

  <div class="ovrflow">
    <table class="table-dicom" id="example" style="margin-top: 3px; width: 100px;" cellpadding="8" cellspacing="0">
      <thead class="thead1">
        <?php require 'thead.php'; ?>
      </thead>
    </table>
    <?php require 'modal.php'; ?>
  </div>
</div>
<div><br><br><br><br><br></div>


<script src="js/3.1.1/jquery.min.js"></script>
<script>
  $('document').ready(function() {
    var table = $('#example').dataTable({
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
  });
</script>