( function ( wp ) {
	var el = wp.element.createElement;
	var useEffect = wp.element.useEffect;
	var __ = wp.i18n.__;
	var be = wp.blockEditor;
	var c = wp.components;
	var ServerSideRender = wp.serverSideRender;
	var LOCK = 'hac-chart-description';

	function countRows( data ) {
		var lines = ( data || '' ).trim().split( /\r?\n/ ).filter( Boolean );
		return Math.max( lines.length - 1, 0 );
	}

	wp.blocks.registerBlockType( 'hac/chart', {
		edit: function ( props ) {
			var a = props.attributes;
			var set = props.setAttributes;
			var blockProps = be.useBlockProps( { className: 'hac-chart-editor' } );
			var missing = ! ( a.description || '' ).trim();

			// Ask for a description before the post can be published.
			useEffect( function () {
				var ed = wp.data.dispatch( 'core/editor' );
				if ( ! ed || ! ed.lockPostSaving ) { return; }
				var key = LOCK + '-' + props.clientId;
				if ( missing ) { ed.lockPostSaving( key ); } else { ed.unlockPostSaving( key ); }
				return function () { ed.unlockPostSaving( key ); };
			}, [ missing, props.clientId ] );

			return el( 'div', blockProps,
				el( be.InspectorControls, {},
					el( c.PanelBody, { title: __( 'Chart settings' ), initialOpen: true },
						el( c.SelectControl, {
							label: __( 'Chart type' ),
							value: a.chartType,
							options: [
								{ label: __( 'Columns' ), value: 'bar' },
								{ label: __( 'Bars (horizontal)' ), value: 'hbar' },
								{ label: __( 'Stacked to 100%' ), value: 'stacked' }
							],
							onChange: function ( v ) { set( { chartType: v } ); }
						} ),
						el( c.TextControl, {
							label: __( 'Unit after values (optional)' ),
							help: __( 'For example % or " homes"' ),
							value: a.unit,
							onChange: function ( v ) { set( { unit: v } ); }
						} ),
						el( c.ToggleControl, {
							label: __( 'Sort largest first' ),
							checked: !! a.sort,
							onChange: function ( v ) { set( { sort: v } ); }
						} )
					)
				),
				el( 'div', { className: 'hac-chart-editor__fields' },
					el( c.TextControl, {
						label: __( 'Chart title' ),
						value: a.title,
						onChange: function ( v ) { set( { title: v } ); }
					} ),
					el( c.TextareaControl, {
						label: __( 'Data: paste from a spreadsheet' ),
						help: __( 'First row is the column names; first column is the labels. Each extra column becomes a series readers can switch between. ' ) + countRows( a.data ) + __( ' rows.' ),
						rows: 6,
						className: 'hac-chart-editor__data',
						value: a.data,
						onChange: function ( v ) { set( { data: v } ); }
					} ),
					el( c.TextareaControl, {
						label: __( 'Description for screen readers and search (required)' ),
						help: __( 'One or two sentences that say what the chart shows.' ),
						rows: 2,
						value: a.description,
						onChange: function ( v ) { set( { description: v } ); }
					} ),
					missing ? el( c.Notice, { status: 'warning', isDismissible: false }, __( 'Add a description to publish. It becomes the chart’s text alternative.' ) ) : null,
					el( c.TextControl, {
						label: __( 'Source line' ),
						value: a.source,
						onChange: function ( v ) { set( { source: v } ); }
					} )
				),
				el( 'div', { className: 'hac-chart-editor__preview' },
					el( 'p', { className: 'hac-chart-editor__label' }, __( 'Preview' ) ),
					el( ServerSideRender, { block: 'hac/chart', attributes: a } )
				)
			);
		},
		save: function () { return null; }
	} );
} )( window.wp );
