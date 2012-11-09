var Generic = {};

Generic.defaultMenuSeparators = function($) {
	// Because IE sucks, we're removing the last stray separator
	// on default navigation menus for browsers that don't 
	// support the :last-child CSS property
	$('.menu.horizontal li:last-child').addClass('last');
};

Generic.removeExtraGformStyles = function($) {
	// Since we're re-registering the Gravity Form stylesheet
	// manually and we can't dequeue the stylesheet GF adds
	// by default, we're removing the reference to the script if
	// it exists on the page (if CSS hasn't been turned off in GF settings.)
	$('link#gforms_css-css').remove();
}

Generic.mobileNavBar = function($) {
	// Switch the navigation bar from standard horizontal nav to bootstrap mobile nav
	// when the browser is at mobile size:
	var mobile_wrap = function() {
		$('#header-menu').wrap('<div class="navbar"><div class="navbar-inner"><div class="container" id="mobile_dropdown_container"><div class="nav-collapse"></div></div></div></div>');
		$('<a class="btn btn-navbar" id="mobile_dropdown_toggle" data-target=".nav-collapse" data-toggle="collapse"><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></a><a class="brand" href="#">Navigation</a>').prependTo('#mobile_dropdown_container');
		$('.current-menu-item, .current_page_item').addClass('active');
	}
	var mobile_unwrap = function() {
		$('#mobile_dropdown_toggle .icon-bar').remove();
		$('#mobile_dropdown_toggle').remove();
		$('#mobile_dropdown_container a.brand').remove();
		$('#header-menu').unwrap();
		$('#header-menu').unwrap();
		$('#header-menu').unwrap();
		$('#header-menu').unwrap();
	}
	var adjust_mobile_nav = function() {
		if ($(window).width() < 480) {
			if ($('#mobile_dropdown_container').length < 1) {
				mobile_wrap();
			}
		}
		else {
			if ($('#mobile_dropdown_container').length > 0) {
				mobile_unwrap();
			}
		}
	}
	
	if ( !($.browser.msie && $.browser.version < 9) ) { /* Don't resize in IE8 or older */
		adjust_mobile_nav();
		$(window).resize(function() {
			adjust_mobile_nav();
		});
	}
}


removeLastCommas = function($) {
	$('#header-taglist .comma:last-child').hide();
	$('#header-servicelist .comma:last-child').hide();
}


if (typeof jQuery != 'undefined'){
	jQuery(document).ready(function($) {
		//Webcom.slideshow($);
		Webcom.analytics($);
		Webcom.handleExternalLinks($);
		Webcom.loadMoreSearchResults($);
		
		/* Theme Specific Code Here */
		Generic.defaultMenuSeparators($);
		Generic.removeExtraGformStyles($);
		Generic.mobileNavBar($);
		removeLastCommas($);
		archiveLazyLoadSetup($);
	});
}else{console.log('jQuery dependancy failed to load');}