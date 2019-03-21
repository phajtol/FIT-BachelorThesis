
/* Bootstrap multiple modals hack
 based on @link(http://miles-by-motorcycle.com/static/bootstrap-modal/index.html#)
 more details on @link http://miles-by-motorcycle.com/fv-b-8-670/stacking-bootstrap-dialogs-using-event-callbacks
 + custom changes
 */

$(document).on('hidden.bs.modal', '.modal', function( event ) {
	$(this).removeClass( 'fv-modal-stack' );
	$('body').data( 'fv_open_modals', $('body').data( 'fv_open_modals' ) - 1 );

	// scrolling hack
	if($('body').data( 'fv_open_modals' ) > 0) {
		$('body').addClass('modal-open');
	} else $('body').removeClass('modal-open');
});

$( document ).on( 'shown.bs.modal', '.modal', function ( event ) {
	// keep track of the number of open modals
	if ( typeof( $('body').data( 'fv_open_modals' ) ) == 'undefined' ) { $('body').data( 'fv_open_modals', 0 ); }
	if ( $(this).hasClass( 'fv-modal-stack' ) ) { return; }

	$(this).addClass( 'fv-modal-stack' );

	$('body').data( 'fv_open_modals', $('body').data( 'fv_open_modals' ) + 1 );

	$(this).css('z-index', 1040 + (10 * $('body').data( 'fv_open_modals' )));

	$( '.modal-backdrop' ).not( '.fv-modal-stack' )
		.css( 'z-index', 1039 + (10 * $('body').data( 'fv_open_modals' )));

	$( '.modal-backdrop' ).not( 'fv-modal-stack' )
		.addClass( 'fv-modal-stack' );

});


// modal-jqxTree redraw hack (custom code)

$(document).on('show.bs.modal', '.modal', function(){
	$(this).find('.jqx-tree').each(function() {
		var jqxTree = $(this);
		setTimeout(function(){
			jqxTree.jqxTree('refresh');
		}, 250);
	});
});

// substring matcher - http://twitter.github.io/typeahead.js/examples/
// edited to use string.normalize().replace() to compare strings without diacritics
function substringMatcher (strs) {
	return function findMatches (q, cb) {
		var matches, substrRegex;

		// an array that will be populated with substring matches
		matches = [];

		// regex used to determine if a string contains the substring `q`
		substrRegex = new RegExp(q.normalize('NFD').replace(/[\u0300-\u036f]/g, ''), 'i');

		// iterate through the pool of strings and for any string that
		// contains the substring `q`, add it to the `matches` array
		$.each(strs, function (i, str) {
			if (substrRegex.test(str.normalize('NFD').replace(/[\u0300-\u036f]/g, ""))) {
				matches.push(str);
			}
		});

		cb(matches);
	};
}