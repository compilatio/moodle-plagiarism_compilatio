define(["jquery"],function(c){function p(){c(".compilatio-button").each(function(){c(this).attr("disabled","disabled"),c(this).addClass("disabled"),c(this).attr("href","#")})}return{get_indexing_state:function(i,t,o){c(document).ready(function(){c.post(i+"/plagiarism/compilatio/ajax/get_indexing_state.php",{idDoc:o},function(i){c(".compi-"+t+" .library").detach(),c(".compi-"+t).prepend(i)})})},refresh_button:function(a,n,e){c(document).ready(function(){var t=n.length,o=0,i=c("i.fa-refresh").parent("button");0==t?p():i.click(function(){p(),c("#compilatio-home").html("<p>"+e+"<progress id='compi-update-progress' value='"+o+"' max='"+t+"'></progress></p>"),c("#compilatio-logo").click(),n.forEach(function(i){c.post(a+"/plagiarism/compilatio/ajax/compilatio_check_analysis.php",{id:i},function(i){o++,c("#compi-update-progress").val(o),o==t&&window.location.reload()})})})})}}});