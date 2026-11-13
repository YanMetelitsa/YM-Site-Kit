'use strict';

jQuery( document ).ready( function ( $ ) {
	// Init dialogs.
	$( '.ymsk-logger-dialog' ).each( function () {
		$( this ).dialog({
			autoOpen : false,
			modal    : true,
			width    : Math.min( $( window ).width()  * .8, 800 ),
			height   : $( window ).height() * .8,
		});
	});

	// Init link-buttons.
	$( '.ymsk-logger-dialog-link' ).each( function () {
		let fileName = $( this ).data( 'file' );

		// Set CodeMirror settings.
		let settings = wp.codeEditor.defaultSettings ? _.clone( wp.codeEditor.defaultSettings ) : {};

		settings.codemirror = _.extend( {}, settings.codemirror, {
			mode       : 'text/plain',
			indentUnit : 4,
			tabSize    : 4,
			lint       : false,
			gutters    : [],
		});

		// Init CodeMirror.
		let dialogCodeMirror = wp.codeEditor.initialize( `ymsk-logger-dialog-editor-${fileName}`, settings );

		// Set click trigger.
		$( this ).on( 'click', function ( e ) {
			e.preventDefault();

			if ( dialogCodeMirror.codemirror.getValue().trim() ) {
				$( `.ymsk-logger-dialog-${fileName}` ).dialog( 'open' );

				dialogCodeMirror.codemirror.refresh();
			} else {
				let linkLabel = $( this ).text();

				$( this ).text( $( this ).data( 'loading' ) );
				
				let formData = new FormData();
				formData.append( 'file', fileName );
				
				fetch( `${YMSK_Logger.restURL}/get_content`, {
					method: 'POST',
					headers: {
						'X-WP-Nonce': YMSK_Logger.restNonce,
					},
					body: formData,
				})
					.then( response => response.json() )
					.then( result => {
						if ( result.success ) {
							dialogCodeMirror.codemirror.setValue( result.data );
							
							$( `.ymsk-logger-dialog-${fileName}` ).dialog( 'open' );
	
							dialogCodeMirror.codemirror.refresh();
						} else {
							alert( result.message ?? 'Error' );
						}
					})
					.finally( () => {
						$( this ).text( linkLabel );
					});
			}
		});
	});
});