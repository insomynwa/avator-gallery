(function ($) {
	var ag_gallery_w = []; // galleries width wrapper
	var ag_img_margin = []; // gallery images margin 
	var ag_img_border = []; // know border width for each gallery
	var ag_gallery_pag = []; // know which page is shown for each gallery
	var ag_gall_pag_cache = []; // cache gallery pages to avoid double ajax calls
	var ag_gall_curr_filter = []; // cache matched image indexes derived from a filter (empty == no filter)

	var ag_first_init = []; // flag for initial gallery management
	var ag_new_images = []; // flag for new images added
	var ag_is_paginating = []; // flag for pagination animation
	var ag_gall_is_showing = []; // showing animation debouncer
	var ag_shown_gall = []; // shown gallery flag
	var ag_debounce_resize = []; // reesize trigger debounce for every gallery 

	var coll_ajax_obj = []; // where to store ajax objects to abort ajax calls
	var coll_gall_cache = []; // store ajax-called galleries to avoid double ajax calls
	var coll_scroll_helper = []; // store collection item clicked to return at proper scroll point

	// photostring manag - global vars
	var ag_temp_w = [];
	var ag_row_img = [];
	var ag_row_img_w = [];

	// CSS3 loader code
	ag_loader =
		'<div class="ag_loader">' +
		'<div class="agl_1"></div><div class="agl_2"></div><div class="agl_3"></div><div class="agl_4"></div>' +
		'</div>';


	// on init
	$(document).ready(function () {
		ag_get_cg_deeplink();

		// if old IE, hide secondary overlay
		if (ag_is_old_IE()) { $('.ag_sec_overlay').hide(); }
	});


	// initialize the galleries
	ag_galleries_init = function (gid, after_resize) {
		// if need to initialize a specific gallery
		if (typeof (gid) != 'undefined' && gid) {
			if (!$('#' + gid).length) { return false; }

			if (typeof (after_resize) == 'undefined') {
				ag_first_init[gid] = 1;
				ag_new_images[gid] = 1;
				ag_is_paginating[gid] = 0;
			}

			ag_pagenum_btn_vis(gid);
			ag_gallery_process(gid, after_resize);
		}

		// execute every gallery in the page
		else {
			$('.ag_gallery_wrap').not(':empty').each(function () {
				var ag_gid = $(this).attr('id');
				ag_galleries_init(ag_gid, after_resize);
			});
		}
	};


	// store galleries info 
	ag_gallery_info = function (gid, after_resize) {
		var coll_sel = ($('#' + gid).hasClass('ag_collection_wrap')) ? '.ag_coll_container' : '';
		ag_gallery_w[gid] = (coll_sel) ? $('#' + gid + ' .ag_coll_container').width() : $('#' + gid).width();

		if (typeof (after_resize) != 'undefined') { return true; } // only get size if resize event has been triggered

		ag_img_border[gid] = parseInt($('#' + gid + ' ' + coll_sel + ' .ag_img').first().css('border-right-width'));
		ag_img_margin[gid] = parseInt($('#' + gid + ' ' + coll_sel + ' .ag_img').first().css('margin-right'));

		// exceptions for isotope elements
		if ($('#' + gid).hasClass('ag_masonry_gallery')) {
			ag_img_margin[gid] = parseInt($('#' + gid + ' ' + coll_sel + ' .ag_img').first().css('padding-right'));
		}
		else if ($('#' + gid).hasClass('ag_collection_wrap')) {
			ag_img_margin[gid] = parseInt($('#' + gid + ' ' + coll_sel + ' .ag_coll_img_wrap').first().css('padding-right'));
		}
	};


	// process single gallery
	ag_gallery_process = function (gid, after_resize) {
		if (typeof (gid) == 'undefined') { return false; }

		ag_gallery_info(gid, after_resize);


		if ($('#' + gid).hasClass('ag_standard_gallery')) {
			ag_man_standard_gallery(gid);
		}
		else if ($('#' + gid).hasClass('ag_columnized_gallery')) {
			ag_man_colnzd_gallery(gid);
		}
		else if ($('#' + gid).hasClass('ag_masonry_gallery')) {
			ag_man_masonry_gallery(gid);
		}
		else if ($('#' + gid).hasClass('ag_string_gallery')) {
			ag_man_string_gallery(gid);
		}
		else if ($('#' + gid).hasClass('ag_collection_wrap')) {
			ag_man_collection(gid);
		}


		// OVERLAY MANAGER ADD-ON //
		if (typeof (agom_hub) == "function") {
			agom_hub(gid);
		}
		////////////////////////////
	};


	// get lazyload parameter and set it as image URL
	var lazy_to_img_url = function (subj_id, is_coll) {
		$subj = (typeof (is_coll) == 'undefined') ? $('#' + subj_id + ' .ag_main_thumb') : $('#' + subj_id + ' .ag_coll_outer_container .ag_main_thumb');

		$subj.each(function () {
			if ($(this).data('ag-lazy-src') != 'undefined') {
				$(this).attr('src', $(this).data('ag-lazy-src'));
				$(this).removeAttr('ag-lazy-src');
			}
		});
	};



	/*** manage standard gallery ***/
	ag_man_standard_gallery = function (gid) {
		if (!$('#' + gid + ' .ag_img').length) { return false; }
		lazy_to_img_url(gid);

		if (ag_new_images[gid]) {
			$('#' + gid + ' .ag_img .ag_main_thumb').lcweb_lazyload({
				allLoaded: function (url_arr, width_arr, height_arr) {
					$('#' + gid + ' .ag_loader').fadeOut('fast');
					ag_img_fx_setup(gid, width_arr, height_arr);

					$('#' + gid + ' .ag_img').each(function (i) {
						$(this).addClass(gid + '-' + i).css('width', (width_arr[0] + ag_img_border[gid] * 2)); // set fixed width to allow CSS fx during filter

						var $to_display = $('#' + gid + ' .ag_img').not('.ag_shown');
						if (i == 0) {
							ag_gallery_slideDown(gid, $to_display.not('.ag_excluded_img').length);
						}

						if (i == ($('#' + gid + ' .ag_img').length - 1)) {
							$to_display.ag_display_images(gid);
						}
					});
					ag_new_images[gid] = 0;

					$(window).trigger('ag_loaded_gallery', [gid]);
				}
			});
		}

		ag_check_primary_ol(gid);
	}



	/*** manage columnized gallery ***/
	ag_man_colnzd_gallery = function (gid) {

		if (!$('#' + gid + ' .ag_img').length) { return false; }
		lazy_to_img_url(gid);

		var cols = calc_colnzd_cols(gid);
		$('#' + gid + ' .ag_container').css('width', 'calc(100% + ' + ag_img_margin[gid] + 'px + ' + cols + 'px)');
		$('#' + gid + ' .ag_img').css('width', 'calc(' + (100 / cols) + '% - ' + ag_img_margin[gid] + 'px - 1px)');

		ag_check_primary_ol(gid);

		if (ag_new_images[gid]) {
			$('#' + gid + ' .ag_img .ag_main_thumb').lcweb_lazyload({
				allLoaded: function (url_arr, width_arr, height_arr) {

					$('#' + gid + ' .ag_loader').fadeOut('fast');
					ag_img_fx_setup(gid, width_arr, height_arr);

					$('#' + gid + ' .ag_img').each(function (i) {
						$(this).addClass(gid + '-' + i);

						var $to_display = $('#' + gid + ' .ag_img').not('.ag_shown');
						if (i == 0) {
							ag_gallery_slideDown(gid, $to_display.not('.ag_excluded_img').length);
						}
						if (i == ($('#' + gid + ' .ag_img').length - 1)) {
							$to_display.ag_display_images(gid);
						}
					});
					ag_new_images[gid] = 0;

					$(window).trigger('ag_loaded_gallery', [gid]);
				}
			});
		}

		ag_check_primary_ol(gid);
	};


	// returns how many columns will gallery needs to show
	var calc_colnzd_cols = function (gid) {
		var tot_w = ag_gallery_w[gid] - ag_img_margin[gid];

		// calculate how many columns to show starting from 1
		var cols = 1;
		var col_w = tot_w;
		var max_w = parseInt($('#' + gid).data('col-maxw'));

		while (col_w >= max_w) {
			cols++;
			col_w = Math.round(tot_w / cols) - ag_img_margin[gid];
		}

		return cols;
	};



	/*** manage masonry gallery ***/
	ag_man_masonry_gallery = function (gid) {
		lazy_to_img_url(gid);

		var cols = parseInt($('#' + gid).data('col-num'));
		var margin = ag_img_margin[gid];
		var col_w = Math.floor((ag_gallery_w[gid] + margin) / cols);

		// custom min width?
		var min_w = (typeof ($('#' + gid).data('minw')) != 'undefined') ? parseInt($('#' + gid).data('minw')) : ag_masonry_min_w;

		// find out right column number
		while (col_w < min_w) {
			if (cols <= 1) {
				cols = 1;
				return false;
			}

			cols--;
			col_w = Math.floor((ag_gallery_w[gid] + margin) / cols);
		}

		$('#' + gid + ' .ag_img').each(function (i) {
			var img_class = gid + '-' + i;
			$(this).css('width', col_w).addClass(img_class);
		});


		// if is smaller than wrapper - center items
		var diff = ag_gallery_w[gid] + margin - (cols * col_w);
		if (diff > 0) {
			$('#' + gid + ' .ag_container').css('left', Math.floor(diff / 2));
		}








		ag_check_primary_ol(gid);


		if (ag_new_images[gid]) {
			$('#' + gid + ' .ag_img:not(.ag_excluded_img) .ag_main_thumb').lcweb_lazyload({
				allLoaded: function (url_arr, width_arr, height_arr) {
					$('#' + gid + ' .ag_loader').fadeOut('fast');
					ag_img_fx_setup(gid, width_arr, height_arr);

					$('#' + gid + ' .ag_container').isotope({
						percentPosition: true,
						isResizeBound: false,
						resize: false,
						originLeft: !ag_rtl,
						masonry: {
							columnWidth: 1
						},
						containerClass: 'ag_isotope',
						itemClass: 'ag_isotope-item',
						itemSelector: '.ag_img',
						transitionDuration: 0,
					});

					setTimeout(function () { // litle delay to allow masonry placement
						var $to_display = $('#' + gid + ' .ag_img').not('.ag_shown');

						ag_gallery_slideDown(gid, $to_display.not('.ag_excluded_img').length);
						$to_display.ag_display_images(gid);

						ag_new_images[gid] = 0;
						$(window).trigger('ag_loaded_gallery', [gid]);
					}, 300);
				}
			});
		}
		else {
			setTimeout(function () {
				if (typeof ($.Isotope) != 'undefined' && typeof ($.Isotope.prototype.reLayout) != 'undefined') { // old Isotope
					$('#' + gid + ' .ag_container').isotope('reLayout');
				} else { // new
					$('#' + gid + ' .ag_container').isotope('layout');
				}
			}, 100);
		}
	}



	/*** manage photostring gallery ***/
	ag_man_string_gallery = function (gid, filter_relayout) {
		lazy_to_img_url(gid);

		if (ag_new_images[gid]) {
			$('#' + gid + ' .ag_img .ag_main_thumb').lcweb_lazyload({
				allLoaded: function (url_arr, width_arr, height_arr) {

					ag_img_fx_setup(gid, width_arr, height_arr);
					layout_photostr_gall(gid, filter_relayout);

					$('#' + gid + ' .ag_loader').fadeOut('fast');

					var $to_display = $('#' + gid + ' .ag_img').not('.ag_shown');
					ag_gallery_slideDown(gid, $to_display.not('.ag_excluded_img').length);
					$to_display.ag_display_images(gid);


					ag_new_images[gid] = 0;
					$(window).trigger('ag_loaded_gallery', [gid]);
				}
			});
		}
		else {
			layout_photostr_gall(gid, filter_relayout);
		}

		ag_check_primary_ol(gid);
	};

	var layout_photostr_gall = function (gid, filter_relayout) {

		// is re-layouting because of a filter? match the fakebox
		if (typeof (filter_relayout) != 'undefined') {
			var selector = filter_relayout + ' .ag_img .ag_main_thumb';
			gid = filter_relayout.replace('#ag_fakebox_', '');
		}
		else {
			var selector = '#' + gid + ' .ag_img:not(.ag_excluded_img) .ag_main_thumb';
		}

		ag_temp_w[gid] = 0;
		ag_row_img[gid] = [];
		ag_row_img_w[gid] = [];

		// sometimes browsers have bad behavior also using perfect width fit
		var container_w = ag_gallery_w[gid] + ag_img_margin[gid];

		$(selector).each(function (i, v) {
			var $img_obj = $(this).parents('.ag_img');
			var img_class = gid + '-' + $img_obj.data('img-id');
			var w_to_match = 0;

			// reset sizes
			$img_obj.css('width', ($(this).width() - 2)).css('maxWidth', ($(this).width() + ag_img_border[gid]));

			$img_obj.addClass(img_class);
			var img_w = ($(this).width() - 2) + ag_img_border[gid] + ag_img_margin[gid]; // subtract 2 pixels to avoid empty bars on sides in rare extensions 

			ag_row_img[gid].push('.' + img_class);
			ag_row_img_w[gid].push(img_w);

			ag_temp_w[gid] = ag_temp_w[gid] + img_w;
			w_to_match = ag_temp_w[gid];

			// if you're lucky and size is perfect
			if (container_w == w_to_match) {
				ag_row_img[gid] = [];
				ag_row_img_w[gid] = [];
				ag_temp_w[gid] = 0;
			}

			// adjust img sizes		
			else if (container_w < w_to_match) {
				var to_shrink = w_to_match - container_w;
				photostr_row_img_shrink(gid, to_shrink, container_w);

				ag_row_img[gid] = [];
				ag_row_img_w[gid] = [];
				ag_temp_w[gid] = 0;
			}
		});
	};


	var photostr_row_img_shrink = function (gid, to_shrink, container_w) {
		var remaining_shrink = to_shrink;
		var new_row_w = 0;

		// custom min width?
		var min_w = (typeof ($('#' + gid).data('minw')) != 'undefined') ? parseInt($('#' + gid).data('minw')) : ag_phosostr_min_w;

		// only one image - set to 100% width
		if (ag_row_img[gid].length == 1) {
			$(ag_row_img[gid][0]).css('width', 'calc(100% - ' + (ag_img_margin[gid] + 1) + 'px)'); // +1 == security margin added previously
			return true;
		}

		// calculate
		var curr_img_w_arr = ag_row_img_w[gid];
		var reached_min = [];
		var extreme_shrink_done = false

		a = 0; // security stop
		while (ps_row_img_w(curr_img_w_arr) > container_w && !extreme_shrink_done && a < 100) {
			a++;

			var to_shrink_per_img = Math.ceil(remaining_shrink / (ag_row_img[gid].length - reached_min.length));
			var new_min_reached = false;

			// does this reduce too much an element? recalculate
			$.each(ag_row_img_w[gid], function (i, img_w) {
				if ($.inArray(i, reached_min) !== -1) {
					return true;
				}

				var new_w = img_w - to_shrink_per_img;
				if (new_w < min_w) {
					new_w = min_w;

					// min is greater than images width?
					var true_img_w = ($(ag_row_img[gid][i]).find('.ag_main_thumb').width() - 2) + ag_img_border[gid]; // subtract 2 pixels to avoid empty bars on sides in rare extensions 
					if (new_w > true_img_w) {
						new_w = true_img_w;
					}

					reached_min.push(i);
					new_min_reached = true;

					remaining_shrink = remaining_shrink - (ag_row_img_w[gid][i] - new_w);
				}
			});
			if (new_min_reached) { continue; }


			// calculate new width for every image
			$.each(ag_row_img_w[gid], function (i, img_w) {
				if ($.inArray(i, reached_min) !== -1) {
					return true;
				}
				ag_row_img_w[gid][i] = img_w - to_shrink_per_img;
			});

			curr_img_w_arr = ag_row_img_w[gid];
			remaining_shrink = ps_row_img_w(curr_img_w_arr) - container_w;


			// if every image reached the minimum - split the remaining between them
			if (reached_min.length >= ag_row_img[gid].length) {
				to_shrink_per_img = Math.ceil(remaining_shrink / ag_row_img[gid].length);

				$.each(ag_row_img_w[gid], function (i, img_w) {
					ag_row_img_w[gid][i] = img_w - to_shrink_per_img;
				});

				extreme_shrink_done = true;
			}

			curr_img_w_arr = ag_row_img_w[gid];
		}


		// apply new width
		$.each(ag_row_img[gid], function (i, img_selector) {
			$(img_selector).css('width', ag_row_img_w[gid][i] - ag_img_margin[gid]);
		});


		// overall width is smaller than container? enlarge the first useful image
		var diff = container_w - ps_row_img_w(ag_row_img_w[gid]);
		if (diff > 0) {

			$.each(ag_row_img[gid], function (i, img_selector) {

				if ($.inArray(i, reached_min) === -1 || i == (ag_row_img[gid].length - 1)) { // extrema ratio - last element will be enlarged if everyone already reached the maximum

					$(img_selector).css('width', ag_row_img_w[gid][i] - ag_img_margin[gid] + diff);
					return false;
				}
			});
		}
	};

	// gived an array of selectors - return the overall elements width
	var ps_row_img_w = function (img_w_array) {
		var tot_w = 0;
		$.each(img_w_array, function (i, img_w) {
			tot_w = tot_w + parseFloat(img_w);
		});

		return tot_w;
	};





	/*** manage collection ***/
	ag_man_collection = function (cid) {
		lazy_to_img_url(cid, true);

		var cols = calc_coll_cols(cid);
		$('#' + cid + ' .ag_coll_container').css('width', 'calc(100% + ' + ag_img_margin[cid] + 'px + ' + cols + 'px)');
		$('#' + cid + ' .ag_coll_img_wrap').css('width', 'calc(' + (100 / cols) + '% - 1px)');

		if (ag_rtl) {
			$('#' + cid + ' .ag_coll_container').css('left', cols * -1);
		}

		ag_check_primary_ol(cid);

		if (!ag_shown_gall[cid]) {
			$('#' + cid + ' .ag_coll_img .ag_main_thumb').lcweb_lazyload({
				allLoaded: function (url_arr, width_arr, height_arr) {
					$('#' + cid + ' .ag_loader').fadeOut('fast');
					ag_img_fx_setup(cid, width_arr, height_arr);


					$('#' + cid + ' .ag_coll_img').each(function (i) {
						var img_class = cid + '-' + i;
						$(this).addClass(img_class);
					});

					$('#' + cid + ' .ag_coll_container').isotope({
						layoutMode: 'fitRows',
						percentPosition: true,
						isResizeBound: false,
						resize: false,
						originLeft: !ag_rtl,
						containerClass: 'ag_isotope',
						itemClass: 'ag_isotope-item',
						itemSelector: '.ag_coll_img_wrap',
						transitionDuration: '0.6s',
						filter: (typeof (ag_coll_dl_filter) != 'undefined') ? '.agc_' + ag_coll_dl_filter : ''
					});

					setTimeout(function () { // litle delay to allow masonry placement
						var $to_display = $('#' + cid + ' .ag_coll_img_wrap').not('.ag_shown');

						ag_gallery_slideDown(cid, $to_display.length);
						$to_display.ag_display_images();

						ag_new_images[cid] = 0;
						$(window).trigger('ag_loaded_collection', [cid]);
					}, 300);
				}
			});
		}
		else {
			setTimeout(function () {
				if (typeof ($.Isotope) != 'undefined' && typeof ($.Isotope.prototype.reLayout) != 'undefined') { // old Isotope
					$('#' + cid + ' .ag_container').isotope('reLayout');
				} else { // new
					$('#' + cid + ' .ag_container').isotope('layout');
				}
			}, 300);
		}
	};


	// returns how many columns will collection needs to show
	var calc_coll_cols = function (cid) {
		var tot_w = ag_gallery_w[cid] - ag_img_margin[cid];

		// calculate how many columns to show starting from 1
		var cols = 1;
		var col_w = tot_w;

		while (col_w >= ag_coll_max_w) {
			cols++;
			col_w = Math.round(tot_w / cols) - ag_img_margin[cid];
		}

		return cols;
	};


	////////////////////////////////////////////////////////////////


	// load a collection gallery - click trigger
	$(document).ready(function () {
		$('body').delegate('.ag_coll_img:not(.ag_linked_img)', 'click', function () {
			var cid = $(this).parents('.ag_collection_wrap').attr('id');
			var gdata = $(this).data('gall-data');
			var gid = $(this).attr('rel');

			if (typeof (coll_ajax_obj[cid]) == 'undefined' || !coll_ajax_obj[cid]) {
				ag_set_deeplink('coll-gall', gid);
				ag_load_coll_gallery(cid, gdata);
			}
		});
	});

	// load collection's gallery 
	ag_load_coll_gallery = function (cid, gdata) {
		var curr_url = $(location).attr('href');
		if (typeof (coll_gall_cache[cid]) == 'undefined') {
			coll_gall_cache[cid] = [];
		}

		// set trigger to return at proper scroll point
		coll_scroll_helper[cid] = $('#' + cid + ' .ag_coll_img[data-gall-data="' + gdata + '"]');

		// prepare
		if ($('#' + cid + ' .ag_coll_gallery_container .ag_gallery_wrap').length) {
			$('#' + cid + ' .ag_coll_gallery_container .ag_gallery_wrap').remove();
			$('#' + cid + ' .ag_coll_gallery_container').append('<div class="ag_gallery_wrap">' + ag_loader + '</div>');
		}
		$('#' + cid + ' .ag_coll_gallery_container .ag_gallery_wrap').addClass('ag_coll_ajax_wait');

		$('#' + cid + ' > table').animate({ 'left': '-100%' }, 700, function () {
			$('#' + cid + ' .ag_coll_table_first_cell').css('opacity', 0);
		});

		// set absolute position to keep just shown gallery's height
		setTimeout(function () {
			$('#' + cid + ' .ag_coll_table_first_cell').css('position', 'absolute');
		}, 710);

		// scroll to the top of the collection - if is lower of the gallery top
		var coll_top_pos = $('#' + cid).offset().top;
		if ($(window).scrollTop() > coll_top_pos) {
			$('html, body').animate({ 'scrollTop': coll_top_pos - 15 }, 600);
		}

		// check in stored cache
		if (typeof (coll_gall_cache[cid][gdata]) != 'undefined') {
			fill_coll_gallery(cid, coll_gall_cache[cid][gdata]);
		}
		else {
			var data = {
				ag_type: 'ag_load_coll_gallery',
				cid: cid,
				gdata: gdata
			};
			coll_ajax_obj[cid] = $.post(curr_url, data, function (response) {
				coll_gall_cache[cid][gdata] = response;
				fill_coll_gallery(cid, response);

				// LC lightbox - deeplink
				if (typeof (ag_lcl_allow_deeplink) != 'undefined') {
					ag_lcl_allow_deeplink();
				}
			});
		}
	}

	// given gallery data (through ajax or cache) - show it
	var fill_coll_gallery = function (cid, gall_data) {
		$('#' + cid + ' .ag_coll_gallery_container .ag_gallery_wrap').remove();
		$('#' + cid + ' .ag_coll_gallery_container').removeClass('ag_main_loader').append(gall_data);

		if ($('#' + cid + ' .ag_coll_gall_title').length > 1) {
			$('#' + cid + ' .ag_coll_gall_title').first().remove();
		}

		ag_coll_gall_title_layout(cid);
		coll_ajax_obj[cid] = null;

		var gid = $('#' + cid + ' .ag_coll_gallery_container').find('.ag_gallery_wrap').attr('id');
		ag_galleries_init(gid);
	};


	// collections title - mobile check
	ag_coll_gall_title_layout = function (cid) {
		$('#' + cid + ' .ag_coll_gall_title').each(function () {
			var wrap_w = $(this).parents('.ag_coll_table_cell').width();
			var elem_w = $(this).parent().find('.ag_coll_go_back').outerWidth(true) + $(this).outerWidth();

			if (elem_w > wrap_w) { $(this).addClass('ag_narrow_coll'); }
			else { $(this).removeClass('ag_narrow_coll'); }
		});
	}


	// back to collection
	$(document).ready(function () {
		$('body').delegate('.ag_coll_go_back', 'click', function () {
			var cid = $(this).parents('.ag_collection_wrap').attr('id');

			// if is performing ajax - abort
			if (typeof (coll_ajax_obj[cid]) != 'undefined' && coll_ajax_obj[cid]) {
				coll_ajax_obj[cid].abort();
				coll_ajax_obj[cid] = null;
			}

			// scroll to previously clicked item only if it is out of screen
			var docViewTop = $(window).scrollTop();
			var docViewBottom = docViewTop + $(window).height();

			var elemTop = coll_scroll_helper[cid].offset().top;
			var elemBottom = elemTop + coll_scroll_helper[cid].height();

			if ((elemBottom > docViewBottom) || elemTop < docViewTop) {
				var coll_top_pos = coll_scroll_helper[cid].offset().top - 60;
				$('html, body').animate({ 'scrollTop': coll_top_pos }, 600);
			}

			// go back
			$('#' + cid + ' .ag_coll_table_first_cell').css('opacity', 1).css('position', 'static');
			$('#' + cid + ' > table').animate({ 'left': 0 }, 700);

			setTimeout(function () {
				$('#' + cid + ' .ag_coll_gallery_container > *').not('.ag_coll_go_back').remove();
			}, 700);

			ag_clear_deeplink();
		});
	});


	// manual collections filter - handlers
	$(document).ready(function () {
		$('body').delegate('.ag_filter a', 'click', function (e) {
			e.preventDefault();

			var cid = $(this).parents('.ag_filter').attr('id').substr(4);
			var sel = $(this).attr('rel');
			var cont_id = '#' + $(this).parents('.ag_collection_wrap').attr('id');

			$('#agf_' + cid + ' a').removeClass('ag_cats_selected');
			$(this).addClass('ag_cats_selected');

			ag_coll_manual_filter(cid, sel, cont_id);

			// if there's a dropdown filter - select option 
			if ($('#agmf_' + cid).length) {
				$('#agmf_' + cid + ' option').removeAttr('selected');

				if ($(this).attr('rel') !== '*') {
					$('#agmf_' + cid + ' option[value=' + $(this).attr('rel') + ']').attr('selected', 'selected');
				}
			}
		});

		$('body').delegate('.ag_coll_table_cell .ag_mobile_filter_dd', 'change', function (e) {
			var cid = $(this).parents('.ag_mobile_filter').attr('id').substr(5);
			var sel = $(this).val();
			var cont_id = '#' + $(this).parents('.ag_collection_wrap').attr('id');

			ag_coll_manual_filter(cid, sel, cont_id);

			// select related desktop filter's button
			var btn_to_sel = ($(this).val() == '*') ? '.agf_all' : '.agf_id_' + sel
			$('#agf_' + cid + ' a').removeClass('ag_cats_selected');
			$('#agf_' + cid + ' ' + btn_to_sel).addClass('ag_cats_selected');
		});
	});


	// manual collections filter - perform
	var ag_coll_manual_filter = function (cid, sel, cont_id) {

		// set deeplink
		if (sel !== '*') { ag_set_deeplink('cat', sel); }
		else { ag_clear_deeplink(); }

		if (sel !== '*') { sel = '.agc_' + sel; }
		$(cont_id + ' .ag_coll_container').isotope({ filter: sel });
	};


	/////////////////////////////////////////////////
	// show gallery/collection images (selection = attribute to use recursively to filter images to show)

	$.fn.ag_display_images = function (gid, selection) {

		// no gid == collection | if no selection, check whether to show before filtered 
		if (typeof (gid) != 'undefined' && typeof (ag_gall_curr_filter[gid]) != 'undefined' && ag_gall_curr_filter[gid] && typeof (selection) == 'undefined') {

			this.ag_display_images(gid, ':not(.ag_excluded_img)');
			this.ag_display_images(gid, '.ag_excluded_img');
			return true;
		}

		// apply some filter?
		var $subj = (typeof (selection) == 'undefined') ? this : $(this).filter(selection);

		// show		
		$subj.each(function (i, v) {
			var $subj = $(this);
			var delay = (typeof (ag_delayed_fx) != 'undefined' && ag_delayed_fx) ? 170 : 0;

			setTimeout(function () {
				if (navigator.appVersion.indexOf("MSIE 8.") != -1 || navigator.appVersion.indexOf("MSIE 9.") != -1) {
					$subj.fadeTo(450, 1);
				}
				$subj.addClass('ag_shown');
			}, (delay * i));
		});
	};


	// remove loaders and slide down gallery
	ag_gallery_slideDown = function (gid, img_num, is_collection) {
		if (typeof (ag_gall_is_showing[gid]) != 'undefined' && ag_gall_is_showing[gid]) {
			return false;
		}

		var fx_time = img_num * 200;
		var $subj = (typeof (is_collection) == 'undefined') ? $('#' + gid + ' .ag_container') : $('#' + gid + ' .ag_coll_container');

		$subj.animate({ "min-height": 80 }, 300, 'linear').animate({ "max-height": 9999 }, 6500, 'linear');
		ag_gall_is_showing[gid] = setTimeout(function () {
			if ( // fix for old safari
				navigator.appVersion.indexOf("Safari") == -1 ||
				(navigator.appVersion.indexOf("Safari") != -1 && navigator.appVersion.indexOf("Version/5.") == -1 && navigator.appVersion.indexOf("Version/4.") == -1)
			) {
				$subj.css('min-height', 'none');
			}

			$subj.stop().css('max-height', 'none');
			ag_gall_is_showing[gid] = false;
		}, fx_time);


		if (ag_new_images[gid]) {
			setTimeout(function () {
				ag_new_images[gid] = 0;
				$('#' + gid + ' .ag_paginate > div').fadeTo(150, 1);
			}, 500);
		}

		ag_shown_gall[gid] = true;
	};


	/////////////////////////////////////
	// collections deeplinking

	// get collection filters deeplink
	function ag_get_cf_deeplink(browser_history) {
		var hash = location.hash;
		if (hash == '' || hash == '#ag') { return false; }

		var arr = hash.split('/'); // ignore names
		hash = arr[0];

		if ($('.ag_filter').length) {
			$('.ag_gallery_wrap').each(function () {
				var cid = $(this).attr('id');
				var val = hash.substring(hash.indexOf('#ag_cf') + 7, hash.length)

				// check the cat existence
				if (hash.indexOf('#ag_cf') !== -1) {
					if ($('#' + cid + ' .ag_filter a[rel=' + val + ']').length) {
						var sel = '.agc_' + $('#' + cid + ' .ag_filter a[rel=' + val + ']').attr('rel');

						// filter
						$('#' + cid + ' .ag_coll_container').isotope({ filter: sel });

						// set the selected
						$('#' + cid + ' .ag_filter a').removeClass('ag_cats_selected');
						$('#' + cid + ' .ag_filter a[rel=' + val + ']').addClass('ag_cats_selected');
					}
				}
			});
		}
	};


	// get collection galleries - deeplink
	function ag_get_cg_deeplink(browser_history) { // coll selection
		var hash = location.hash;
		if (hash == '' || hash == '#ag') { return false; }

		var arr = hash.split('/'); // ignore names
		hash = arr[0];

		// call gallery
		if (hash.indexOf('#ag_cg') !== -1) {
			var gid = hash.substring(7);
			// check the item existence
			if ($('.ag_coll_img[rel=' + gid + ']').length) {
				var cid = $('.ag_coll_img[rel=' + gid + ']').first().parents('.ag_gallery_wrap').attr('id');
				var gdata = $('.ag_coll_img[rel=' + gid + ']').first().data('gall-data');

				ag_load_coll_gallery(cid, gdata);
			}
		}

		// trigger filter
		else if (hash.indexOf('#ag_cf') !== -1) {
			ag_coll_dl_filter = hash.substring(7);

			setTimeout(function () {
				$('.agf_id_' + ag_coll_dl_filter).trigger('click');
			}, 300);
		}
	};


	function ag_set_deeplink(subj, val) {
		if (ag_use_deeplink) {
			ag_clear_deeplink();

			// add text
			if (subj == 'cat') {
				var txt = $('.agf_id_' + val).text();
			} else {
				var txt = ($('.ag_coll_img[rel=' + val + ']').parents('.ag_coll_img_wrap').find('.ag_img_title_under').length) ? $('.ag_coll_img[rel=' + val + ']').parents('.ag_coll_img_wrap').find('.ag_img_title_under').text() : $('.ag_coll_img[rel=' + val + ']').find('.ag_img_title').text();
			}
			txt = (txt && typeof (txt) != 'undefined') ? '/' + encodeURIComponent(txt) : '';

			var ag_hash = (subj == 'cat') ? 'ag_cf' : 'ag_cg';
			location.hash = ag_hash + '_' + val + txt;
		}
	};


	function ag_clear_deeplink() {
		if (ag_use_deeplink) {
			var curr_hash = location.hash;

			// find if a mg hash exists
			if (curr_hash.indexOf('#ag_cg') !== false || curr_hash.indexOf('#ag_cf') !== false) {
				location.hash = 'ag';
			}
		}
	};



	//////////////////////////////////////
	// pagination

	$(document).ready(function () {
		//// standard pagination - next
		$('body').delegate('.ag_next_page', 'click', function () {
			var gid = $(this).parents('.ag_gallery_wrap').attr('id');

			if (!$(this).hasClass('ag_pag_disabled') && ag_is_paginating[gid] == 0) {
				var curr_page = (typeof (ag_gallery_pag[gid]) == 'undefined') ? 1 : ag_gallery_pag[gid];
				ag_standard_pagination(gid, (curr_page + 1));

				ag_gallery_pag[gid] = curr_page + 1;
			}
		});
		// standard pagination - prev
		$('body').delegate('.ag_prev_page', 'click', function () {
			var gid = $(this).parents('.ag_gallery_wrap').attr('id');

			if (!$(this).hasClass('ag_pag_disabled') && ag_is_paginating[gid] == 0) {
				var curr_page = (typeof (ag_gallery_pag[gid]) == 'undefined') ? 1 : ag_gallery_pag[gid];
				var new_pag = ((curr_page - 1) < 1) ? 1 : (curr_page - 1);

				ag_standard_pagination(gid, new_pag);

				ag_gallery_pag[gid] = new_pag;
			}
		});

		// numbered buttons - handle click
		$(document).ready(function () {
			$('body').delegate('.ag_num_btns_wrap > div', 'click', function () {
				var gid = $(this).parents('.ag_gallery_wrap').attr('id');

				if (!$(this).hasClass('ag_pag_disabled') && ag_is_paginating[gid] == 0) {
					ag_gallery_pag[gid] = $(this).attr('rel');
					ag_standard_pagination(gid, ag_gallery_pag[gid]);
				}
			});
		});

		// dots - handle click
		$(document).ready(function () {
			$('body').delegate('.ag_dots_pag_wrap > div', 'click', function () {
				var gid = $(this).parents('.ag_gallery_wrap').attr('id');

				if (!$(this).hasClass('ag_pag_disabled') && ag_is_paginating[gid] == 0) {
					ag_gallery_pag[gid] = $(this).attr('rel');
					ag_standard_pagination(gid, ag_gallery_pag[gid]);
				}
			});
		});
	});


	// standard / num buttons / dots pagination - do pagination
	//// applying_filter == recall any item matching that tag and discard pagination parameters (but take advantage of the structure and fx)
	ag_standard_pagination = function (gid, new_pag, applying_filter) {
		applying_filter = (typeof (applying_filter) == 'undefined') ? false : applying_filter;

		if ($('#' + gid).hasClass('ag_filtering_imgs') || ag_is_paginating[gid]) {
			console.error('AG - wait till previous tag filter or pagination to end');
			return false;
		}

		ag_is_paginating[gid] = 1;
		$('#' + gid).removeClass('ag_noresult');

		// setup cache array
		if (typeof (ag_gall_pag_cache[gid]) == 'undefined') {
			ag_gall_pag_cache[gid] = [];
		}

		// pagenum visibility management
		if (!applying_filter) {
			ag_pagenum_btn_vis(gid);
		}

		// smooth change effect
		var curr_h = $('#' + gid + ' .ag_container').height();
		var smooth_timing = Math.round((curr_h / 30) * 25);
		if (smooth_timing < 220) { smooth_timing = 220; }

		if (typeof (ag_gall_is_showing[gid]) != 'undefined') {
			clearTimeout(ag_gall_is_showing[gid]);
			ag_gall_is_showing[gid] = false;
		}

		$('#' + gid + ' .ag_container').css('max-height', curr_h).stop().animate({ "max-height": 150 }, smooth_timing);



		var is_closing = true
		setTimeout(function () { is_closing = false; }, smooth_timing);

		// hide images
		$('#' + gid + ' .ag_img').addClass('ag_old_page');
		if (navigator.appVersion.indexOf("MSIE 8.") != -1 || navigator.appVersion.indexOf("MSIE 9.") != -1) {
			$('#' + gid + ' .ag_img').fadeTo(200, 0);
		}

		// show loader
		setTimeout(function () {
			$('#' + gid + ' .ag_loader').fadeIn('fast');
		}, 200);

		// destroy the old isotope layout
		setTimeout(function () {
			if ($('#' + gid).hasClass('ag_masonry_gallery')) {
				$('#' + gid + ' .ag_container').isotope('destroy');
			}
		}, (smooth_timing - 10));

		// scroll to the top of the gallery
		if ($(window).scrollTop() > ($("#" + gid).offset().top - 20)) {
			$('html,body').animate({ scrollTop: ($("#" + gid).offset().top - 20) }, smooth_timing);
		}

		// manage disabled for standard pag system
		if (!applying_filter) {
			if ($('#' + gid + ' .ag_standard_pag').length) {
				if (new_pag >= parseInt($('#' + gid + ' .ag_paginate').data('ag-totpages'))) {
					$('#' + gid + ' .ag_paginate').find('.ag_next_page').addClass('ag_pag_disabled');
				} else {
					$('#' + gid + ' .ag_paginate').find('.ag_next_page').removeClass('ag_pag_disabled');
				}

				if (new_pag == 1) {
					$('#' + gid + ' .ag_paginate').find('.ag_prev_page').addClass('ag_pag_disabled');
				} else {
					$('#' + gid + ' .ag_paginate').find('.ag_prev_page').removeClass('ag_pag_disabled');
				}
			}
			// manage for numbered buttons and dots
			else {
				$('#' + gid + ' .ag_num_btns_wrap > div, #' + gid + ' .ag_dots_pag_wrap > div').removeClass('ag_pag_disabled');
				$('#' + gid + ' .ag_num_btns_wrap > div[rel=' + new_pag + '], #' + gid + ' .ag_dots_pag_wrap > div[rel=' + new_pag + ']').addClass('ag_pag_disabled');
			}
		}

		// set new pag number 
		if (!applying_filter) {
			$('#' + gid + ' .ag_nav_mid span').text(new_pag);
		}


		// cache index
		var cache_index = (!applying_filter || (applying_filter == '*' && typeof (new_pag) == 'number')) ? new_pag : applying_filter;

		// check in cache
		if (typeof (ag_gall_pag_cache[gid][cache_index]) != 'undefined' && typeof (new_pag) == 'number') {
			fill_standard_pagination(gid, ag_gall_pag_cache[gid][cache_index], new_pag, smooth_timing, applying_filter);
		}
		else {
			// get new datas
			var data = {
				gid: $("#" + gid).attr('rel'),
				ag_type: 'ag_pagination',
				ag_page: new_pag,
				ag_ol: $('#' + gid).data('ag_ol'),
				ag_pag_vars: ag_pag_vars[gid]
			};

			if (applying_filter && applying_filter != '*') {
				data.ag_filtered_imgs = applying_filter;
			}

			$.post(window.location.href, data, function (response) {
				ag_gall_pag_cache[gid][cache_index] = response;
				fill_standard_pagination(gid, response, new_pag, smooth_timing, applying_filter);
			});
		}
	};

	// standard / num buttons / dots pagination - fill gallery with new page images
	var fill_standard_pagination = function (gid, data, new_pag, delay, applying_filter) {
		setTimeout(function () {
			$('#' + gid + ' .ag_paginate .ag_loader').remove();

			resp = $.parseJSON(data);
			$('#' + gid + ' .ag_container').html(resp.html);

			// if old IE, hide secondary overlay
			if (ag_is_old_IE()) {
				$('.ag_sec_overlay').hide();
			}

			// was gallery filtered? re-apply
			if (!applying_filter && typeof (ag_gall_curr_filter[gid]) != 'undefined' && ag_gall_curr_filter[gid]) {
				ag_tag_filter(gid, ag_gall_curr_filter[gid], true);

				if ($('#' + gid).hasClass('ag_noresult')) {
					$('#' + gid + ' .ag_loader').fadeOut('fast');
				}
			}

			ag_new_images[gid] = 1;
			ag_gallery_process(gid);
			ag_is_paginating[gid] = 0;
		}, delay);
	};



	// track galleries width and avoid pagenum and dots to go on two lines
	var ag_pagenum_btn_vis = function (gid) {
		if (!$('#' + gid).find('.ag_num_btns_wrap, .ag_dots_pag_wrap').length) {
			return false;
		}

		var $pag_wrap = $('#' + gid).find('.ag_paginate');
		var $btns_wrap = $('#' + gid).find('.ag_num_btns_wrap, .ag_dots_pag_wrap');
		var $btns = $btns_wrap.find('> div');

		// reset
		$btns_wrap.removeClass('ag_hpb_after ag_hpb_before');
		$btns.removeClass('ag_hidden_pb');


		// there must be at least 5 buttons
		if ($btns.length <= 5) { return false; }

		// calculate overall btns width
		var btns_width = 0;
		$btns.each(function () {
			btns_width += $(this).outerWidth(true) + 1; // add 1px to avoid any issue
		});

		// act if is wider
		if (btns_width > $pag_wrap.outerWidth()) {
			var $sel_btn = $('#' + gid + ' .ag_pag_disabled');
			var curr_pag = parseInt($sel_btn.attr('rel'));
			var tot_pages = parseInt($btns.last().attr('rel'));

			// side "there's more" dots width
			var dots_w = (curr_pag <= 2 || curr_pag >= (tot_pages - 1)) ? 26 : 52; // width = 16px + add 10px margin || 52 is the double

			var diff = btns_width + dots_w - $pag_wrap.outerWidth();
			var last_btn_w = $btns.last().outerWidth(true);
			var to_hide = Math.ceil(diff / last_btn_w);

			// manage pag btn visibility
			if (curr_pag <= 2 || curr_pag >= (tot_pages - 1)) {
				var to_hide_sel = [];

				if (curr_pag <= 2) {
					$btns_wrap.addClass('ag_hpb_after');

					for (a = 0; a < to_hide; a++) {
						to_hide_sel.push('[rel=' + (tot_pages - a) + ']');
					}
				}
				else if (curr_pag >= (tot_pages - 1)) {
					$btns_wrap.addClass('ag_hpb_before');

					for (a = 0; a < to_hide; a++) {
						to_hide_sel.push('[rel=' + (1 + a) + ']');
					}
				}

				$btns.filter(to_hide_sel.join(',')).addClass('ag_hidden_pb');

			}
			else {
				$btns_wrap.addClass('ag_hpb_before ag_hpb_after');
				var to_keep_sel = ['[rel=' + curr_pag + ']'];

				// use opposite system: selected is the center and count how to keep 
				var to_keep = tot_pages - to_hide;
				var to_keep_pre = Math.floor(to_keep / 2);
				var to_keep_post = Math.ceil(to_keep / 2);

				// if pre/post already reaches the edge, sum remaining ones on the other side
				var reach_pre = curr_pag - to_keep_pre;
				var reach_post = curr_pag + to_keep_post;

				if (reach_pre <= 1) {
					$btns_wrap.removeClass('ag_hpb_before');
					to_keep_post = to_keep_post + (reach_pre * -1 + 1);
				}
				else if (reach_post >= tot_pages) {
					$btns_wrap.removeClass('ag_hpb_after');
					to_keep_pre = to_keep_pre + (reach_post - tot_pages);
				}

				for (a = 1; a <= to_keep_pre; a++) {
					to_keep_sel.push('[rel=' + (curr_pag - a) + ']');
				}
				for (a = 1; a <= to_keep_post; a++) {
					to_keep_sel.push('[rel=' + (curr_pag + a) + ']');
				}

				$btns.not(to_keep_sel.join(',')).addClass('ag_hidden_pb');
			}
		}
	};



	//// infinite scroll
	$(document).ready(function () {
		$('body').delegate('.ag_infinite_scroll', 'click', function () {
			var gid = $(this).parents('.ag_gallery_wrap').attr('id');
			var curr_url = window.location.href;

			if ($('#' + gid).hasClass('ag_filtering_imgs') || ag_is_paginating[gid]) {
				console.error('AG - wait till previous tag filter or pagination to end');
				return false;
			}
			ag_is_paginating[gid] = 1;


			$('#' + gid + ' .ag_container').css('max-height', $('#' + gid + ' .ag_container').height());

			// hide nav and append loader
			if ($('#' + gid + ' .ag_paginate .ag_loader').length) { $('#' + gid + ' .ag_paginate .ag_loader').remove(); }
			$('#' + gid + ' .ag_infinite_scroll').fadeTo(200, 0);
			setTimeout(function () {
				$('#' + gid + ' .ag_paginate').prepend(ag_loader);
			}, 200);

			// set the page to show
			if (typeof (ag_gallery_pag[gid]) == 'undefined') {
				var next_pag = 2;
				ag_gallery_pag[gid] = next_pag;
			} else {
				var next_pag = ag_gallery_pag[gid] + 1;
				ag_gallery_pag[gid] = next_pag;
			}

			var data = {
				gid: $("#" + gid).attr('rel'),
				ag_type: 'ag_pagination',
				ag_page: next_pag,
				ag_ol: $('#' + gid).data('ag_ol'),
				ag_pag_vars: ag_pag_vars[gid]
			};
			$.post(window.location.href, data, function (response) {
				resp = $.parseJSON(response);

				if ($('#' + gid).hasClass('ag_string_gallery')) {
					$('#' + gid + ' .ag_container .ag_string_clear_both').remove();
					$('#' + gid + ' .ag_container').append(resp.html);
					$('#' + gid + ' .ag_container').append('<div class="ag_string_clear_both" style="clear: both;"></div>');
				}
				else {
					$('#' + gid + ' .ag_container').append(resp.html);
				}

				if ($('#' + gid).hasClass('ag_masonry_gallery')) {
					$('#' + gid + ' .ag_container').isotope('reloadItems');
				}

				// if old IE, hide secondary overlay
				if (ag_is_old_IE()) { $('.ag_sec_overlay').hide(); }


				// was gallery filtered? re-apply
				if (typeof (ag_gall_curr_filter[gid]) != 'undefined' && ag_gall_curr_filter[gid]) {
					ag_tag_filter(gid, ag_gall_curr_filter[gid], true);

					if ($('#' + gid).hasClass('ag_noresult')) {
						$('#' + gid + ' .ag_loader').fadeOut('fast');
					}
				}


				ag_is_paginating[gid] = 0;
				ag_new_images[gid] = 1;
				ag_gallery_process(gid);


				if (resp.more != '1') {
					$('#' + gid + ' .ag_paginate').hide();
				}
				else {
					$('#' + gid + ' .ag_paginate .ag_loader').remove();
					$('#' + gid + ' .ag_infinite_scroll').fadeTo(200, 1);
				}
			});
		});
	});



	///////////////////////////////////////////////////////


	// GALLERY TAGS FILTER
	$(document).ready(function () {

		// tags filter through tag click
		$('body').delegate('.ag_tag:not(.ag_tag_sel)', 'click', function (e) {
			var gid = $(this).parents('.ag_tags_wrap').data('gid');
			var tag = $(this).data('tag');

			if (tag == '*') {
				var img_indexes = '*';
			}
			else {
				var raw_target_imgs = $(this).data('images').toString();
				var img_indexes = raw_target_imgs.split(',');
			}

			// perform and manage tag selection
			if (ag_tag_filter(gid, img_indexes)) {
				$(this).parents('.ag_tags_wrap').find('.ag_tag_sel').removeClass('ag_tag_sel');
				$(this).addClass('ag_tag_sel');
			}

			// if there's a dropdown filter - select option 
			if ($(this).parents('.ag_tags_wrap').find('.ag_tags_dd').length) {
				$(this).parents('.ag_tags_wrap').find('.ag_tags_dd option').removeAttr('selected');

				if (tag !== '*') {
					$(this).parents('.ag_tags_wrap').find('.ag_tags_dd option[value="' + tag + '"]').attr('selected', 'selected');
				}
			}
		});


		// tag filter using mobile dropdown
		$('body').delegate('.ag_tags_dd', 'change', function (e) {
			var $wrap = $(this).parents('.ag_tags_wrap');
			var gid = $wrap.data('gid');

			var raw_target_imgs = $wrap.find('.ag_tag[data-tag="' + $(this).val() + '"]').data('images'); // match filters to avoid misleading equal arrays
			var img_indexes = (raw_target_imgs == '*') ? raw_target_imgs : raw_target_imgs.split(',');

			if (ag_tag_filter(gid, img_indexes)) {
				$wrap.find('.ag_tag_sel').removeClass('ag_tag_sel');
				$wrap.find('.ag_tag[data-images="' + raw_target_imgs + '"]').addClass('ag_tag_sel');
			}
			else {
				return false;
			}
		});
	});


	// performs the filter
	function ag_tag_filter(gid, matched_imgs_index, on_pagination) {
		var $gall = $('#' + gid);

		// is filtering? wait
		if ($gall.hasClass('ag_filtering_imgs') && (typeof (on_pagination) == 'undefined' && ag_is_paginating[gid])) {
			console.error('AG - wait till previous tag filter or pagination to end');
			return false;
		}


		// filter reset
		if (matched_imgs_index == '*') {
			ag_gall_curr_filter[gid] = '';
			$gall.find('.ag_paginate').css('visibility', 'visible');

			// if is an ajax filtered - recall original page
			if ($gall.hasClass('ag_ajax_filtered')) {
				$gall.removeClass('ag_ajax_filtered');

				if (typeof (ag_gallery_pag[gid]) == 'undefined') {
					ag_gallery_pag[gid] = 1;
				}

				// if is infinite scroll - pass an array
				var pag_to_restore = ($gall.find('.ag_infinite_scroll').length) ? [1, ag_gallery_pag[gid]] : ag_gallery_pag[gid];
				ag_standard_pagination(gid, pag_to_restore, '*');
			}
			else {
				local_tags_filter($gall, '*');
			}
		}

		// filter
		else {
			ag_gall_curr_filter[gid] = matched_imgs_index;

			// every matched image is already in the gallery?
			var all_matched_showing = true;
			$.each(matched_imgs_index, function (i, v) {

				if (!$('#' + gid + ' .ag_img[data-img-id="' + v + '"]').length) {
					all_matched_showing = false;
					return false;
				}
			});


			// forced local filter or if every matched image is showing
			if (all_matched_showing || ag_monopage_filter) {
				local_tags_filter($gall, matched_imgs_index, on_pagination);
			}

			// ajax filter recallingany image
			else {
				$gall.addClass('ag_ajax_filtered');
				ag_standard_pagination(gid, '*', matched_imgs_index);
			}

			$gall.find('.ag_paginate').css('visibility', 'hidden');
		}

		return true;
	};


	// local filter (animate and eventualy show "no results")
	var local_tags_filter = function ($gall, matched_imgs_index, on_pagination) {

		var gid = $gall.attr('id');
		var $container = $gall.find('.ag_container');
		var fakebox_id = 'ag_fakebox_' + gid;
		var string_gall = $gall.hasClass('ag_string_gallery');
		var matched_count = 0;

		// masonry gallery - just manage class
		if ($gall.hasClass('ag_masonry_gallery')) {
			$gall.addClass('ag_filtering_imgs');

			$gall.find('.ag_img').each(function () {
				var img_id = $(this).data('img-id');

				if (matched_imgs_index == '*' || $.inArray(img_id.toString(), matched_imgs_index) !== -1) {
					$(this).removeClass('ag_excluded_img');
					matched_count++;
				}
				else {
					$(this).addClass('ag_excluded_img');
				}
			});

			$container.isotope({ filter: ':not(.ag_excluded_img)' });
		}


		// other layouts
		else {

			$container.css('height', $container.outerHeight());

			// create a fake container recreating the new layout
			var fakebox_align = ($gall.hasClass('ag_standard_gallery')) ? 'text-align: center;' : '';
			var fb_w = (string_gall) ? $gall.outerWidth(true) : $container.outerWidth(true);
			$('body').append('<div id="' + fakebox_id + '" class="ag_filter_fakebox" style="width: ' + fb_w + 'px; ' + fakebox_align + '"></div>');


			// photostring - copy the whole gallery into fakebox
			if (string_gall) {
				$('#' + fakebox_id).html($gall.clone());
				$('#' + fakebox_id + ' .ag_string_gallery').removeAttr('id');
				$('#' + fakebox_id + ' .ag_img').removeClass('ag_excluded_img').removeAttr('style');
			}


			// prepend placeholders to prepare new positions
			$gall.find('.ag_img').each(function () {
				var $img = $(this);
				var img_id = $img.data('img-id');

				if (matched_imgs_index == '*' || $.inArray(img_id.toString(), matched_imgs_index) !== -1) {
					matched_count++;

					if (!string_gall) {
						$('#' + fakebox_id).append('<div style="display: inline-block; width: ' + $img.outerWidth(true) + 'px; height: ' + $img.outerHeight(true) + 'px;" data-img-id="' + img_id + '"></div>');
					}
				}

				// for photostring remove discarded images
				else {
					$('#' + fakebox_id).find('[data-img-id="' + img_id + '"]').remove();
				}


				var pos = $img.position();
				$img.css({
					left: pos.left + 'px',
					top: pos.top + 'px',
				});
			});
			$gall.find('.ag_img').css('position', 'absolute');


			// wait a bit to let CSS to propagate
			setTimeout(function () {
				$gall.addClass('ag_filtering_imgs');

				// photostring - relayout fakebox gallery to get new positions
				if (matched_count && string_gall && typeof (on_pagination) == 'undefined') {
					layout_photostr_gall(false, '#' + fakebox_id);
				}


				// cycle again applying new positions and hiding others
				$gall.find('.ag_img').each(function () {
					var img_id = $(this).data('img-id');

					if (matched_imgs_index == '*' || $.inArray(img_id.toString(), matched_imgs_index) !== -1) {

						var newpos = $('#' + fakebox_id + ' [data-img-id="' + img_id + '"]').position();
						$(this).css({
							left: newpos.left + 'px',
							top: newpos.top + 'px'

						});

						$(this).removeClass('ag_excluded_img');
					}

					else {
						$(this).css({
							left: 'auto',
							top: 'auto'
						});

						$(this).addClass('ag_excluded_img');
					}
				});

				// animate new container's height
				var new_cont_h = ($('#' + fakebox_id + ' div').length) ? $('#' + fakebox_id + ' div').last().position().top + $('#' + fakebox_id + ' div').last().height() : 100;
				$container.css('height', new_cont_h);

				// if photostring - animate image to shape them
				if (matched_count && string_gall && typeof (on_pagination) == 'undefined') {
					layout_photostr_gall(gid);
				}
			}, 50);
		}


		// no matched?  show "no results in this page"
		if (!matched_count) {
			$gall.addClass('ag_noresult');
		} else {
			$gall.removeClass('ag_noresult');
		}


		// remove filtering animation class
		setTimeout(function () {
			$gall.removeClass('ag_filtering_imgs');

			if (!$gall.hasClass('ag_masonry_gallery')) {
				$container.css('height', 'auto');

				$gall.find('.ag_img').not('.ag_excluded_img').css('position', 'static');
				$('#' + fakebox_id).remove();
			}
		}, 500);
	};



	///////////////////////////////////////////////////////



	//  primary overlay check - if no title hide
	ag_check_primary_ol = function (gid, respect_delay) {
		$('#' + gid + ' .ag_img').each(function (i, e) {
			var $ol_subj = $(this);

			if (!$.trim($ol_subj.find('.ag_img_title').html())) {
				$ol_subj.find('.ag_main_overlay').hide();
			} else {
				$ol_subj.find('.ag_main_overlay').show();
			}
		});
	}



	///////////////////////////////////////////////////////



	// images effects
	ag_img_fx_setup = function (gid, width_arr, height_arr) {
		var fx_timing = $('#' + gid).data('agom_timing');

		if (typeof ($('#' + gid).data('agom_fx')) != 'undefined' && $('#' + gid).data('agom_fx').indexOf('grayscale') != -1) {

			// create and append grayscale image
			$('#' + gid + ' .ag_main_thumb').each(function (i, v) {
				if ($(this).parents('.ag_img').find('.ag_fx_canvas.ag_grayscale_fx ').length == 0) {
					var img = new Image();
					img.onload = function (e) {
						Pixastic.process(img, "desaturate", { average: false });
					}

					$(img).addClass('ag_photo ag_grayscale_fx ag_fx_canvas');
					$(this).before(img);

					if (navigator.appVersion.indexOf("MSIE 9.") != -1 || navigator.appVersion.indexOf("MSIE 10.") != -1) {
						if ($(this).parents('.ag_img').hasClass('ag_car_item')) {
							$(this).parents('.ag_img').find('.ag_fx_canvas').css('width', width_arr[i]);
						}
						else {
							$(this).parents('.ag_img').find('.ag_fx_canvas').css('max-width', width_arr[i]).css('max-height', height_arr[i]);

							if ($(this).parents('.ag_gallery_wrap').hasClass('ag_collection_wrap')) {
								$(this).parents('.ag_img').find('.ag_fx_canvas').css('min-width', width_arr[i]).css('min-height', height_arr[i]);
							}
						}
					}

					img.src = $(this).attr('src');
				}
			});

			// mouse hover opacity
			$('#' + gid).delegate('.ag_img', 'mouseenter touchstart', function (e) {
				if (!ag_is_old_IE()) {
					$(this).find('.ag_grayscale_fx').stop().animate({ opacity: 0 }, fx_timing);
				} else {
					$(this).find('.ag_grayscale_fx').stop().fadeOut(fx_timing);
				}
			}).
				delegate('.ag_img', 'mouseleave touchend', function (e) {
					if (!ag_is_old_IE()) {
						$(this).find('.ag_grayscale_fx').stop().animate({ opacity: 1 }, fx_timing);
					} else {
						$(this).find('.ag_grayscale_fx').stop().fadeIn(fx_timing);
					}
				});
		}

		if (typeof ($('#' + gid).data('agom_fx')) != 'undefined' && $('#' + gid).data('agom_fx').indexOf('blur') != -1) {

			// create and append blurred image
			$('#' + gid + ' .ag_main_thumb').each(function (i, v) {
				if ($(this).parents('.ag_img').find('.ag_fx_canvas.ag_blur_fx ').length == 0) {
					var img = new Image();
					img.onload = function () {
						Pixastic.process(img, "blurfast", { amount: 0.15 });
					}

					$(img).addClass('ag_photo ag_blur_fx ag_fx_canvas').attr('style', 'opacity: 0; filter: alpha(opacity=0);');
					$(this).before(img);

					if (navigator.appVersion.indexOf("MSIE 9.") != -1 || navigator.appVersion.indexOf("MSIE 10.") != -1) {
						if ($(this).parents('.ag_img').hasClass('ag_car_item')) {
							$(this).parents('.ag_img').find('.ag_fx_canvas').css('width', width_arr[i]);
						}
						else {
							$(this).parents('.ag_img').find('.ag_fx_canvas').css('max-width', width_arr[i]).css('max-height', height_arr[i]);

							if ($(this).parents('.ag_gallery_wrap').hasClass('ag_collection_wrap')) {
								$(this).parents('.ag_img').find('.ag_fx_canvas').css('min-width', width_arr[i]).css('min-height', height_arr[i]);
							}
						}
					}

					img.src = $(this).attr('src');
				}
			});


			// mouse hover opacity
			$('#' + gid).delegate('.ag_img', 'mouseenter touchstart', function (e) {
				if (!ag_is_old_IE()) {
					$(this).find('.ag_blur_fx').stop().animate({ opacity: 1 }, fx_timing);
				} else {
					$(this).find('.ag_blur_fx').stop().fadeIn(fx_timing);
				}
			}).
				delegate('.ag_img', 'mouseleave touchend', function (e) {
					if (!ag_is_old_IE()) {
						$(this).find('.ag_blur_fx').stop().animate({ opacity: 0 }, fx_timing);
					} else {
						$(this).find('.ag_blur_fx').stop().fadeOut(fx_timing);
					}
				});
		}
	}


	/////////////////////////////////////


	// touch devices hover effects
	if (ag_is_touch_device()) {
		$('.ag_img').bind('touchstart', function () { $(this).addClass('ag_touch_on'); });
		$('.ag_img').bind('touchend', function () { $(this).removeClass('ag_touch_on'); });
	}

	// check for touch device
	function ag_is_touch_device() {
		return !!('ontouchstart' in window);
	}



	/////////////////////////////////////
	// image-to-gallery functions

	ag_itg_init = function (id) {
		lazy_to_img_url(id);

		$('#' + id + ' .ag_img .ag_main_thumb').lcweb_lazyload({
			allLoaded: function (url_arr, width_arr, height_arr) {

				$('#' + id + ' .ag_itg_container').addClass('ag_itg_shown');
			}
		});
	};


	// launch lightbox
	$(document).delegate('.ag_itg_wrap', 'click', function (e) {
		var id = $(this).attr('id');

		// which index?
		if ($(e.terget).hasClass('ag_itg_img')) {
			var clicked_index = $(e.target).data('index');
		}
		else if ($(e.target).parents('.ag_itg_img').length) {
			var clicked_index = $(e.target).parents('.ag_itg_img').data('index');
		}
		else {
			var clicked_index = 0;
		}

		ag_throw_lb(ag_itg_obj[id], id, clicked_index, true);
	});





	/////////////////////////////////////
	// galleria slider functions

	// manage the slider initial appearance
	ag_galleria_show = function (sid) {
		setTimeout(function () {
			if ($(sid + ' .galleria-stage').length) {
				$(sid).removeClass('ag_show_loader');
				$(sid + ' .galleria-container').fadeTo(200, 1);
			} else {
				ag_galleria_show(sid);
			}
		}, 50);
	}


	// manage the slider proportions on resize
	ag_galleria_height = function (sid) {
		if ($(sid).hasClass('ag_galleria_responsive')) {
			return parseFloat($(sid).data('asp-ratio'));
		} else {
			return $(sid).height();
		}
	}


	// Initialize Galleria
	ag_galleria_init = function (sid) {
		// autoplay flag
		var spec_autop = $(sid).data('ag-autoplay');
		var sl_autoplay = ((ag_galleria_autoplay && spec_autop != '0') || (spec_autop == '1')) ? true : false;

		// init
		Galleria.run(sid, {
			theme: 'agallery',
			height: ag_galleria_height(sid),
			fullscreenDoubleTap: false,
			wait: true,
			debug: false,

			// avoid using ALT for description
			dataConfig: function (img) {
				return {
					title: $(img).attr('alt'),
					description: $(img).data('description')
				};
			},

			// customizations
			extend: function () {
				var ag_slider_gall = this;
				$(sid + ' .galleria-loader').append(ag_loader);

				if (sl_autoplay) {

					setTimeout(function () {
						$(sid + ' .galleria-ag-play').addClass('galleria-ag-pause')
						ag_slider_gall.play(ag_galleria_interval);
					}, 50);
				}

				// play-pause
				$(sid + ' .galleria-ag-play').click(function () {
					$(this).toggleClass('galleria-ag-pause');
					ag_slider_gall.playToggle(ag_galleria_interval);
				});

				// pause slider on lightbox click
				$(sid + ' .galleria-ag-lightbox').click(function () {
					// get the slider offset
					$(sid + ' .galleria-thumbnails > div').each(function (k, v) {
						if ($(this).hasClass('active')) { ag_active_index = k; }
					});

					$(sid + ' .galleria-ag-play').removeClass('galleria-ag-pause');
					ag_slider_gall.pause();
				});

				// thumbs navigator toggle
				$(sid + ' .galleria-ag-toggle-thumb').click(function () {
					var $ag_slider_wrap = $(this).parents('.ag_galleria_slider_wrap');
					var thumb_h = $(this).parents('.ag_galleria_slider_wrap').find('.galleria-thumbnails-container').height();

					if ($ag_slider_wrap.hasClass('galleria-ag-show-thumbs') || $ag_slider_wrap.hasClass('ag_galleria_slider_show_thumbs')) {
						$ag_slider_wrap.stop().animate({ 'padding-bottom': '15px' }, 400);
						$ag_slider_wrap.find('.galleria-thumbnails-container').stop().animate({ 'bottom': '20px', 'opacity': 0 }, 400);

						$ag_slider_wrap.removeClass('galleria-ag-show-thumbs');
						if ($ag_slider_wrap.hasClass('ag_galleria_slider_show_thumbs')) {
							$ag_slider_wrap.removeClass('ag_galleria_slider_show_thumbs')
						}
					}
					else {
						$ag_slider_wrap.stop().animate({ 'padding-bottom': (thumb_h + 2 + 12) }, 400);
						$ag_slider_wrap.find('.galleria-thumbnails-container').stop().animate({ 'bottom': '-' + (thumb_h + 2 + 10) + 'px', 'opacity': 1 }, 400);

						$ag_slider_wrap.addClass('galleria-ag-show-thumbs');
					}
				});

				// LC lightbox - deeplink
				if (typeof (ag_lcl_allow_deeplink) != 'undefined') {
					ag_lcl_allow_deeplink();
				}
			}
		});
	}


	/////////////////////////////////////
	// Slick carousel functions


	// dynamically calculate breakpoints
	ag_car_calc_breakpoints = function (gid, img_max_w, multiscroll, forced_init_cols) {
		var bp = [];

		/* OLD forced sizes? try to find a good way to setup breakpoints */
		if (forced_init_cols) {
			var base_treshold = $("#ag_car_" + gid).width() + 50;
			var base_img_w = Math.round(base_treshold / forced_init_cols);

			var obj = {
				breakpoint: base_treshold,
				settings: {
					slidesToShow: forced_init_cols,
					slidesToScroll: (multiscroll) ? forced_init_cols : 1
				}
			};
			bp.push(obj);

			for (a = forced_init_cols; a >= 1; a--) {

				obj = {
					breakpoint: (base_treshold - (base_img_w * (forced_init_cols - a))),
					settings: {
						slidesToShow: a,
						slidesToScroll: (multiscroll) ? a : 1
					}
				};
				bp.push(obj);
			}
		}

		/* new max-width based */
		else {
			for (a = 1; a < 100; a++) {
				var overall_w = a * img_max_w;
				if (overall_w > 2000) { break; }

				var obj = {
					breakpoint: overall_w,
					settings: {
						slidesToShow: a,
						slidesToScroll: (multiscroll) ? a : 1
					}
				};

				bp.push(obj);
			}
		}

		return bp;
	};


	/* preload visible images */
	ag_carousel_preload = function (gid, autoplay) {
		$('#ag_car_' + gid).prepend(ag_loader);

		// apply effects
		if (!$('#ag_car_' + gid + ' .ag_grayscale_fx').length && !$('#ag_car_' + gid + ' .ag_blur_fx').length) {
			$('#ag_car_' + gid + ' img').lcweb_lazyload({
				allLoaded: function (url_arr, width_arr, height_arr) {
					var true_h = $('#ag_car_' + gid + ' .ag_img_inner').height();

					// old IE fix - find true width related to height
					if (navigator.appVersion.indexOf("MSIE 9.") != -1 || navigator.appVersion.indexOf("MSIE 8.") != -1) {
						$.each(width_arr, function (i, v) {
							width_arr[i] = (width_arr[i] * true_h) / height_arr[i];
							height_arr[i] = true_h;
						});
					}

					ag_img_fx_setup('ag_car_' + gid, width_arr, height_arr);
				}
			});
			var wait_for_fx = true;
		}
		else { var wait_for_fx = false; }

		var shown_first = (wait_for_fx) ? '' : '.slick-active';
		$('#ag_car_' + gid + ' ' + shown_first + ' img').lcweb_lazyload({
			allLoaded: function (url_arr, width_arr, height_arr) {
				$('#ag_car_' + gid + ' .ag_loader').fadeOut(200, function () {
					$(this).remove();
				});
				$('#ag_car_' + gid).removeClass('ag_car_preload');

				if (autoplay) {
					$('#ag_car_' + gid).slick('slickPlay');
				}

				// wait and show
				var delay = (wait_for_fx) ? 1200 : 320;
				setTimeout(function () {
					ag_car_center_images(gid);

					$(window).trigger('ag_loaded_carousel', [gid]);
				}, delay);
			}
		});


		// OVERLAY MANAGER ADD-ON //
		if (typeof (agom_hub) == "function") {
			agom_hub(gid);
		}
		////////////////////////////
	};


	var ag_car_center_images = function (subj_id) {
		var subj_sel = (typeof (subj_id) == 'undefined') ? '' : '#ag_car_' + subj_id;

		$(subj_sel + ' .ag_img.ag_car_item').each(function (i, v) {
			var $img = $(this);
			var $elements = $img.find('.ag_main_img_wrap > *');

			var wrap_w = $(this).width();
			var wrap_h = $(this).height();


			$('<img />').bind("load", function () {
				var ratio = Math.max(wrap_w / this.width, wrap_h / this.height);
				var new_w = this.width * ratio;
				var new_h = this.height * ratio;

				var margin_top = Math.ceil((wrap_h - new_h) / 2);
				var margin_left = Math.ceil((wrap_w - new_w) / 2);

				if (margin_top > 0) { margin_top = 0; }
				if (margin_left > 0) { margin_left = 0; }

				$elements.css('width', new_w).css('height', new_h);

				// mark to be shown
				$img.addClass('ag_car_img_ready');

			}).attr('src', $img.find('.ag_main_thumb').attr('src'));

		});
	}


	$(document).ready(function (e) {

		/* pause on hover fix */
		$(document).delegate('.ag_car_pause_on_h', 'mouseenter touchstart', function (e) {
			$(this).slick('slickPause');
		}).
			delegate('.ag_car_pause_on_h', 'mouseleave touchend', function (e) {
				if ($(this).hasClass('ag_car_autoplay')) {
					$(this).slick('slickPlay');
				}
			});

		/* pause on lightbox open */
		$(document).delegate('.ag_carousel_wrap .ag_img:not(.ag_linked_img)', 'click tap', function (e) {
			var $subj = $(this);
			setTimeout(function () {
				$subj.parents('.ag_carousel_wrap').slick('slickPause');
			}, 150);
		});

		// navigating through pages, disable autoplay on mouseleave
		$(document).delegate('.ag_carousel_wrap .slick-arrow, .ag_carousel_wrap .slick-dots li:not(.slick-active)', 'click tap', function (e) {
			$(this).parents('.ag_carousel_wrap').removeClass('ag_car_autoplay');
		});
		$(document).delegate('.ag_carousel_wrap', 'swipe', function (e) {
			$(this).removeClass('ag_car_autoplay');
		});
	});



	/////////////////////////////////////
	// debouncers

	ag_debouncer = function ($, cf, of, interval) {
		var debounce = function (func, threshold, execAsap) {
			var timeout;

			return function debounced() {
				var obj = this, args = arguments;
				function delayed() {
					if (!execAsap) { func.apply(obj, args); }
					timeout = null;
				}

				if (timeout) { clearTimeout(timeout); }
				else if (execAsap) { func.apply(obj, args); }

				timeout = setTimeout(delayed, threshold || interval);
			};
		};
		$.fn[cf] = function (fn) { return fn ? this.bind(of, debounce(fn)) : this.trigger(cf); };
	};


	// bind resize to trigger only once event
	ag_debouncer($, 'ag_smartresize', 'resize', 49);
	$(window).ag_smartresize(function () {

		// resize galleria slider
		$('.ag_galleria_responsive').each(function () {
			var slider_w = $(this).width();
			var ag_asp_ratio = parseFloat($(this).data('asp-ratio'));
			var new_h = Math.ceil(slider_w * ag_asp_ratio);
			$(this).css('height', new_h);
		});
	});


	// bind scroll to keep "back to gallery" button visible
	ag_debouncer($, 'ag_smartscroll', 'scroll', 50);
	$(window).ag_smartscroll(function () {
		ag_keep_back_to_gall_visible();
	});

	var ag_keep_back_to_gall_visible = function () {
		if ($('.ag_coll_back_to_new_style').length && typeof (ag_back_to_gall_scroll) != 'undefined' && ag_back_to_gall_scroll) {
			$('.ag_coll_gallery_container .ag_gallery_wrap').each(function (i, v) {
				var gall_h = $(this).height();
				var $btn = $(this).parents('.ag_coll_gallery_container').find('.ag_coll_go_back');

				if (gall_h > $(window).height()) {

					var offset = $(this).offset();
					if ($(window).scrollTop() > offset.top && $(window).scrollTop() < (offset.top + gall_h - 60)) {
						var top = Math.round($(window).scrollTop() - offset.top) + 55;
						if (top < 0) { top = 0; }

						$btn.addClass('ag_cgb_sticky').css('top', top);
					}
					else { $btn.removeClass('ag_cgb_sticky').css('top', 0); }
				}
				else { $btn.removeClass('ag_cgb_sticky').css('top', 0); }

			});
		}
	}


	// persistent check for galleries collections size change 
	$(document).ready(function () {
		setInterval(function () {
			$('.ag_gallery_wrap').each(function () {
				var gid = $(this).attr('id');
				if (typeof (ag_shown_gall[gid]) == 'undefined') { return true; } // only for shown galleries

				var new_w = ($(this).hasClass('ag_collection_wrap')) ? $('#' + gid + ' .ag_coll_container').width() : $('#' + gid).width();

				if (typeof (ag_gallery_w[gid]) == 'undefined') {

					ag_gallery_w[gid] = new_w;
					return true;
				}

				// trigger only if size is different
				if (ag_gallery_w[gid] != new_w) {
					persistent_resize_debounce(gid);
					ag_gallery_w[gid] = new_w;
				}
			});
		}, 200);
	});

	var persistent_resize_debounce = function (gall_id) {
		if (typeof (ag_debounce_resize[gall_id]) != 'undefined') { clearTimeout(ag_debounce_resize[gall_id]); }


		ag_debounce_resize[gall_id] = setTimeout(function () {
			$('#' + gall_id).trigger('ag_resize_gallery', [gall_id]);
		}, 50);
	}


	// standard AG operations on resize
	$(document).delegate('.ag_gallery_wrap', 'ag_resize_gallery', function (evt, gall_id) {

		// collection galleries title check 	
		if ($(this).hasClass('ag_collection_wrap') && $(this).find('.ag_coll_gallery_container .ag_container').length) {
			ag_coll_gall_title_layout(gall_id);
		}


		// whether to trigger only carousel resizing
		if ($(this).hasClass('ag_carousel_wrap')) {
			ag_car_center_images(gall_id); // carousel images sizing	
		}
		else {
			ag_galleries_init(gall_id, true); // rebuilt galleries on resize	
		}
	});



	/////////////////////////////////////////////////////
	// full-resolution images preloading after galleries

	if (typeof (ag_preload_hires_img) != 'undefined' && ag_preload_hires_img) {
		var $phi_subjs = $('.ag_gallery_wrap, .ag_carousel_wrap');
		var phi_tot_subjs = $phi_subjs.length;
		var phi_loaded = 0;

		if (phi_tot_subjs) {
			$(window).on('ag_loaded_gallery ag_loaded_collection ag_loaded_carousel', function () {
				phi_loaded++;

				if (phi_loaded == phi_tot_subjs) {
					setTimeout(function () {
						$('.ag_img').not('.ag_coll_img, .ag_linked_img').each(function () {
							$('<img />')[0].src = $(this).data('ag-url');
						});
					}, 300);
				}
			});
		}
	}



	/////////////////////////////////////////////////////
	// check if the browser is IE8 or older
	function ag_is_old_IE() {
		if (navigator.appVersion.indexOf("MSIE 7.") != -1 || navigator.appVersion.indexOf("MSIE 8.") != -1) { return true; }
		else { return false; }
	};



	/////////////////////////////////////
	// Lightbox initialization

	// fix for HTML inside attribute
	ag_lb_html_fix = function (str) {
		var txt = (typeof (str) == 'string') ? str.replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;') : '';
		return $.trim(txt);
	};


	// via image click
	$(document).ready(function () {
		$(document).delegate('.ag_gallery_wrap:not(.ag_static_car) div.ag_img:not(.ag_coll_img, .ag_linked_img, .ag_excluded_img)', 'click', function (e) {
			e.preventDefault();
			if ($(e.target).hasClass('.agom_socials') || $(e.target).parents('.agom_socials').length) { return false; }

			var gall_obj = [];

			var $clicked = $(this);
			var rel = $clicked.attr('rel');
			var gid = $clicked.parents('.ag_gallery_wrap').attr('id');
			var clicked_url = $clicked.data('ag-url');
			var clicked_index = 0;

			$('#' + gid + ' .ag_img:not(.ag_coll_img, .ag_linked_img, .ag_excluded_img)').each(function (i, v) {
				var img_url = $(this).data('ag-url');

				if (typeof (gall_obj[img_url]) == 'undefined') {
					gall_obj[img_url] = {
						"img": img_url,
						"title": ag_lb_html_fix($(this).data('ag-title')),
						"descr": ag_lb_html_fix($(this).data('ag-descr')),
						'author': ag_lb_html_fix($(this).data('ag-author'))
					};

					if (img_url == clicked_url) { clicked_index = i; }
				}
			});

			ag_throw_lb(gall_obj, rel, clicked_index);
		});
	});


	// via slider
	ag_slider_lightbox = function (data, clicked_index) {
		var rel = $.now();
		var gall_obj = {};

		$.each(data, function (i, v) {
			gall_obj[v.big] = {
				"img": v.big,
				"title": ag_lb_html_fix(v.title),
				"descr": ag_lb_html_fix(v.description),
				'author': '',
			};
		});
		ag_throw_lb(gall_obj, rel, clicked_index);
	};
})(jQuery);