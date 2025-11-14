<?php
require '../koneksi/koneksi.php';
session_start();

// --------------------------------

if ($_SESSION['level'] == "radiographer") {
?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html>

    <head>
        <title>Notification radiology result to patient</title>
        <?php include('head.php'); ?>

        <meta http-equiv="refresh" content="500" />
    </head>

    <body>
        <?php include('../sidebar-index.php'); ?>
        <div class="container-fluid" id="content2">
            <div class="row">
                <?php include('../view-push-notification-index.php'); ?>
            </div>
        </div>
        <br><br>

        <?php include('script-footer.php'); ?>
        <script>
            $(document).ready(function() {
                $("li[data-target='#service']").addClass("active");
                $("ul[id='service'] li[id='workload1']").addClass("active");
                $("li[data-target='#service'] a i").css('color', '#bdbdbd');
            });
        </script>
        <script src="../js/view-push-notification.js?v=123"></script>
        <script>
            $('#phone').keyup(function() {
                if (this.value == 0) {
                    this.value = ''
                } else if (this.value == 62) {
                    this.value = 0
                }
            })
        </script>
    </body>

    </html>
<?php } else {
    header("location:../index.php");
} ?>