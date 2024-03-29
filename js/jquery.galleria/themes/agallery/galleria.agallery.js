/*global jQuery, Galleria */

/**
 * Galleria LCweb Theme for Avator Gallery - 2013-04-17
 * update: 2017-08-19
 * (c) Montanari Luca aka LCweb
 */
 
(function($) {

/*global jQuery, Galleria */

Galleria.addTheme({
    name: 'agallery',
    author: 'Montanari Luca',
	version: '1.5.7',
    defaults: {
		//initialTransition: 'fade', 
        transition: ag_galleria_fx,
    	transitionSpeed: ag_galleria_fx_time,
		imageCrop:	ag_galleria_img_crop,
        thumbCrop:  true,
		queue:		false,
		showCounter:false,
		pauseOnInteraction: true,
		
        // set this to false if you want to show the caption by default
        _toggleInfo: ag_galleria_toggle_info
    },
    init: function(options) {

        Galleria.requires(1.5, 'LCweb theme requires Galleria 1.5 or later');

        // add some elements
        this.addElement('ag-play','ag-toggle-thumb', 'ag-lightbox','ag-info-link');
        this.append({
            'info' : ['ag-play','ag-toggle-thumb', 'ag-lightbox', 'ag-info-link', 'info-text']
        });

        // cache some stuff
        var slider_obj = this,
			info_btn = this.$('ag-info-link'),
			info = this.$('info-text'),
			play_btn = this.$('ag-play'),
			lightbox_btn = this.$('ag-lightbox'),
            touch = Galleria.TOUCH,
            click = touch ? 'touchstart' : 'click';

        // some stuff for non-touch browsers
        if (! touch ) {
            this.addIdleState( this.get('image-nav-left'), { left:-50 });
            this.addIdleState( this.get('image-nav-right'), { right:-50 });
        }

        // toggle info
		info_btn.bind( click, function() {
			info.stop().fadeToggle(150);
		});
		
		// launch lightbox
		setTimeout(function() { // delay to avoid strange autoclick
			lightbox_btn.bind("click tap", function(e) {
				
				if(typeof(ag_active_index) != 'undefined') {
					ag_slider_lightbox(slider_obj._data, ag_active_index);
				} 
				else {
					ag_slider_lightbox(slider_obj._data, 0);	
				}
			});
		}, 50);	
		

        // bind some stuff
        this.bind('thumbnail', function(e) {

            if (! touch ) {
                // fade thumbnails
                $(e.thumbTarget).css('opacity', 0.6).parent().hover(function() {
                    $(this).not('.active').children().stop().fadeTo(100, 1);
                }, function() {
                    $(this).not('.active').children().stop().fadeTo(400, 0.6);
                });

                if ( e.index === this.getIndex() ) {
                    $(e.thumbTarget).css('opacity',1);
                }
            } else {
                $(e.thumbTarget).css('opacity', this.getIndex() ? 1 : 0.6);
            }
        });

        this.bind('loadstart', function(e) {
            if (!e.cached) {
                this.$('loader').show().fadeTo(200, 1);
            }
			
			if(this.hasInfo()) {
				this.$('info').removeClass('has_no_data');
			} else {
				this.$('info').addClass('has_no_data');
			}	
			
            $(e.thumbTarget).css('opacity',1).parent().siblings().children().css('opacity', 0.6);
        });

        this.bind('loadfinish', function(e) {
			this.$('loader').fadeOut(200);
			
			// security check for the play-pause button
			if(!this._playing && play_btn.hasClass('galleria-ag-pause')) {
				play_btn.removeClass('galleria-ag-pause');
			}
			
			// avoid double titles due to empty descriptions
			if( $.trim( this.$('info').find('.galleria-info-description').html()) == '&nbsp;' ) {
				this.$('info').find('.galleria-info-description').css('height', 0).css('margin', 0);	
			} else {
				this.$('info').find('.galleria-info-description').removeAttr('style');
			}
			
			if ( options._toggleInfo === false && info.is(':hidden') && !info.hasClass('already_shown') ) {
				info.fadeIn(300).addClass('already_shown');
			}
        });
		
		
    }
});

}(jQuery));