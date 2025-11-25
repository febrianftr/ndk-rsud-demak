<?php

$hostname = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM xray_hostname_publik"));

// PDF
define('PDFFIRST', '<a class="dropdown-item dropdown-item1"  href="../radiology/pdf/expertise.php?uid=');
define('PDFLAST', '"target="_blank"><i class="fas fa-file-alt"></i> PDF Expertise</a>');

define('LINKPDFFIRST', '../radiology/pdf/expertise.php?uid=');
define('LINKPDFLAST', '"target="_blank');

// DICOM
define('DICOMFIRST', '<a style="text-decoration:none;" href="jnlp://' . $_SERVER['SERVER_NAME'] . ':19898/weasis-pacs-connector/DCM_viewer.jnlp?studyUID=');
define('DICOMLAST', '"><span class="btn rgba-stylish-slight btn-inti2" style="box-shadow: none;"><img src="../image/eyered.svg" data-toggle="tooltip" title="Dicom Viewer" style="width: 100%;"></span></a>');

// DICOM NEW
define('DICOMNEWFIRST', '<a style="text-decoration:none;" href="http://' . $_SERVER['SERVER_NAME'] . ':9090/weasis-pacs-connector/IHEInvokeImageDisplay?studyUID=');
define('DICOMNEWLAST', '"><span class="btn rgba-stylish-slight btn-inti2" style="box-shadow: none;"><img src="../image/eyered.svg" data-toggle="tooltip" title="Dicom Viewer" style="width: 100%;"></span></a>');

// HTML
if ($_SERVER['SERVER_NAME'] == $hostname['ip_publik'] or $_SERVER['SERVER_NAME'] == '49.128.176.141') {
    define('HTMLFIRST', '<a class="dropdown-item dropdown-item1" href="http://' . $_SERVER['SERVER_NAME'] . ':20001/holan/viewer.html?studyUID=');
    define('LINKHTMLFIRST', 'http://' . $_SERVER['SERVER_NAME'] . ':20001/holan/viewer.html?studyUID=');
} else {
    define('HTMLFIRST', '<a class="dropdown-item dropdown-item1" href="http://' . $_SERVER['SERVER_NAME'] . ':19898/holan/viewer.html?studyUID=');
    define('LINKHTMLFIRST', 'http://' . $_SERVER['SERVER_NAME'] . ':19898/holan/viewer.html?studyUID=');
}
define('HTMLLAST', '"target="_blank"><i class="fas fa-x-ray"></i>Viewer HTML</a>');
define('LINKHTMLLAST', '"target="_blank');

// Mobile
define('MOBILEFIRST', '<a style="text-decoration:none;" class="ahref-edit" href="http://' . $_SERVER['SERVER_NAME'] . ':19898/dwv-viewer/index.html?type=manifest&input=%2Fweasis-pacs-connector%2Fmanifest%3FseriesUID%3D');
define('MOBILELAST', '"target="_blank"><span class="btn btn-warning btn-inti"><i class="fas fa-eye" data-toggle="tooltip" title="Web Viewer"></i></span></a>');

// Change doctor
define('CHANGEDOCTORICONYES', '<i class="fas fa-user-nurse"></i>');
define('CHANGEDOCTORICONNO', '<i class="fas fa-user-nurse text-danger"></i>');
define('CHANGEDOCTORFIRST', '<a class="dropdown-item dropdown-item1" href="#" onclick="changeDoctorApproved(event, ');
define('CHANGEDOCTORLAST', ')">');
define('CHANGEDOCTORCLASS', 'Choose phycisian');
define('CHANGEDOCTORVERYLAST', 'Change Radiologist</a>');

// Ambil hasil expertise
define('GETEXPERTISEICONYES', '<img src="../image/new/envelop.svg" data-toggle="tooltip" style="width: 100%;">');
define('GETEXPERTISEICONNO', '<i class="fas fa-envelope-open fa-lg deep-orange-text"></i>');
define('GETEXPERTISEICONWAITING', '<img src="../image/new/envelop-not.svg" data-toggle="tooltip" class="not-allowed" style="width: 100%;">');
define('GETEXPERTISEFIRST', '<a style="text-decoration: none;" title="');
define('GETEXPERTISEHREFYES', '" href="take-envelope.php?uid=');
define('GETEXPERTISEHREFNO', '" href="#');
define('GETEXPERTISELAST', '"><span class="btn rgba-stylish-slight darken-1 btn-inti2">');
define('GETEXPERTISEVERYLAST', '</span></a>');

// Update Exp Date QR
define('CHANGEEXPDATEFIRST', '<a class="dropdown-item dropdown-item1" href="change_expdate.php?uid=');
define('CHANGEEXPDATELAST', '"><i class="fas fa-history"></i>Edit Expired Date</a>');

// DELETE
define('DELETEFIRST', '<a style="text-decoration:none;" class="ahref-edit" href="deleteworkload.php?uid=');
define('DELETELAST', '"onclick=\'return confirm("Delete data?");\'><span class="btn red lighten-1 btn-intiwid1"><i class="fas fa-trash-alt" data-toggle="tooltip" title="Delete"></i></span></a>');

// EDIT PASIEN
define('EDITPASIENICONYES', '<i class="fas fa-user-check">');
define('EDITPASIENICONNO', '<i class="fas fa-user-edit"></i>');
define('EDITPASIENFIRST', '<a class="dropdown-item dropdown-item1" href="update-workload.php?uid=');
define('EDITPASIENLAST', '">');
define('EDITPASIENVERYLAST', 'Edit Patient</a>');

// EDIT WORKLOAD
define('EDITWORKLOADFIRST', '<a class="dropdown-item dropdown-item1" href="workload-edit.php?uid=');
define('EDITWORKLOADLAST', '"><i class="far fa-edit"></i> Edit Expertise</a>');

// telegram dokter pengirim
define('TELEDOKTERPENGIRIMFIRST', '<a style="text-decoration: none;" href="../radiology/telenotif.php?uid=');
define('TELEDOKTERPENGIRIMLAST', '" target="_blank"><span class="btn deep-orange-text rgba-stylish-slight btn-inti2"><img src="../image/telegram2.svg" data-toggle="tooltip" title="Telegram" style="width: 100%;"></span></a>');

// telegram signature
define('TELEGRAMSIGNATUREFIRST', '<a style="text-decoration:none;" href="otp.php?uid=');
define('TELEGRAMSIGNATURELAST', '"><span class="btn text-secondary rgba-stylish-slight btn-inti2"><img src="../image/signature.svg" data-toggle="tooltip" title="Signature" style="width: 100%;"></span></a>');

// pop up read more series
define('READMORESERIESFIRST', '<a href="#" class="hasil-series penawaran-a" data-id="');
define('READMORESERIESLAST', '">Read More</a>');

// pop up read more radiographer
define('READMORERADIOGRAPHERFIRST', '<a href="#" class="hasil-radiographer penawaran-a" data-id="');
define('READMORERADIOGRAPHERLAST', '">Read More</a>');

//choose series
define('CHOOSESERIESFIRST', '<a class="dropdown-item dropdown-item1" href="view-choose-series.php?uid=');
define('CHOOSESERIESLAST', '"><i class="far fa-list-alt"></i>Choose Series</a>');

// integrasi simrs
define('SIMRS', '<i class="fas fa-exchange-alt text-info" style="font-size:0.5rem;" title="terintegrasi dengan SIMRS"></i>');

// priority NORMAL
define('PRIORITYNORMAL', '<i style="color: #2d2; font-size:0.4rem;" class="fas fa-circle"></i>');

// PIORITY CITO
define('PRIORITYCITO', '<i style="color: red; font-size:0.4rem;" class="fas fa-circle"></i>');

// PUSH NOTIFICATION send WA
define('PUSHNOTIFICATIONFIRST', '<a class="dropdown-item dropdown-item1" href="view-push-notification.php?uid=');
define('PUSHNOTIFICATIONLAST', '"><i class="fab fa-whatsapp"></i>Send WA</a>');

// WORKLIST DOKTER BELUM DIBACA
define('WORKLISTFIRST', '<a class="dropdown-item dropdown-item1" href="worklist.php?uid=');
define('WORKLISTLAST', '"><i class="fas fa-user-edit"></i> Go to Expertise</a>');

// DRAFT DOKTER 
define('DRAFTFIRST', '<a class="dropdown-item dropdown-item1" href="worklist.php?uid=');
define('DRAFTLAST', '"><i style="color: yellow;" class="fas fa-user-edit"></i> Draft Expertise</a>');


//radiant
define('RADIANTFIRST', '<a class="dropdown-item dropdown-item1" href="radiant://?n=paet&v=dcmPACS&n=pstv&v=0020000D&v=%22');
define('RADIANTLAST', '%22" "target="_blank"><i class="fas fa-x-ray"></i>Viewer Radiant</a>');

//LINK RAADIANT
define('LINKRADIANTFIRST', 'radiant://?n=paet&v=dcmPACS&n=pstv&v=0020000D&v=%22');
define('LINKRADIANTLAST', '%22');

//ipiview
define('IPIVIEWFIRST', '<a style="text-decoration:none;" class="ahref-edit" href="http://192.168.10.144:8089/ipiview/ipiview/html/start.html?StudyInstanceUID=');
define('IPIVIEWLAST', '" target="_blank"><span class="btn rgba-stylish-slight btn-inti2" style="box-shadow: none;"><img src="../image/eyeyellow.svg" data-toggle="tooltip" title="IPI Viewer" style="width: 100%;"></span></a>');

// send dicom
define('SENDDICOMFIRST', '<a class="dropdown-item dropdown-item1" href="view-send-dicom.php?uid=');
define('SENDDICOMLAST', '"><i class="fas fa-share-alt-square"></i>Send Image to..</a>');

// ino  bitec
define('INOBITECFIRST', '<a href="#" class="ahref-edit" onclick="inobitec(');
define('LINKINOBITECFIRST', 'inobitec(');
define('INOBITECLAST', ')"id="inobitec" data-ip="' . $_SERVER['SERVER_NAME'] . '"><span class="btn rgba-stylish-slight btn-inti2" style="box-shadow: none;"><img src="../image/inobitec.png" data-toggle="tooltip" title="Radiant Viewer" style="width: 72%;"></span></a>');
define('LINKINOBITECLAST', ')"id="inobitec" data-ip="' . $_SERVER['SERVER_NAME'] . '');

// HOROS

// Horos://?methodName=retrieve&serverName=PACS&then=open&retrieveOnlyIfNeeded=yes&filterKey=StudyInstanceUID&filterValue=
$horos = '<a href="Horos://?methodName=displayStudy&StudyInstanceUID=';
define('HOROSFIRST', "$horos");
define('HOROSLAST', '" class="dropdown-item dropdown-item1" target="_blank"><i class="fas fa-x-ray"></i>Viewer Horos</a>');

// LINK HOROS 
define('LINKHOROSFIRST', 'Horos://?methodName=displayStudy&StudyInstanceUID=');
define('LINKHOROSLAST', '"');


// untuk icon OHIF LARGE DI WORKLIST
$ohif_large = '"class="button8 delete1" target="_blank"><img src="../image/web.svg" style="width: 50px;"><br> <span> Web Viewer</span></a>';
// untuk icon OHIF small DI WORKLIST
$ohif_small = '"target="_blank" class="dropdown-item dropdown-item1" href="#"><i class="fas fa-x-ray"></i>Viewer Web</a>';

function ohifnewurl($port)
{
    return "http://$_SERVER[SERVER_NAME]:$port/viewer?StudyInstanceUIDs=";
}
// OHIF TERBARU
if ($_SERVER['SERVER_NAME'] == $hostname['ip_publik'] or $_SERVER['SERVER_NAME'] == '49.128.176.141') {
    // jika menggunakan ip publik
    $urlnew = ohifnewurl(92);
    define('OHIFNEWFIRST', '<a style="text-decoration:none;" class="dropdown-item dropdown-item1" href="' . $urlnew . '');
    define('OHIFNEWLAST', "$ohif_small");
    define('LINKOHIFNEWFIRST', 'http://' . $_SERVER['SERVER_NAME'] . ':92/viewer?StudyInstanceUIDs=');
    define('LINKOHIFNEWLAST', '"target="_blank');
    // jika menggunakan ohif baru icon(large)
    define('OHIFNEWWORKLISTFIRST', '<a href="' . $urlnew . '');
    define('OHIFNEWWORKLISTLAST', "$ohif_large");
} else {
    $urlnew = ohifnewurl(91);
    define('OHIFNEWFIRST', '<a style="text-decoration:none;" class="dropdown-item dropdown-item1" href="' . $urlnew . '');
    define('OHIFNEWLAST', "$ohif_small");
    define('LINKOHIFNEWFIRST', 'http://' . $_SERVER['SERVER_NAME'] . ':91/viewer?StudyInstanceUIDs=');
    define('LINKOHIFNEWLAST', '"target="_blank');
    // jika menggunakan ohif baru icon(large)
    define('OHIFNEWWORKLISTFIRST', '<a href="' . $urlnew . '');
    define('OHIFNEWWORKLISTLAST', "$ohif_large");
}

function ohifurl($port)
{
    if (isset($_SERVER['HTTPS'])) {
        $serverPort = 'https://';
    } else {
        $serverPort = 'http://';
    }
    return "$serverPort$_SERVER[SERVER_NAME]:$port/viewer/";
}
// OHIF LAMA
if ($_SERVER['SERVER_NAME'] == $hostname['ip_publik'] or $_SERVER['SERVER_NAME'] == '49.128.176.141') {
    // jika menggunakan ip publik
    // jika menggunakan ohif lama icon (small)
    $url = ohifurl(82);
    define('OHIFOLDFIRST', '<a href="' . $url . '');
    define('OHIFOLDLAST', "$ohif_small");
    // jika menggunakan ohif lama icon(large)
    define('OHIFOLDWORKLISTFIRST', '<a data-toggle="collapse" href="#ohif" role="button" aria-expanded="false" aria-controls="ohif');
    define('OHIFOLDWORKLISTLAST', "$ohif_large");
} else {
    // jika menggunakan ip lokal
    // jika menggunakan ohif lama icon (small)
    $url = ohifurl(81);
    define('OHIFOLDFIRST', '<a href="' . $url . '');
    define('OHIFOLDLAST', "$ohif_small");

    // jika menggunakan ohif lama icon(large)
    define('OHIFOLDWORKLISTFIRST', '<a data-toggle="collapse" href="#ohif" role="button" aria-expanded="false" aria-controls="ohif');
    define('OHIFOLDWORKLISTLAST', "$ohif_large");
}

// RENDER
define('RENDERFIRST', '<a style="text-decoration:none;" class="ahref-edit" href="http://' . $_SERVER['SERVER_NAME'] . ':20012/?dicomweb=http://' . $_SERVER['SERVER_NAME'] . ':9090/dcm4chee-arc/aets/DCM4CHEE/rs/studies/');
define('RENDERLAST', '"target="_blank"><span class="btn rgba-stylish-slight btn-inti2" style="box-shadow: none;"><img src="../image/eyepink.svg" data-toggle="tooltip" title="Intiwid Render Viewer" style="width: 100%;"></span></a>');


// VIEWER DI WORKLIST ICON LARGE
define('DICOMWORKLISTFIRST', '<a href="jnlp://' . $_SERVER['SERVER_NAME'] . ':19898/weasis-pacs-connector/DCM_viewer.jnlp?studyUID=');
define('DICOMWORKLISTLAST', '"class="button8 delete1"><img src="../image/desktop.svg" style="width: 50px;"><br> <span> Dicom Viewer</span></a>');
define('DICOMNEWWORKLISTFIRST', '<a href="http://' . $_SERVER['SERVER_NAME'] . ':9090/weasis-pacs-connector/IHEInvokeImageDisplay?studyUID=');
define('DICOMNEWWORKLISTLAST', '"class="button8 delete1"><img src="../image/desktop.svg" style="width: 50px;"><br> <span> Dicom Viewer</span></a>');
if ($_SERVER['SERVER_NAME'] == $hostname['ip_publik'] or $_SERVER['SERVER_NAME'] == '49.128.176.141') {
    define('HTMLWORKLISTFIRST', '<a href="http://' . $_SERVER['SERVER_NAME'] . ':20001/holan/viewer.html?studyUID=');
} else {
    define('HTMLWORKLISTFIRST', '<a href="http://' . $_SERVER['SERVER_NAME'] . ':19898/holan/viewer.html?studyUID=');
}
define('HTMLWORKLISTLAST', '" class="button8 delete1" target="_blank"><img src="../image/html.svg" style="width: 50px;"><br> <span> HTML Viewer</span></a>');
define('RADIANTWORKLISTFIRST', '<a href="radiant://?n=paet&v=dcmPACS&n=pstv&v=0020000D&v=%22');
define('RADIANTWORKLISTLAST', '%22" class="button8 delete1"><img src="../image/radiAnt.png" style="width: 50px;"><br><span> Radiant</span></a>');
define('RENDERWORKLISTFIRST', '<a href="http://' . $_SERVER['SERVER_NAME'] . ':93/?dicomweb=http://' . $_SERVER['SERVER_NAME'] . ':9090/dcm4chee-arc/aets/DCM4CHEE/rs/studies/');
define('RENDERWORKLISTLAST', '" class="button8 delete1" target="_blank"><img src="../image/render-viewer.svg" style="width: 50px;"><br> <span> Intiwid Render Viewer</span></a>');

// url HOROS -> Horos://?methodName=retrieve&serverName=INTIWID&then=open&retrieveOnlyIfNeeded=yes&filterKey=StudyInstanceUID&filterValue=
// url HOROS -> Horos://?methodName=displayStudy&StudyInstanceUID=
define('HOROSWORKLISTFIRST', "$horos");
define('HOROSWORKLISTLAST', '"class="button8 delete1"><img src="../image/horos.png" style="width: 50px;"><br><span> Horos Viewer</span></a>');
define('INOBITECWORKLISTFIRST', '<a onclick="inobitec(');
define('INOBITECWORKLISTLAST', ')" id="inobitec" data-ip="' . $_SERVER['SERVER_NAME'] . '" class="button8 delete1"><img src="../image/inobitec.png" style="width: 50px;"><br><span> Inobitec Viewer</span></a>');


//copy link ohif
define('LINKOHIFFIRST', '<button class="dropdown-item dropdown-item1" id="my_button" onclick="copyText(event, ');
define('LINKOHIFLAST', ')"><i class="fas fa-link"></i>Copy Link</button>');
define('EXTLINKOHIF', "'");
define('COPYUIDFIRST', '<button sclass="dropdown-item dropdown-item1" id="my_button" onclick="copyText(event, ');
define('COPYUIDLAST', ')"><i class="fas fa-copy"></i> Copy UID</button>');
