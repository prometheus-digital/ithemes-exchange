/**
 * jQuery Nested From Toggles Plugin by iThemes.com
*/
;(function ( $, window, document, undefined ) { 
    // Create the defaults once
    var pluginName = 'iThemesNestedFormToggles';

    // The plugin constructor
    function Plugin( element, options ) {

        // Set element
        this.element = element;

		// Default options
		var defaults = {
			'container'    : $(element)
		}

		// Merge defaults
		this.options = $.extend(true, defaults, options);	

        this._name = pluginName;

		// Register init whenver a selector changes and trigger the change on page load
		$('.' + this.options.selectorsClass).change($.proxy(this.init, this)).triggerHandler('change');
    }

	/**
	 * Used to trigger the first time on page load
	*/
	Plugin.prototype.init = function (globalThis) {
		this.toggle(this.options);
	}

	/**
	 * Main functionality
	*/
	Plugin.prototype.toggle = function (options) {

		// jQuery object containing container element
		var $container = options.container

		// jQuery object containing all elements with group class and inside container
		var $groups = $('.' + options.groupsClass, $container);

		// jQuery object containing all elements with selectors class and inside container
		var $selectors = $('.' + options.selectorsClass, $container);

		// jQuery object containing all group elemens that should be shown based on parent selectors
		var selectedGroups = this.getSelectedGroups($selectors);

		// Hide all groups
		$groups.hide();

		// If selectedGroups is an array, convert to jQuery selector and filter them from all groups then show them.
		if( Object.prototype.toString.call( selectedGroups ) === '[object Array]' ) {
			selectedGroupValuesString = selectedGroups.join(', .');
			if ($('.'+selectedGroupValuesString).length > 0) {
				var $selectedGroups = $groups.filter( '.'+selectedGroupValuesString );
				$selectedGroups.show();
			}
		}
	}

	Plugin.prototype.getSelectedGroups = function($selectors) {
		var selectedGroups = [];
		if( $selectors.length > 0 ) {
			for( i=0; i < $selectors.length; i++ ) {
				$selector = $($selectors[i]);
				var selectorType = $selector.prop('type').toLowerCase();
				if ('checkbox' == selectorType || 'radio' == selectorType){
					if( $selector.is(":checked") ) {
						$('input[name="'+$selector.attr('name')+'"]:checked').each(function(e){
							var classes = $(this).data('dependant-classes');
							if (undefined != classes) {
								classes = classes.split(' ');
								$.each(classes, function(i, className) {
										selectedGroups.push(className);
								});
							}
						});
					}
				} else if( 'select-one' == selectorType || 'select-multiple' == selectorType) {
					$.map($selector.find(':selected'), function(e) {
						var classes = $(e).data('dependant-classes');
						if ( undefined != classes ) {
							classes = classes.split(' ');
							$.each(classes, function(i, className) {
								selectedGroups.push(className);
							});
						}
					});
				} else if ( 'hidden' == selectorType ) {
					var classes = $selector.data('dependant-classes');
					if (undefined != classes) {
						classes = classes.split(' ');
						$.each(classes, function(i, className) {
								selectedGroups.push(className);
						});
					}
				}
			}
			if ( selectedGroups.length > 0 && "" != selectedGroups[0]) {
				return selectedGroups;
			}
		}
		return false;
	}

    // A really lightweight plugin wrapper around the constructor, 
    // preventing against multiple instantiations
    $.fn[pluginName] = function (options) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName,
                new Plugin( this, options ));
            }
        });
    }
})( jQuery, window, document );
