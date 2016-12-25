(function( $ ) {
	'use strict';

	// Credit for ucFirst: https://jsfiddle.net/gabrieleromanato/vBBnR/
	 $.ucfirst = function(str) {
        var text = str;

        var parts = text.split(' '),
            len = parts.length,
            i, words = [];

        for (i = 0; i < len; i++) {
            var part = parts[i];
            var first = part[0].toUpperCase();
            var rest = part.substring(1, part.length);
            var word = first + rest;

            words.push(word);
        }

        return words.join(' ');
    };

	 var PSD = {
	 	load: function(){
			$tbody.html('');
			
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
						var $tbody = $('#plugin-stats-dashboard.postbox TABLE.plugin-stats-dashboard-list-table TBODY');

						$.each(json.plugins, function(i, plugin) {

							var $row = $('<tr>');

							$row.append($('<td>').append($('<a>').prop({href: plugin.link, target: '_blank'}).text(plugin.name)));
							$row.append($('<td>').prop('align', 'right').text(plugin[current_stat]));

							$tbody.append($row);
						});
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
