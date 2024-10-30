<?php
if(!defined('ROOT')) exit('No direct script access allowed');
loadModule("pages");

// Reference Code : 
// https://github.com/LogiksApps/Logiks-adminCP-OLD/blob/master/app/plugins/modules/changeLog/service.php
// https://github.com/LogiksApps/Logiks-adminCP-OLD/blob/master/app/plugins/modules/changeLog/index.php

function pageContentArea() {//Change Log Since : <span id='dated'>".date("d/m/Y H:i:s")."</span>
    return "<div class='container table-responsive'>
    <h2 id='changeLogTitle'></h2>
    <ul id='changeLogBody' class='list-group'>
      
    </ul>
</div>";
}

echo _css(["bootstrap.datetimepicker","changelog"]);
echo _js(["moment","bootstrap.datetimepicker","changelog"]);

printPageComponent(false,[
		"toolbar"=>[
			"reloadPage"=>["icon"=>"<i class='fa fa-refresh'></i>"],
			"getChangeLog"=>["icon"=>"<i class='fa fa-eye'></i>","align"=>"right","title"=>"Fetch"],
			
			"getPatches"=>["icon"=>"<i class='fa fa-list'></i>","align"=>"left","title"=>"Patches"],
			
			"checkAll"=>["icon"=>"<i class='fa fa-check-square'></i>","class"=>"on_fetch hidden","title"=>"Check All"],
			"downloadChangeLog"=>["icon"=>"<i class='fa fa-download'></i>","class"=>"on_fetch hidden","title"=>"Download"],
		],
		"sidebar"=>false,
		"contentArea"=>"pageContentArea"
	]);

?>
<script>
$(function() {
    $("#pgtoolbar .nav.navbar-right").prepend('<li class="form-group" style="margin-top: 4px;width: 250px;"><div class="input-group" id=datetimepicker2>'+
            '<input type="text" class="form-control" placeholder="Find Since" id="changeSince" value="<?=date("d/m/Y H:i:00")?>">'+
            '<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span></div></li>');
    
    $("#datetimepicker2").datetimepicker({
        format: 'DD/MM/YYYY HH:mm:00'
    });
    $("#changeLogBody").html("<h3 class='text-center'>No changelog found</h3>");
});
function reloadPage() {
    window.location.reload();
}
function getChangeLog() {
    $("#changeLogTitle").html("Change Log Since : <span id='dated'>"+$("#changeSince").val()+"</span>");
    // $("#dated").html($("#changeSince").val());
    $(".on_fetch").closest("li").addClass("hidden");
    
    $("#changeLogBody").html("Loading ...");
    $("#changeLogBody").load(_service("changeLog","list-log")+"&date1="+encodeURIComponent($("#changeSince").val()), function(data) {
        if($("#changeLogBody").children().length>0) {
            $(".on_fetch").closest("li").removeClass("hidden");
        } else {
            $("#changeLogBody").html("<h3 class='text-center'>No changelog found</h3>");
        }
    });
}
function getPatches() {
    $("#changeLogTitle").html("Patch History");
    $("#changeLogBody").html("Loading ...");
    $("#changeLogBody").load(_service("changeLog","list-patches"), function(data) {
        if($("#changeLogBody").children().length>0) {
            // $("#toolbtn_downloadChangeLog").closest("li").removeClass("hidden");
        } else {
            $("#changeLogBody").html("<h3 class='text-center'>No patches found</h3>");
        }
    });
}
function downloadChangeLog() {
    a=[];
	$("#changeLogBody input[type=checkbox]:checked").each(function() {
			a.push("file[]="+$(this).attr("rel"));
		});
	lnk=getServiceCMD("changeLog","download-zip")+"&"+a.join("&");
	window.open(lnk);
}
function checkAll() {
    if($("#changeLogBody input[type=checkbox]:first-child").is(":checked")) {
        $("#changeLogBody input[type=checkbox]").each(function() {
            this.checked = false;
        })
    } else {
        $("#changeLogBody input[type=checkbox]").each(function() {
            this.checked = true;
        })
    }
}
</script>