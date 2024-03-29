jQuery(document).ready(function ($) {

	// switch theme menu pages
	jQuery('.lcwp_opt_menu').click(function () {
		curr_opt = jQuery('.curr_item').attr('id').substr(5);
		var opt_id = jQuery(this).attr('id').substr(5);

		if (!jQuery('#form_' + opt_id).is(':visible')) {
			// remove curr
			jQuery('.curr_item').removeClass('curr_item');
			jQuery('#form_' + curr_opt).hide();

			// show selected
			jQuery(this).addClass('curr_item');
			jQuery('#form_' + opt_id).show();
		}
	});


	// sliders
	ag_slider_opt = function () {
		var a = 0;
		$('.lcwp_slider').each(function (idx, elm) {
			var sid = 'slider' + a;
			jQuery(this).attr('id', sid);

			svalue = parseInt(jQuery("#" + sid).next('input').val());
			minv = parseInt(jQuery("#" + sid).attr('min'));
			maxv = parseInt(jQuery("#" + sid).attr('max'));
			stepv = parseInt(jQuery("#" + sid).attr('step'));

			jQuery('#' + sid).slider({
				range: "min",
				value: svalue,
				min: minv,
				max: maxv,
				step: stepv,
				slide: function (event, ui) {
					jQuery('#' + sid).next().val(ui.value);
				}
			});
			jQuery('#' + sid).next('input').change(function () {
				var val = parseInt(jQuery(this).val());
				var minv = parseInt(jQuery("#" + sid).attr('min'));
				var maxv = parseInt(jQuery("#" + sid).attr('max'));

				if (val <= maxv && val >= minv) {
					jQuery('#' + sid).slider('option', 'value', val);
				}
				else {
					if (val <= maxv) { jQuery('#' + sid).next('input').val(minv); }
					else { jQuery('#' + sid).next('input').val(maxv); }
				}
			});

			a = a + 1;
		});
	}
	ag_slider_opt();



	// custom checks
	ag_live_checks = function () {
		jQuery('.ip-checkbox').lc_switch('YES', 'NO');
	};
	ag_live_checks();



	// chosen
	ag_live_chosen = function () {
		jQuery('.lcweb-chosen').each(function () {
			var w = jQuery(this).css('width');
			jQuery(this).chosen({ width: w });
		});
		jQuery(".lcweb-chosen-deselect").chosen({ allow_single_deselect: true });
	};
	ag_live_chosen();



	// colorpicker
	ag_colpick = function () {
		jQuery('.lcwp_colpick input').each(function () {
			var curr_col = jQuery(this).val().replace('#', '');
			jQuery(this).colpick({
				layout: 'rgbhex',
				submit: 0,
				color: curr_col,
				onChange: function (hsb, hex, rgb, el, fromSetColor) {
					if (!fromSetColor) {
						jQuery(el).val('#' + hex);
						jQuery(el).parents('.lcwp_colpick').find('.lcwp_colblock').css('background-color', '#' + hex);
					}
				}
			}).keyup(function () {
				jQuery(this).colpickSetColor(this.value);
				jQuery(this).parents('.lcwp_colpick').find('.lcwp_colblock').css('background-color', this.value);
			});
		});
	}
	ag_colpick();

});