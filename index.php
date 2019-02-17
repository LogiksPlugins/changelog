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
			"downloadChangeLog"=>["icon"=>"<i class='fa fa-download'></i>","class"=>"hidden"],
		],
		"sidebar"=>false,
		"contentArea"=>"pageContentArea"
	]);

?>
<script>
$(function() {
    $("#pgtoolbar .nav.navbar-right").prepend('<li class="form-group" style="margin-top: 4px;width: 250px;"><div class="input-group" id=datetimepicker2>'+
            '<input type="text" class="form-control" placeholder="Find Since" id="changeSince" value="<?=date("d/m/Y H:i:s")?>">'+
            '<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span></div></li>');
    
    $("#datetimepicker2").datetimepicker({
        format: 'DD/MM/YYYY HH:mm:ss'
    });
    $("#changeLogBody").html("<h3 class='text-center'>No changelog found</h3>");
});
function reloadPage() {
    window.location.reload();
}
function getChangeLog() {
    $("#changeLogTitle").html("Change Log Since : <span id='dated'>"+$("#changeSince").val()+"</span>");
    // $("#dated").html($("#changeSince").val());
    $("#toolbtn_downloadChangeLog").closest("li").addClass("hidden");
    $("#changeLogBody").html("Loading ...");
    $("#changeLogBody").load(_service("changelog","list-log")+"&date1="+encodeURIComponent($("#changeSince").val()), function(data) {
        if($("#changeLogBody").children().length>0) {
            $("#toolbtn_downloadChangeLog").closest("li").removeClass("hidden");
        } else {
            $("#changeLogBody").html("<h3 class='text-center'>No changelog found</h3>");
        }
    });
}
function downloadChangeLog() {
    a=[];
	$("#changeLogBody input[type=checkbox]:checked").each(function() {
			a.push("file[]="+$(this).attr("rel"));
		});
	lnk=getServiceCMD("changelog","download-zip")+"&"+a.join("&");
	window.open(lnk);
}
</script>