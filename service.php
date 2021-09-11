<?php
if(!defined('ROOT')) exit('No direct script access allowed');

$appPath = ROOT.APPS_FOLDER.CMS_SITENAME."/";

$sFrmt="<li class='list-group-item file'><input type=checkbox rel='%s' style='float:left;' /><b style='color:green;margin-right:4px;margin-left: 5px;'>%d</b>%s<a class='downloadlink hidden' href='".SiteLocation."services/?scmd=changelog&mode=downloadfile&file=%s' target=_blank><i style='color:blue;margin-left:10px;'>Download</i></a><div style='float:right'>%s</div></li>";
$patchFrmt="";
$logFile=ROOT.TMP_FOLDER."changelog/".date("Y-m-d G:m").".log";
$patchFile=ROOT.TMP_FOLDER."patches/patch_".date("YmdHis").".zip";
$excludeDir=[
    "usermedia",
	"tmp",
	"temp",
	"cache",
	"log","logs",
	".git",
	"vendor","vendors",
];
$excludeFile=array(".gitignore",".npmignore");
$writeLogToFile=false;

if(!file_exists(dirname($logFile))) {
	mkdir(dirname($logFile),0777,true);
	chmod(dirname($logFile),0777);
}
if(!file_exists(dirname($patchFile))) {
	mkdir(dirname($patchFile),0777,true);
	chmod(dirname($patchFile),0777);
}

include_once __DIR__ . "/changelog.php";

switch ($_REQUEST['action']) {
    case "list-log":
        if(isset($_REQUEST["date1"]) && strlen($_REQUEST["date1"])>0) {
            $data1Arr = explode(" ",$_REQUEST["date1"]);
			$date1=_date($data1Arr[0],"d/m/Y")." ".$data1Arr[1];
		}
		if(isset($_REQUEST["date2"]) && strlen($_REQUEST["date2"])>0) {
			$date2=_date($_REQUEST["date2"],"d/m/Y");
		} else {
			$date2 = strtotime(date("Y-m-d") . " +1 day");
			$date2 = date('Y-m-d 00:00:00', $date2);
		}
		
		$cl=new ChangeLog($appPath,$sFrmt,$writeLogToFile,$logFile,$excludeDir,$excludeFile);
		$cl->dumpChangelog($appPath, strtotime($date1),strtotime($date2));
    break;
    case "download-zip":
        $zipFile=$patchFile;
		$baseFolder=CMS_APPROOT;
		//$zip=null;
		$zip = new ZipArchive;
		$res = $zip->open($zipFile, ZipArchive::CREATE);
		if($res !== TRUE){
			echo 'Error: Unable to create zip file';
		} else {
			foreach($_REQUEST["file"] as $f) {
				$f=$_SERVER["DOCUMENT_ROOT"].base64_decode($f);
				if(file_exists($f)) {
					$fName=str_replace("#{$baseFolder}","","#{$f}");
					$zip->addFile($f,$fName);
				}
			}
			$zip->close();
			$filename=basename($zipFile);
			$mime="application/zip";
			ob_start();
			header("Content-type: $mime");
			header("Content-Disposition: attachment; filename=$filename");
			header("Expires: 0");
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header("Content-Transfer-Encoding: binary");
			header('Pragma: public');
			readfile($zipFile);
			ob_flush();
			ob_clean();
			if(isset($_REQUEST['autoclear']) && $_REQUEST['autoclear']=="true")
				unlink($zipFile);
			exit();
		}
		echo "Failed To Collect The Files";
    break;
    case "download-patch":
        if(isset($_REQUEST["file"])) {
            $patchDir=dirname($patchFile)."/";
    		$zipFile=$patchDir.$_REQUEST["file"];
    		if(file_exists($zipFile)) {
    			$filename=basename($zipFile);
    			$mime="application/zip";
    			ob_start();
    			header("Content-type: $mime");
    			header("Content-Disposition: attachment; filename=$filename");
    			header("Expires: 0");
    			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    			header("Content-Transfer-Encoding: binary");
    			header('Pragma: public');
    			readfile($zipFile);
    			ob_flush();
    			ob_clean();
    		} else {
    			echo "<b>".$_REQUEST["file"]."</b> Was Not Found In Patch Repo ...";
    		}
        } else {
            echo "<h3 align=center>No file requested</h3>";
        }
        break;
    case "list-patches":
        $patchDir=dirname($patchFile)."/";
		$fs=scandir($patchDir);
		$cnt=1;
		if(count($fs)>2) {
			foreach($fs as $a) {
				if($a=="." || $a=="..") continue;
				elseif(is_dir($patchDir.$a)) continue;
				$x=explode("_",$a);
				$dt=$x[1];
				$dt=substr($dt,0,strlen($dt)-4);
				$patchFrmt="<li class='list-group-item file'><b style='color:green;margin-right:4px;'>%d</b>%s<a class=downloadlink href='".SiteLocation."services/?scmd=changelog&mode=downloadpatch&file=%s' target=_blank><i style='color:blue;margin-left:10px;'>Download</i></a><div style='float:right'>%s</div></li>";
				printf($patchFrmt,$cnt,$a,$a,date("Y-m-d H:i:s", strtotime($dt)));
				$cnt++;
			}
		} else {
			echo "<h3 align=center>No Patches Created Till Now.</h3>";
		}
    break;
    
}
?>