const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const {
	PanelBody,
	RangeControl,
	ToggleControl,
	SelectControl,
	TextControl,
} = wp.components;
var InspectorControls = wp.blockEditor.InspectorControls;

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
			type: 'boolean',
     		default: false
		},
		layout: {
			type: 'string',
			default: '',
		},
	},
    edit: ( { attributes, setAttributes } ) => {
        const { col, max_events, page_id, new_window, layout,  } = attributes;
		const { serverSideRender: ServerSideRender } = wp;

        return (
            <div>
                <InspectorControls key="inspector">
					<PanelBody title={ __( 'Facebook Events Setting' ) }>
						<TextControl
							label={ __( 'Facebook Page ID' ) }
							value={ page_id || '' }
							onChange={ ( value ) => setAttributes( { page_id: value } ) }
						/>
						<RangeControl
							label={ __( 'Columns' ) }
							value={ col || 3 }
							onChange={ ( value ) => setAttributes( { col: value } ) }
							min={ 1 }
							max={ 4 }
						/>
						<SelectControl
							label="Event Grid View Layout"
							value={ layout }
							options={ [
								{ label: 'Default', value: '' },
								{ label: 'Style 2', value: 'style2' },
							] }
							onChange={ ( value ) => setAttributes( { layout: value } ) }
						/>
						<RangeControl
							label={ __( 'Max. Events' ) }
							value={ max_events || 12 }
							onChange={ ( value ) => setAttributes( { max_events: value } ) }
							min={ 1 }
							max={ 100 }
						/>
						<ToggleControl
							label={ __( 'Open Events in new window' ) }
							checked={ new_window }
							onChange={ value => {
								return setAttributes( { new_window: value } );
							}
							}
						/>
					</PanelBody>
				</InspectorControls>
				<ServerSideRender
					block="xtfe-block/facebook-events"
					attributes={attributes}
					key={JSON.stringify(attributes)}
				/>
            </div>
        );
    },
	save: function() {
		// Rendering in PHP.
		return null;
	},
});