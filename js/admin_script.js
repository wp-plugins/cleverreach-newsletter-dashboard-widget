var haet_cleverreach_dashboard = haet_cleverreach_dashboard || {};



haet_cleverreach_dashboard.ajaxSave = function(){
	var $ = jQuery;
	var data = {
		'action': 'cleverreach_dashboard_save_settings',
		'api_key': $('#haet_cleverreach_dashboard_api_key').val()
	};
	$('.haet-crd-status-bar').html('<div class="haet-crd-loading"><i></i><i></i><i></i></div> '+ajax_object.str_connecting).slideDown(500);
	$.post(ajax_object.ajax_url, data, function(response) {
		if(response=='success'){
			location.reload(); 
		}else{
			$('.haet-crd-status-bar').html(response);
		}
	});
}

haet_cleverreach_dashboard.loadCharts = function(){
	var $ = jQuery;
	var params = {
		'action': 'cleverreach_dashboard_get_chart_data'
	};
	$('.haet-crd-status-bar').html('<div class="haet-crd-loading"><i></i><i></i><i></i></div> '+ajax_object.str_loading);
	$.post(ajax_object.ajax_url, params, function(response) {
		data = JSON.parse(response);
		$('.haet-crd-status-bar').slideUp();
		if(data.success==true){
			haet_cleverreach_dashboard.showCharts(data);
		}else{
			$('.haet-crd-status-bar').html(data.message);
		}
	});
}

haet_cleverreach_dashboard.initializeChartsWithCachedData = function(){
	var $ = jQuery;
	for(var i=0;i<3 ;i++){
		var $canvas = $('#haet-crd-pie-'+i);
		$canvas[0].height = $canvas.width() ;
	}
	data = JSON.parse(ajax_object.haet_crd_cached_data);
	haet_cleverreach_dashboard.showCharts(data);
}

haet_cleverreach_dashboard.showCharts = function(data){
	var $ = jQuery;
	if(data.reports){
		for(var i=0;i<3 && i<data.reports.length;i++){
			var $chart_area = $('.haet-crd-pie-'+i);
			var $canvas = $('#haet-crd-pie-'+i);

			var ctx = $canvas.get(0).getContext("2d");
			var segmentStrokeWidth=2;
			if(i>0)
				segmentStrokeWidth=1;
			new Chart(ctx).Pie(data.reports[i].chart_data,{segmentStrokeWidth : segmentStrokeWidth, animationEasing : "easeOutQuad", animationSteps:20});	
			$chart_area.find('.chart-title').text(data.reports[i].name);
			$chart_area.parent().find('.date').text(data.reports[i].sent);
		}
	}
	if(data.daily_chart_data){
		var $canvas = $('#haet-crd-line');
		var ctx = $canvas.get(0).getContext("2d");
		
		new Chart(ctx).Line(data.daily_chart_data);
	}
	if(data.links){
		$('.haet-crd-links').html(data.links);
	}
}


jQuery(document).ready(function($) {

	Chart.defaults.global.responsive = true;
	Chart.defaults.global.animationEasing = 'easeOutQuad';
	Chart.defaults.global.animationSteps = 30;

	$('#haet_cleverreach_dashboard_save').click(function(){
		haet_cleverreach_dashboard.ajaxSave();
	});

	if($('.haet-crd-widget-small').length>0){
		haet_cleverreach_dashboard.initializeChartsWithCachedData();
		haet_cleverreach_dashboard.loadCharts();
	}
}); 



	//$('#haet_cleverreach_dashboardfooterleftcolor,#haet_cleverreach_dashboardfootercentercolor,#haet_cleverreach_dashboardfooterrightcolor').wpColorPicker();
