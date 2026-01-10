/*
JS that glue stuff together
*/

jQuery(function($) {
  // get the action filter option item on page load
  // filterType is just a string that is equal to the class value.
  var filterType = jQuery('#plugin-categories-list li.active a').attr('class');
  // get and assign the ourHolder element to the
  // holder varible for use later
  var holder = jQuery('#availableplugins');
  // clone all items within the pre-assigned holder element
  var data = holder.clone();

  // This variable contains the list of plugin divs that have the "available-plugin" as a class
  var filteredData = data.find('div.available-plugin');

  // Initialize the plugin filters with default values.
  filter_plugins_cats(wmd_pl_a.show_category);
  filter_plugins_status('all');

  // Executes when the user clicks the category link on the menu
  jQuery('#plugin-categories-list li a').on('click', function(e) {
    e.preventDefault();

    var filterType = jQuery(this).attr('class');

    // Run the filter with the category clicked as a parameter
    filter_plugins_cats(filterType);

    return false;
  });

  // Execute when the user clicks on the category link of the plugin card


  jQuery('#toplevel_page_pretty-plugins li a').on('click', function(e) {
    var filterType = get_url_parameter(jQuery(this).attr('href'));

    if(filterType['page'] == 'pretty-plugins.php') {
    e.preventDefault();

    if(typeof filterType['category'] === "undefined")
      filterType = 'all';
    else
      filterType = filterType['category'];

    filter_plugins_cats(filterType);

    return false;
    }
  });

  // Executes when the user clicks on the status links.
  jQuery('#plugin-status-list li a').on('click', function(e) {
    e.preventDefault();

    var filterType = jQuery(this).attr("class");

    // Run the filter with the status clicked as a parameter
    filter_plugins_status(filterType);

    return false;
  });

  // Executes when the user enters a search term on the search box
  jQuery("#theme-search-input").on('keyup click', function(){
    if(typeof delay_search != 'undefined')
      clearTimeout(delay_search);

    var filterSearch = jQuery(this).val();

    // Run the filter with the search keyword as a parameter
    delay_search = setTimeout(function() {
      search_plugins(filterSearch);
    }, 400)
  });

  // Function that updates the plugin list taking into consideration both the status (i.e. active, inactive) and the current category selected
  function search_plugins(filterSearch) {
    var filteredDataTemp = jQuery(document.createElement('div')).append(filteredData.clone());
    // Loop through the comment list
    filteredDataTemp.find('.available-plugin').each(function(){
      if(jQuery(this).find('h3').text().search(new RegExp(filterSearch, "i")) < 0)
        filteredDataTemp.find("div[data-id='"+jQuery(this).attr('data-id')+"']").remove();
    });

    check_if_plugins_found(filteredDataTemp.find('.available-plugin'));
    // call quicksand and assign transition parameters
    holder.quicksand(
      filteredDataTemp.find('.available-plugin'),
      function() { // callback function
        activate_plugins_links_js();
        check_if_plugins_found(filteredDataTemp.find('.available-plugin'));
      }
    );
  }

  // Function that updates the plugin list by taking into consideration the category selected
  function filter_plugins_cats(filterType) {
    // Removes those items on the menu bar that have classes active and current
    jQuery('#plugin-categories-list li').removeClass('active');
    jQuery('#plugin-categories-list li').removeClass('current');

    // 1.0 jQuery('#toplevel_page_pretty-plugins li').removeClass('active');

    // Get the selected option of the Status filter. By default, this is set to 'all'.
    var currentStatus = jQuery('#plugin-status-list li.active a').attr('class')/*1.0 .split(" ")[0]*/;

    // Catch the unexpected undefined currentStatus
    if (typeof currentStatus === "undefined")
      currentStatus = 'all';

    if (filterType == 'all' || filterType == 'All') {
      if(currentStatus == 'all'){
        // Display all plugins
        filteredData = data.find('div.available-plugin');
      } else {
        // Display only those plugins that are match the currentStatus
        filteredData = data.find('div.available-plugin[data-type~="'+ currentStatus +'"]');
      }
      // Add the current class on the Admin menu item
      jQuery('#toplevel_page_pretty-plugins li a.current').parent().addClass('current');
      jQuery('#plugin-categories-list a.all').parent().addClass('active');
      // jQuery('#plugin-categories-list a.all').addClass('current');

      //jQuery('#plugin-categories-list a.all').addClass('current');
    }
    else {
      if(currentStatus == 'all'){
        // display all the plugins that are available
        filteredData = data.find('div.available-plugin[data-type~="' + filterType + '"]');
      } else{
        // search through the plugins that have assigned the currentStatus
        filteredData = data.find('div.available-plugin[data-type~="' + filterType + '"][data-type~="'+ currentStatus +'"]');
      }
      // Add the current class on the Admin menu item
      jQuery('#toplevel_page_pretty-plugins li a[href$="category\\='+filterType+'"]').parent().addClass('current');

      jQuery('#plugin-categories-list a.'+filterType).parent().addClass('active');
      // jQuery('#plugin-categories-list a.'+filterType).parent().addClass('current');
    }

    display_results(filteredData);
  }

  // Function that updates the plugin results by taking into consideration the status selected (all, active, inactive)
  function filter_plugins_status(filterType) {
    jQuery('#plugin-status-list li').removeClass('active');
    // jQuery('#plugin-status-list li a').removeClass('current');

    // Get the selected category item from the filter bar in the format category1, category2, etc...
    var currentCategory = jQuery('#plugin-categories-list li.active a').attr('class').split(" ")[0];

    // Catch the unexpected undefined currentStatus
    if (typeof currentStatus === "undefined")
      currentStatus = 'all';

    if (filterType == 'all' || filterType == 'All') {
      if(currentCategory == 'all'){
        // display all plugins
        filteredData = data.find('div.available-plugin');
      } else{
        // search through the plugins that have assigned the currentCategory
        filteredData = data.find('div.available-plugin[data-type~="'+ currentCategory +'"]');
      }
      jQuery('#plugin-status-list a.all').parent().addClass('active');
      //jQuery('#plugin-status-list a.all').addClass('current');
    }
    else {
      if(currentCategory == 'all'){
        filteredData = data.find('div.available-plugin[data-type~="' + filterType + '"]');
      } else {
        filteredData = data.find('div.available-plugin[data-type~="' + filterType + '"][data-type~="'+ currentCategory +'"]');
      }
      jQuery('#plugin-status-list a.'+filterType).parent().addClass('active');
      //jQuery('#plugin-status-list a.'+filterType).addClass('current');
    }

    display_results(filteredData);
  }


  function display_results(plugins_found) {
    var search_value = jQuery("#theme-search-input").val();
    if(search_value != '')
      search_plugins(search_value);
    else {
      // call quicksand and assign transition parameters
      check_if_plugins_found(plugins_found);
      holder.quicksand(
        plugins_found,
        function() { // callback function
          activate_plugins_links_js();
          check_if_plugins_found(plugins_found);
        }
      );
    }
  }

  function activate_plugins_links_js() {
    jQuery('a.plugin-details').on('click',function(e) {
        e.preventDefault();
        jQuery(this).parents('.available-plugin-inner').find('.themedetaildiv').toggle();
    });
    //image hover effect
    jQuery('a.activate-deactivate').on({
      mouseenter: function () {
        jQuery(this).closest('.available-plugin-inner').find('a.screenshot').addClass("active");
      },
      mouseleave: function () {
        jQuery(this).closest('.available-plugin-inner').find('a.screenshot').removeClass("active");
      }
    });
  }

  //image hover effect
  function check_if_plugins_found(plugins_found) {
    if(plugins_found.length == 0) {
      jQuery('.no-plugins-found').fadeIn('fast');
    }
    else {
      jQuery('.no-plugins-found').fadeOut('fast');
    }
  }

  //get parameter of url
  function get_url_parameter(url){
    var vars = [], hash;
    var hashes = url.slice(url.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++)
    {
      hash = hashes[i].split('=');
      vars.push(hash[0]);
      vars[hash[0]] = hash[1];
    }
    return vars;
  }

});

/*

Material 1.0

Reorder and filter items with a nice shuffling animation.

Copyright (c) 2010 Jacek Galanciak (razorjack.net) and agilope.com
Big thanks for Piotr Petrus (riddle.pl) for deep code review and wonderful docs & demos.

Dual licensed under the MIT and GPL version 2 licenses.
http://github.com/jquery/jquery/blob/master/MIT-LICENSE.txt
http://github.com/jquery/jquery/blob/master/GPL-LICENSE.txt

Project site: http://razorjack.net/quicksand
Github site: http://github.com/razorjack/quicksand

 */

(function($) {
  $.fn.quicksand = function(collection, customOptions) {
    var options = {
      duration : 600,
      easing : 'swing',
      attribute : 'data-id',        // attribute to recognize same items within source and dest
      adjustHeight : 'false',        // 'dynamic' animates height during shuffling (slow), 'auto' adjusts it
                                    // before or after the animation, false leaves height constant
      adjustWidth : 'false',         // 'dynamic' animates width during shuffling (slow),
                                    // 'auto' adjusts it before or after the animation, false leaves width constant
      useScaling : false,           // enable it if you're using scaling effect
      enhancement : function(c) {}, // Visual enhacement (eg. font replacement) function for cloned elements
      selector : '> *',
      atomic : false,
      dx : 0,
      dy : 0,
      maxWidth : 0,
      retainExisting : false         // disable if you want the collection of items to be replaced completely by incoming items.
    };
    $.extend(options, customOptions);

    // Got IE and want scaling effect? Kiss my ass.
    if (navigator.userAgent.match(/msie/i) || (typeof ($.fn.scale) == 'undefined')) {
      options.useScaling = false;
    }

    var callbackFunction;
    if (typeof (arguments[1]) == 'function') {
      callbackFunction = arguments[1];
    } else if (typeof (arguments[2] == 'function')) {
      callbackFunction = arguments[2];
    }

    return this.each(function(i) {
      var val;
      var animationQueue = []; // used to store all the animation params before starting the animation;
      // solves initial animation slowdowns
      var $collection;
      if (typeof(options.attribute) == 'function') {
        $collection = $(collection);
      } else {
        $collection = $(collection).filter('[' + options.attribute + ']').clone(); // destination (target) collection
      }
      var $sourceParent = $(this); // source, the visible container of source collection
      var sourceHeight = $(this).css('height'); // used to keep height and document flow during the animation
      var sourceWidth = $(this).css('width'); // used to keep  width and document flow during the animation
      var destHeight, destWidth;
      var adjustHeightOnCallback = false;
      var adjustWidthOnCallback = false;
      var offset = $($sourceParent).offset(); // offset of visible container, used in animation calculations
      var offsets = []; // coordinates of every source collection item
      var $source = $(this).find(options.selector); // source collection items
      var width = $($source).innerWidth(); // need for the responsive design

      // Replace the collection and quit if IE6
      if (navigator.userAgent.match(/msie/i) && navigator.userAgent.match(/6/)) {
        $sourceParent.html('').append($collection);
        return;
      }

      // Gets called when any animation is finished
      var postCallbackPerformed = 0; // prevents the function from being called more than one time
      var postCallback = function() {
        $(this).css('margin', '').css('position', '').css('top', '').css('left', '').css('opacity', '');
        if (!postCallbackPerformed) {
          postCallbackPerformed = 1;

          if (!options.atomic) {
            // hack: used to be: $sourceParent.html($dest.html());
            // put target HTML into visible source container
            // but new webkit builds cause flickering when replacing the collections
            var $toDelete = $sourceParent.find(options.selector);
            if (!options.retainExisting) {
              $sourceParent.prepend($dest.find(options.selector));
              $toDelete.remove();
            } else {
              // Avoid replacing elements because we may have already altered items in significant
              // ways and it would be bad to have to do it again. (i.e. lazy load images)
              // But $dest holds the correct ordering. So we must re-sequence items in $sourceParent to match.
              var $keepElements = $([]);
              $dest.find(options.selector).each(function(i) {
                var $matchedElement = $([]);
                if (typeof (options.attribute) == 'function') {
                  var val = options.attribute($(this));
                  $toDelete.each(function() {
                    if (options.attribute(this) == val) {
                      $matchedElement = $(this);
                      return false;
                    }
                  });
                } else {
                  $matchedElement = $toDelete.filter(
                    '[' + options.attribute + '="'+
                    $(this).attr(options.attribute) + '"]');
                }
                if ($matchedElement.length > 0) {
                  // There is a matching element in the $toDelete list and in $dest
                  // list, so make sure it is in the right location within $sourceParent
                  // and put it in the list of elements we need to not delete.
                  $keepElements = $keepElements.add($matchedElement);
                  if (i === 0) {
                    $sourceParent.prepend($matchedElement);
                  } else {
                    $matchedElement.insertAfter($sourceParent.find(options.selector).get(i - 1));
                  }
                }
              });
              // Remove whatever is remaining from the DOM
              $toDelete.not($keepElements).remove();
            }

            if (adjustHeightOnCallback) {
              $sourceParent.css('height', destHeight);
            }
            if (adjustWidthOnCallback) {
              $sourceParent.css('width', sourceWidth);
            }
          }
          options.enhancement($sourceParent); // Perform custom visual enhancements on a newly replaced collection
          if (typeof callbackFunction == 'function') {
            callbackFunction.call(this);
          }
        }

        if (false === options.adjustHeight) {
          $sourceParent.css('height', 'auto');
        }

        if (false === options.adjustWidth) {
          $sourceParent.css('width', 'auto');
        }
      };

      // Position: relative situations
      var $correctionParent = $sourceParent.offsetParent();
      var correctionOffset = $correctionParent.offset();
      if ($correctionParent.css('position') == 'relative') {
        if ($correctionParent.get(0).nodeName.toLowerCase() != 'body') {
          correctionOffset.top += (parseFloat($correctionParent.css('border-top-width')) || 0);
          correctionOffset.left += (parseFloat($correctionParent.css('border-left-width')) || 0);
        }
      } else {
        correctionOffset.top -= (parseFloat($correctionParent.css('border-top-width')) || 0);
        correctionOffset.left -= (parseFloat($correctionParent.css('border-left-width')) || 0);
        correctionOffset.top -= (parseFloat($correctionParent.css('margin-top')) || 0);
        correctionOffset.left -= (parseFloat($correctionParent.css('margin-left')) || 0);
      }

      // perform custom corrections from options (use when Quicksand fails to detect proper correction)
      if (isNaN(correctionOffset.left)) {
        correctionOffset.left = 0;
      }
      if (isNaN(correctionOffset.top)) {
        correctionOffset.top = 0;
      }

      correctionOffset.left -= options.dx;
      correctionOffset.top -= options.dy;

      // keeps nodes after source container, holding their position
      $sourceParent.css('height', $(this).height());
      $sourceParent.css('width', $(this).width());

      // get positions of source collections
      $source.each(function(i) {
        offsets[i] = $(this).offset();
      });

      // stops previous animations on source container
      $(this).stop();
      var dx = 0;
      var dy = 0;
      $source.each(function(i) {
        $(this).stop(); // stop animation of collection items
        var rawObj = $(this).get(0);
        if (rawObj.style.position == 'absolute') {
          dx = -options.dx;
          dy = -options.dy;
        } else {
          dx = options.dx;
          dy = options.dy;
        }

        rawObj.style.position = 'absolute';
        rawObj.style.margin = '0';

        if (!options.adjustWidth) {
          rawObj.style.width = (width + 'px'); // sets the width to the current element
          // with even if it has been changed
          // by a responsive design
        }

        rawObj.style.top = (offsets[i].top- parseFloat(rawObj.style.marginTop) - correctionOffset.top + dy) + 'px';
        rawObj.style.left = (offsets[i].left- parseFloat(rawObj.style.marginLeft) - correctionOffset.left + dx) + 'px';

        if (options.maxWidth > 0 && offsets[i].left > options.maxWidth) {
          rawObj.style.display = 'none';
        }
      });

      // create temporary container with destination collection
      var $dest = $($sourceParent).clone();
      var rawDest = $dest.get(0);
      rawDest.innerHTML = '';
      rawDest.setAttribute('id', '');
      rawDest.style.height = 'auto';
      rawDest.style.width = $sourceParent.width() + 'px';
      $dest.append($collection);
      // Inserts node into HTML. Note that the node is under visible source container in the exactly same position
      // The browser render all the items without showing them (opacity: 0.0) No offset calculations are needed,
      // the browser just extracts position from underlayered destination items and sets animation to destination positions.
      $dest.insertBefore($sourceParent);
      $dest.css('opacity', 0.0);
      rawDest.style.zIndex = -1;

      rawDest.style.margin = '0';
      rawDest.style.position = 'absolute';
      rawDest.style.top = offset.top - correctionOffset.top + 'px';
      rawDest.style.left = offset.left - correctionOffset.left + 'px';

      if (options.adjustHeight === 'dynamic') {
        // Animate sourceParent height to match dest height
        $sourceParent.animate({ height: $dest.height() }, {
          duration: options.duration,
          easing: options.easing,
          complete: postCallback
        });
      } else if (options.adjustHeight === 'auto') {
        // Adjust sourceParent height based on conditions
        destHeight = $dest.height();
        if (parseFloat(sourceHeight) < parseFloat(destHeight)) {
          // Set height immediately if sourceHeight is less than destHeight
          $sourceParent.css('height', destHeight);
        } else {
          // Set flag to adjust height later in callback if needed
          adjustHeightOnCallback = true;
        }
      }            

      if (options.adjustWidth === 'dynamic') {
        // Animate sourceParent width to match dest width
        $sourceParent.animate({ width: $dest.width() }, {
          duration: options.duration,
          easing: options.easing,
          complete: postCallback
        });
      } else if (options.adjustWidth === 'auto') {
        // Adjust sourceParent width based on conditions
        destWidth = $dest.width();
        if (parseFloat(sourceWidth) < parseFloat(destWidth)) {
          // Set width immediately if sourceWidth is less than destWidth
          $sourceParent.css('width', destWidth);
        } else {
          // Set flag to adjust width later in callback if needed
          adjustWidthOnCallback = true;
        }
      }            

      // Now it's time to do shuffling animation. First of all, we need to identify same elements within
      // source and destination collections
      $source.each(function(i) {
        var destElement = [];
        if (typeof (options.attribute) == 'function') {
          val = options.attribute($(this));
          $collection.each(function() {
            if (options.attribute(this) == val) {
              destElement = $(this);
              return false;
            }
          });
        } else {
          destElement = $collection.filter('[' + options.attribute + '="' + $(this).attr(options.attribute) + '"]');
        }
        if (destElement.length) {
          // The item is both in source and destination collections. It it's under different position, let's move it
          if (!options.useScaling) {
            animationQueue.push({
              element : $(this), dest : destElement,
              style : {
                top : $(this).offset().top,
                left : $(this).offset().left,
                opacity : ""
              },
              animation : {
                top : destElement.offset().top - correctionOffset.top,
                left : destElement.offset().left - correctionOffset.left,
                opacity : 1.0
              }
            });
          } else {
            animationQueue.push({
              element : $(this), dest : destElement,
              style : {
                top : $(this).offset().top,
                left : $(this).offset().left,
                opacity : ""
              },
              animation : {
                top : destElement.offset().top - correctionOffset.top,
                left : destElement.offset().left - correctionOffset.left,
                opacity : 1.0,
                scale : '1.0'
              }
            });
          }
        } else {
          // The item from source collection is not present in destination collections.  Let's remove it
          if (!options.useScaling) {
            animationQueue.push({
              element : $(this),
              style : {
                top : $(this).offset().top,
                left : $(this).offset().left,
                opacity : ""
              },
              animation : {
                opacity : '0.0'
              }
            });
          } else {
            animationQueue.push({
              element : $(this),
              animation : {
                opacity : '0.0',
                style : {
                  top : $(this).offset().top,
                  left : $(this).offset().left,
                  opacity : ""
                },
                scale : '0.0'
              }
            });
          }
        }
      });

      $collection.each(function(i) {
        // Grab all items from target collection not present in visible source collection
        var sourceElement = [];
        var destElement = [];
        if (typeof (options.attribute) == 'function') {
          val = options.attribute($(this));
          $source.each(function() {
            if (options.attribute(this) == val) {
              sourceElement = $(this);
              return false;
            }
          });

          $collection.each(function() {
            if (options.attribute(this) == val) {
              destElement = $(this);
              return false;
            }
          });
        } else {
          sourceElement = $source.filter('[' + options.attribute + '="' + $(this).attr(options.attribute) + '"]');
          destElement = $collection.filter('[' + options.attribute + '="' + $(this).attr(options.attribute) + '"]');
        }

        var animationOptions;
        if (sourceElement.length === 0 && destElement.length > 0) {

          // No such element in source collection...
          if (!options.useScaling) {
            animationOptions = {opacity : '1.0'};
          } else {
            animationOptions = {opacity : '1.0', scale : '1.0'};
          }

          // Let's create it
          var d = destElement.clone();
          var rawDestElement = d.get(0);
          rawDestElement.style.position = 'absolute';
          rawDestElement.style.margin = '0';

          if (!options.adjustWidth) {
            // sets the width to the current element with even if it has been changed by a responsive design
            rawDestElement.style.width = width + 'px';
          }

          rawDestElement.style.top = destElement.offset().top - correctionOffset.top + 'px';
          rawDestElement.style.left = destElement.offset().left - correctionOffset.left + 'px';

          d.css('opacity', 0.0); // IE

          if (options.useScaling) {
            d.css('transform', 'scale(0.0)');
          }
          d.appendTo($sourceParent);

          if (options.maxWidth === 0 || destElement.offset().left < options.maxWidth) {
            animationQueue.push({element : $(d), dest : destElement,animation : animationOptions});
          }
        }
      });

      $dest.remove();
      if (!options.atomic) {
        options.enhancement($sourceParent); // Perform custom visual enhancements during the animation
        for (i = 0; i < animationQueue.length; i++) {
          var animationOptions = {
            duration: options.duration,
            easing: options.easing,
            complete: postCallback
          };
          animationQueue[i].element.animate(animationQueue[i].animation, animationOptions);
        }
      } else {
        $toDelete = $sourceParent.find(options.selector);
        $sourceParent.prepend($dest.find(options.selector));
        for (i = 0; i < animationQueue.length; i++) {
          if (animationQueue[i].dest && animationQueue[i].style) {
            var destElement = animationQueue[i].dest;
            var destOffset = destElement.offset();
      
            destElement.css({
              position: 'relative',
              top: (animationQueue[i].style.top - destOffset.top),
              left: (animationQueue[i].style.left - destOffset.left)
            });
      
            var animationOptions = {
              top: "0",
              left: "0",
              duration: options.duration,
              easing: options.easing,
              complete: postCallback
            };
      
            destElement.animate(animationOptions);
          } else {
            var animationOptions = {
              duration: options.duration,
              easing: options.easing,
              complete: postCallback
            };
      
            animationQueue[i].element.animate(animationQueue[i].animation, animationOptions);
          }
        }
        $toDelete.remove();
      }            
    });
  };
})(jQuery);

/*
 * jQuery Easing v1.4.1 - http://gsgd.co.uk/sandbox/jquery/easing/
 * Open source under the BSD License.
 * Copyright © 2008 George McGinley Smith
 * All rights reserved.
 * https://raw.github.com/gdsmith/jquery.easing/master/LICENSE
*/

/* globals jQuery, define, module, require */
(function (factory) {
	if (typeof define === "function" && define.amd) {
		define(['jquery'], function ($) {
			return factory($);
		});
	} else if (typeof module === "object" && typeof module.exports === "object") {
		module.exports = factory(require('jquery'));
	} else {
		factory(jQuery);
	}
})(function($){

	// Preserve the original jQuery "swing" easing as "jswing"
	if (typeof $.easing !== 'undefined') {
		$.easing['jswing'] = $.easing['swing'];
	}

	var pow = Math.pow,
		sqrt = Math.sqrt,
		sin = Math.sin,
		cos = Math.cos,
		PI = Math.PI,
		c1 = 1.70158,
		c2 = c1 * 1.525,
		c3 = c1 + 1,
		c4 = ( 2 * PI ) / 3,
		c5 = ( 2 * PI ) / 4.5;

	// x is the fraction of animation progress, in the range 0..1
	function bounceOut(x) {
		var n1 = 7.5625,
			d1 = 2.75;
		if ( x < 1/d1 ) {
			return n1*x*x;
		} else if ( x < 2/d1 ) {
			return n1*(x-=(1.5/d1))*x + .75;
		} else if ( x < 2.5/d1 ) {
			return n1*(x-=(2.25/d1))*x + .9375;
		} else {
			return n1*(x-=(2.625/d1))*x + .984375;
		}
	}

	$.extend( $.easing, {
		def: 'easeOutQuad',
		swing: function (x) {
			return $.easing[$.easing.def](x);
		},
		easeInQuad: function (x) {
			return x * x;
		},
		easeOutQuad: function (x) {
			return 1 - ( 1 - x ) * ( 1 - x );
		},
		easeInOutQuad: function (x) {
			return x < 0.5 ?
				2 * x * x :
				1 - pow( -2 * x + 2, 2 ) / 2;
		},
		easeInCubic: function (x) {
			return x * x * x;
		},
		easeOutCubic: function (x) {
			return 1 - pow( 1 - x, 3 );
		},
		easeInOutCubic: function (x) {
			return x < 0.5 ?
				4 * x * x * x :
				1 - pow( -2 * x + 2, 3 ) / 2;
		},
		easeInQuart: function (x) {
			return x * x * x * x;
		},
		easeOutQuart: function (x) {
			return 1 - pow( 1 - x, 4 );
		},
		easeInOutQuart: function (x) {
			return x < 0.5 ?
				8 * x * x * x * x :
				1 - pow( -2 * x + 2, 4 ) / 2;
		},
		easeInQuint: function (x) {
			return x * x * x * x * x;
		},
		easeOutQuint: function (x) {
			return 1 - pow( 1 - x, 5 );
		},
		easeInOutQuint: function (x) {
			return x < 0.5 ?
				16 * x * x * x * x * x :
				1 - pow( -2 * x + 2, 5 ) / 2;
		},
		easeInSine: function (x) {
			return 1 - cos( x * PI/2 );
		},
		easeOutSine: function (x) {
			return sin( x * PI/2 );
		},
		easeInOutSine: function (x) {
			return -( cos( PI * x ) - 1 ) / 2;
		},
		easeInExpo: function (x) {
			return x === 0 ? 0 : pow( 2, 10 * x - 10 );
		},
		easeOutExpo: function (x) {
			return x === 1 ? 1 : 1 - pow( 2, -10 * x );
		},
		easeInOutExpo: function (x) {
			return x === 0 ? 0 : x === 1 ? 1 : x < 0.5 ?
				pow( 2, 20 * x - 10 ) / 2 :
				( 2 - pow( 2, -20 * x + 10 ) ) / 2;
		},
		easeInCirc: function (x) {
			return 1 - sqrt( 1 - pow( x, 2 ) );
		},
		easeOutCirc: function (x) {
			return sqrt( 1 - pow( x - 1, 2 ) );
		},
		easeInOutCirc: function (x) {
			return x < 0.5 ?
				( 1 - sqrt( 1 - pow( 2 * x, 2 ) ) ) / 2 :
				( sqrt( 1 - pow( -2 * x + 2, 2 ) ) + 1 ) / 2;
		},
		easeInElastic: function (x) {
			return x === 0 ? 0 : x === 1 ? 1 :
				-pow( 2, 10 * x - 10 ) * sin( ( x * 10 - 10.75 ) * c4 );
		},
		easeOutElastic: function (x) {
			return x === 0 ? 0 : x === 1 ? 1 :
				pow( 2, -10 * x ) * sin( ( x * 10 - 0.75 ) * c4 ) + 1;
		},
		easeInOutElastic: function (x) {
			return x === 0 ? 0 : x === 1 ? 1 : x < 0.5 ?
				-( pow( 2, 20 * x - 10 ) * sin( ( 20 * x - 11.125 ) * c5 )) / 2 :
				pow( 2, -20 * x + 10 ) * sin( ( 20 * x - 11.125 ) * c5 ) / 2 + 1;
		},
		easeInBack: function (x) {
			return c3 * x * x * x - c1 * x * x;
		},
		easeOutBack: function (x) {
			return 1 + c3 * pow( x - 1, 3 ) + c1 * pow( x - 1, 2 );
		},
		easeInOutBack: function (x) {
			return x < 0.5 ?
				( pow( 2 * x, 2 ) * ( ( c2 + 1 ) * 2 * x - c2 ) ) / 2 :
				( pow( 2 * x - 2, 2 ) *( ( c2 + 1 ) * ( x * 2 - 2 ) + c2 ) + 2 ) / 2;
		},
		easeInBounce: function (x) {
			return 1 - bounceOut( 1 - x );
		},
		easeOutBounce: bounceOut,
		easeInOutBounce: function (x) {
			return x < 0.5 ?
				( 1 - bounceOut( 1 - 2 * x ) ) / 2 :
				( 1 + bounceOut( 2 * x - 1 ) ) / 2;
		}
	});
	return $;
});


