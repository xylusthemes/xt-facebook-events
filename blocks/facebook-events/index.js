const { __ } = wp.i18n; // Import __() from wp.i18n
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks
const {
	PanelBody,
	TextControl,
	RangeControl,
	ToggleControl,
	ServerSideRender
} = wp.components;
const { InspectorControls } = wp.editor;
const { createElement } = wp.element;

/**
 * Register: Facebook Events Gutenberg Block.
 */
registerBlockType( 'xtfe-block/facebook-events', {
	// Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
	title: __( 'Facebook Events' ),
	description: __( 'Block for Display Facebook Events directly in your website.' ),
	icon: {
		foreground: '#333333',
		src: <svg viewBox="0 0 24 24"><path d="M20 3H4c-.6 0-1 .4-1 1v16c0 .5.4 1 1 1h8.6v-7h-2.3v-2.7h2.3v-2c0-2.3 1.4-3.6 3.5-3.6 1 0 1.8.1 2.1.1v2.4h-1.4c-1.1 0-1.3.5-1.3 1.3v1.7h2.7l-.4 2.8h-2.3v7H20c.5 0 1-.4 1-1V4c0-.6-.4-1-1-1z" /></svg>,
	},
	category: 'widgets',
	keywords: [
		__( 'Events' ),
		__( 'Facebook' ),
		__( 'facebook events' ),
	],

	// Enable or disable support for features
	supports: {
		html: false,
	},

	// Set for each piece of dynamic data used in your block
	attributes: {
		col: {
			type: 'number',
			default: 3,
		},
		max_events: {
			type: 'number',
			default: 12,
		},
		page_id: {
			type: 'string',
		},
		new_window: {
			type: 'string',
		}
	},

	// Determines what is displayed in the editor
	edit: function( props ) {
		const { attributes, isSelected, setAttributes } = props;

		return [
			isSelected && (
				<InspectorControls key="inspector">
					<PanelBody title={ __( 'Facebook Events Setting' ) }>
						<TextControl
							label={ __( 'Facebook Page ID' ) }
							value={ attributes.page_id || '' }
							onChange={ ( value ) => setAttributes( { page_id: value } ) }
						/>
						<RangeControl
							label={ __( 'Columns' ) }
							value={ attributes.col || 3 }
							onChange={ ( value ) => setAttributes( { col: value } ) }
							min={ 1 }
							max={ 4 }
						/>
						<RangeControl
							label={ __( 'Max. Events' ) }
							value={ attributes.max_events || 12 }
							onChange={ ( value ) => setAttributes( { max_events: value } ) }
							min={ 1 }
							max={ 100 }
						/>
						<ToggleControl
							label={ __( 'Open Events in new window' ) }
							checked={ attributes.new_window }
							onChange={ value => {
								return setAttributes( { new_window: value ? '1' : '0' } );
							}
							}
						/>
					</PanelBody>
				</InspectorControls>
			),

			createElement( ServerSideRender, {
				block: 'xtfe-block/facebook-events',
				attributes: attributes,
			} ),
		];
	},

	save: function() {
		// Rendering in PHP.
		return null;
	},
} );
