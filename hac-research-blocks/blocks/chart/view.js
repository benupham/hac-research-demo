document.addEventListener( 'click', function ( e ) {
	var btn = e.target.closest( '.hac-chart__series button' );
	if ( ! btn ) { return; }
	var fig = btn.closest( '.hac-chart' );
	var i = btn.getAttribute( 'data-series' );
	fig.querySelectorAll( '.hac-chart__series button' ).forEach( function ( b ) {
		b.setAttribute( 'aria-pressed', b === btn ? 'true' : 'false' );
	} );
	fig.querySelectorAll( '.hac-chart__plot' ).forEach( function ( p ) {
		p.hidden = p.getAttribute( 'data-series' ) !== i;
	} );
} );
