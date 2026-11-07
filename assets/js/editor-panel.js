/**
 * "Anchor" panel in the block settings of every heading block. Shows the
 * anchor the heading gets automatically and lets editors fix it or enter
 * their own. A fixed anchor is stored in the block's "anchor" attribute (the
 * same as Advanced → HTML anchor), i.e. as the heading's id in the content,
 * so it stays when the heading text changes.
 *
 * Plain script without a build step; uses the wp.* globals.
 */
( function ( wp, config ) {
	'use strict';

	const { createElement: el, Fragment, useEffect, useState } = wp.element;
	const { __, sprintf } = wp.i18n;
	const { InspectorControls } = wp.blockEditor;
	const { Button, Notice, PanelBody, TextControl } = wp.components;
	const { createHigherOrderComponent } = wp.compose;
	const { useSelect } = wp.data;

	const PATH = '/rmd-heading-anchors/v1/anchors';
	const DELAY = 400;
	const LEVELS = ( config && Array.isArray( config.levels ) ) ? config.levels : [];

	// Same rule as the core "HTML anchor" field.
	const sanitize = ( value ) => value.replace( /[\s#]/g, '-' );

	// Last answer of the server, so selecting another heading needs no new request.
	let lastKey = '';
	let lastIds = null;

	/**
	 * All heading blocks of the post in document order, as a JSON string so
	 * useSelect only re-renders when something relevant changed.
	 */
	function selectHeadings( select ) {
		const store = select( 'core/block-editor' );
		const headings = store.getClientIdsWithDescendants()
			.filter( ( clientId ) => store.getBlockName( clientId ) === 'core/heading' )
			.map( ( clientId ) => {
				const attributes = store.getBlockAttributes( clientId ) || {};
				return [
					clientId,
					attributes.level || 2,
					String( attributes.content || '' ),
					attributes.anchor || '',
				];
			} );
		return JSON.stringify( headings );
	}

	function useAutomaticIds( key ) {
		const [ state, setState ] = useState( { key: lastKey, ids: lastIds, error: '' } );

		useEffect( () => {
			if ( key === lastKey ) {
				setState( { key, ids: lastIds, error: '' } );
				return undefined;
			}

			let active = true;
			const timer = setTimeout( () => {
				const headings = JSON.parse( key ).map( ( heading ) => ( {
					level: heading[ 1 ],
					content: heading[ 2 ],
					anchor: heading[ 3 ],
				} ) );

				wp.apiFetch( { path: PATH, method: 'POST', data: { headings } } )
					.then( ( response ) => {
						const ids = response && Array.isArray( response.ids ) ? response.ids : [];
						lastKey = key;
						lastIds = ids;
						if ( active ) {
							setState( { key, ids, error: '' } );
						}
					} )
					.catch( ( error ) => {
						if ( active ) {
							setState( { key, ids: null, error: ( error && error.message ) || String( error ) } );
						}
					} );
			}, DELAY );

			return () => {
				active = false;
				clearTimeout( timer );
			};
		}, [ key ] );

		return state.key === key ? state : { key, ids: null, error: '' };
	}

	function AnchorPanel( { clientId, attributes, setAttributes } ) {
		const key = useSelect( selectHeadings, [] );
		const { ids, error } = useAutomaticIds( key );

		const headings = JSON.parse( key );
		const index = headings.findIndex( ( heading ) => heading[ 0 ] === clientId );
		const anchor = attributes.anchor || '';
		const level = attributes.level || 2;
		const automatic = ids && index >= 0 && typeof ids[ index ] === 'string' ? ids[ index ] : '';
		const duplicate = anchor !== '' && headings.some( ( heading ) => heading[ 0 ] !== clientId && heading[ 3 ] === anchor );

		let help;
		if ( anchor !== '' ) {
			help = __( 'The heading keeps this anchor when its text changes.', 'rmd-heading-anchors' );
		} else if ( error ) {
			/* translators: %s: error message */
			help = sprintf( __( 'The automatic anchor could not be determined: %s', 'rmd-heading-anchors' ), error );
		} else if ( ids === null ) {
			help = __( 'Determining the automatic anchor …', 'rmd-heading-anchors' );
		} else if ( automatic !== '' ) {
			/* translators: %s: anchor ID */
			help = sprintf( __( 'Automatic anchor: #%s. It changes when the heading text changes.', 'rmd-heading-anchors' ), automatic );
		} else if ( ! LEVELS.includes( level ) ) {
			/* translators: %d: heading level (1–6) */
			help = sprintf( __( 'H%d headings get no automatic anchor (Settings → Heading anchors).', 'rmd-heading-anchors' ), level );
		} else {
			help = __( 'The heading has no text an automatic anchor could be made of.', 'rmd-heading-anchors' );
		}

		return el(
			PanelBody,
			{ title: __( 'Anchor', 'rmd-heading-anchors' ), initialOpen: true },
			el( TextControl, {
				__nextHasNoMarginBottom: true,
				__next40pxDefaultSize: true,
				label: __( 'Fixed anchor', 'rmd-heading-anchors' ),
				value: anchor,
				placeholder: automatic,
				help,
				autoComplete: 'off',
				onChange: ( value ) => setAttributes( { anchor: sanitize( value ) || undefined } ),
			} ),
			duplicate && el(
				Notice,
				{ status: 'warning', isDismissible: false },
				__( 'Another heading in this post uses the same anchor.', 'rmd-heading-anchors' )
			),
			anchor === '' && automatic !== '' && el(
				Button,
				{
					variant: 'secondary',
					__next40pxDefaultSize: true,
					onClick: () => setAttributes( { anchor: automatic } ),
				},
				__( 'Fix automatic anchor', 'rmd-heading-anchors' )
			),
			anchor !== '' && el(
				Button,
				{
					variant: 'secondary',
					__next40pxDefaultSize: true,
					onClick: () => setAttributes( { anchor: undefined } ),
				},
				__( 'Use automatic anchor', 'rmd-heading-anchors' )
			)
		);
	}

	const withAnchorPanel = createHigherOrderComponent( ( BlockEdit ) => ( props ) => {
		if ( props.name !== 'core/heading' || ! props.isSelected ) {
			return el( BlockEdit, props );
		}
		return el(
			Fragment,
			null,
			el( BlockEdit, props ),
			el( InspectorControls, null, el( AnchorPanel, props ) )
		);
	}, 'withRmdHaAnchorPanel' );

	wp.hooks.addFilter( 'editor.BlockEdit', 'rmd-heading-anchors/anchor-panel', withAnchorPanel );
}( window.wp, window.rmdHaEditor ) );
