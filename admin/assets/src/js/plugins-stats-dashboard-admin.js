(function( $ ) {
	'use strict';

	// Inspired by http://stackoverflow.com/questions/2901102/how-to-print-a-number-with-commas-as-thousands-separators-in-javascript
	 $.commaSeparateNumber = function(value) {
        var parts = value.toString().split(".");

    	parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");

    	return parts.join(".");
    };

	 var PSD = {
	 	load: function(){	
	 		NProgress.configure({
				parent : "#plugins-stats-dashboard-progress-bar",
				showSpinner : false
			});			
	 				
	 		var $tbody = $('#plugin-stats-dashboard.postbox TABLE.plugin-stats-dashboard-list-table TBODY');

			$tbody.hide('fast', function(){
				$(this).html('');
			});

			NProgress.start();
			
	 		var current_stat = $('#plugins-stats-dashboard-stat-option').val();

	 		$.ajax({
				url: ajaxurl,
				type: 'POST',			 
				data: { action : 'plugin_stats_dashboard_load', security: ajax_object.security, current_stat: current_stat },			 
				dataType: 'json',
				success: function(json) {
					if (json.error) {
						console.log("Error loading stats.");
					} else {
						var count = json.plugins.length;

						$.each(json.plugins, function(i, plugin) {
							var $row = $('<tr>'),
								stat = current_stat == 'version' ? plugin[current_stat] : $.commaSeparateNumber(plugin[current_stat]);

							$row.append($('<td>').append($('<a>').prop({href: plugin.homepage, target: '_blank'}).html(plugin.name)));
							$row.append($('<td>').prop('align', 'right').text(stat));

							$tbody.append($row);

							NProgress.set(count/100);
						});

						NProgress.done();

						$tbody.show('fast');
					}
				}
			});			
	 	}
	 };

	 $(function() {
	 		PSD.load();

	 		$('#plugins-stats-dashboard-stat-option').on('change', function(e){
				PSD.load();
	 		}) ;
	 });

})( jQuery );
