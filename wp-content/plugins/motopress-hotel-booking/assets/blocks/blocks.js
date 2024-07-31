var __ = wp.i18n.__,
	createElement = wp.element.createElement,
	registerBlockType = wp.blocks.registerBlockType,
	ServerSideRender = wp.serverSideRender || wp.components.ServerSideRender, // New version deprecates ServerSideRender in wp.components
	editorControls = wp.blockEditor || wp.editor, // New version deprecates wp.editor
	InspectorControls = editorControls.InspectorControls,
	PanelBody = wp.components.PanelBody,
	TextControl = wp.components.TextControl,
	ToggleControl = wp.components.ToggleControl,
	SelectControl = wp.components.SelectControl,
	BlockControls = editorControls.BlockControls,
	BlockAlignmentToolbar = editorControls.BlockAlignmentToolbar,
	Placeholder = wp.components.Placeholder,
	Disabled = wp.components.Disabled;

MPHBBlockEditor.isValidRoomTypeId = function (idString) {
	var id = parseInt(idString);
	return !isNaN(id) && this.roomTypeIds.indexOf(id) >= 0
};

var getEditWrapperProps = function (attributes) {
	var align = attributes.alignment;

	if (align == 'wide' || align == 'full') {
		return { 'data-align': align };
	}
};

registerBlockType('motopress-hotel-booking/availability-search', {
	title: __('Availability Search Form', 'motopress-hotel-booking'),
	category: 'hotel-booking',
	icon: 'search',
	attributes: {
		adults: { type: 'number', default: MPHBBlockEditor.minAdults },
		children: { type: 'number', default: MPHBBlockEditor.minChildren },
		check_in_date: { type: 'string', default: '' },
		check_out_date: { type: 'string', default: '' },
		attributes: { type: 'string', default: '' },
		alignment: { type: 'string', default: '' }
	},
	getEditWrapperProps: getEditWrapperProps,
	edit: function (props) {
		var isSelected = !!props.isSelected;

		return [
			isSelected && createElement(
				InspectorControls,
				{
					key: 'inspector-controls'
				},
				createElement(
					PanelBody,
					{
						title: __('Settings', 'motopress-hotel-booking')
					},
					[
						createElement(
							TextControl,
							{
								label: __('Adults', 'motopress-hotel-booking'),
								help: __('The number of adults presetted in the search form.', 'motopress-hotel-booking'),
								type: 'number',
								value: props.attributes.adults,
								min: MPHBBlockEditor.minAdults,
								max: MPHBBlockEditor.maxAdults,
								onChange: function (value) {
									props.setAttributes({ adults: parseInt(value) });
								},
								key: 'adults-control'
							}
						),
						createElement(
							TextControl,
							{
								label: __('Children', 'motopress-hotel-booking'),
								help: __('The number of children presetted in the search form.', 'motopress-hotel-booking'),
								type: 'number',
								value: props.attributes.children,
								min: MPHBBlockEditor.minChildren,
								max: MPHBBlockEditor.maxChildren,
								onChange: function (value) {
									props.setAttributes({ children: parseInt(value) });
								},
								key: 'children-control'
							}
						),
						createElement(
							TextControl,
							{
								label: __('Check-in Date', 'motopress-hotel-booking'),
								help: __('Preset date. Formatted as %s', 'motopress-hotel-booking').replace('%s', MPHBBlockEditor.dateFormat),
								value: props.attributes.check_in_date,
								onChange: function (value) {
									props.setAttributes({ check_in_date: value });
								},
								key: 'check_in_date-control'
							}
						),
						createElement(
							TextControl,
							{
								label: __('Check-out Date', 'motopress-hotel-booking'),
								help: __('Preset date. Formatted as %s', 'motopress-hotel-booking').replace('%s', MPHBBlockEditor.dateFormat),
								value: props.attributes.check_out_date,
								onChange: function (value) {
									props.setAttributes({ check_out_date: value });
								},
								key: 'check_out_date-control'
							}
						),
						createElement(
							TextControl,
							{
								label: __('Custom attributes for advanced search.', 'motopress-hotel-booking'),
								help: __('Comma-separated slugs of attributes.', 'motopress-hotel-booking'),
								value: props.attributes.attributes,
								onChange: function (value) {
									props.setAttributes({ attributes: value });
								},
								key: 'attributes-control'
							}
						)
					]
				)
			),
			createElement(
				BlockControls,
				{
					key: 'block-controls'
				},
				createElement(
					BlockAlignmentToolbar,
					{
						value: props.attributes.alignment,
						controls: ['wide', 'full'],
						onChange: function (value) {
							props.setAttributes({ alignment: value });
						}
					}
				)
			),
			createElement(
				Disabled,
				{
					key: 'server-side-render'
				},
				createElement(
					ServerSideRender,
					{
						block: "motopress-hotel-booking/availability-search",
						attributes: props.attributes
					}
				)
			)
		];
	},
	save: function () {
		return null;
	}
});

registerBlockType('motopress-hotel-booking/availability-calendar', {
	title: __('Availability Calendar', 'motopress-hotel-booking'),
	description: __('Display availability calendar of the current accommodation type or by ID.', 'motopress-hotel-booking'),
	category: 'hotel-booking',
	icon: 'calendar-alt',
	attributes: {
		id: { type: 'string', default: '' },
		monthstoshow: { type: 'string', default: '2' },
		display_price: { type: 'boolean', default: false },
		truncate_price: { type: 'boolean', default: true },
		display_currency: { type: 'boolean', default: false },
		alignment: { type: 'string', default: '' }
	},
	getEditWrapperProps: getEditWrapperProps,
	edit: function (props) {
		var isSelected = !!props.isSelected;
		var mayHaveValidOutput = MPHBBlockEditor.isValidRoomTypeId(props.attributes.id);

		return [
			isSelected && createElement(
				InspectorControls,
				{
					key: 'inspector-controls'
				},
				createElement(
					PanelBody,
					{
						title: __('Settings', 'motopress-hotel-booking')
					},
					[
						createElement(
							TextControl,
							{
								label: __('Accommodation Type ID', 'motopress-hotel-booking'),
								help: __('ID of Accommodation Type to check availability.', 'motopress-hotel-booking'),
								value: props.attributes.id,
								onChange: function (value) {
									props.setAttributes({ id: value });
								},
								key: 'id-control'
							}
						),
						createElement(
							TextControl,
							{
								label: __('How many months to show.', 'motopress-hotel-booking'),
								help: __('Set the number of columns or the number of rows and columns separated by comma. Example: "3" or "2,3"', 'motopress-hotel-booking'),
								value: props.attributes.monthstoshow,
								onChange: function (value) {
									props.setAttributes({ monthstoshow: value });
								},
								key: 'monthstoshow-control'
							}
						),
						createElement(
							ToggleControl,
							{
								label: __('Display per-night prices in the availability calendar.', 'motopress-hotel-booking'),
								checked: props.attributes.display_price,
								onChange: function (value) {
									props.setAttributes({ display_price: value });
								},
								key: 'display_price-control'
							}
						),
						createElement(
							ToggleControl,
							{
								label: __('Truncate per-night prices in the availability calendar.', 'motopress-hotel-booking'),
								checked: props.attributes.truncate_price,
								onChange: function (value) {
									props.setAttributes({ truncate_price: value });
								},
								key: 'truncate_price-control'
							}
						),
						createElement(
							ToggleControl,
							{
								label: __('Display the currency sign in the availability calendar.', 'motopress-hotel-booking'),
								checked: props.attributes.display_currency,
								onChange: function (value) {
									props.setAttributes({ display_currency: value });
								},
								key: 'display_currency-control'
							}
						)
					]
				)
			),
			createElement(
				BlockControls,
				{
					key: 'block-controls'
				},
				createElement(
					BlockAlignmentToolbar,
					{
						value: props.attributes.alignment,
						controls: ['wide', 'full'],
						onChange: function (value) {
							props.setAttributes({ alignment: value });
						}
					}
				)
			),
			// dynamic loading from server does not work because we need some
			// callback after loading html to reinit calendar JS and
			// Gutenberg does not have it yet but there is some
			// feature requests so we will be able to fix it later
			// mayHaveValidOutput && createElement(
			//     Disabled,
			//     {
			//         key: 'server-side-render'
			//     },
			//     createElement(
			//         ServerSideRender,
			//         {
			//             block: "motopress-hotel-booking/availability-calendar",
			//             attributes: props.attributes
			//         }
			//     )
			// ),
			// !mayHaveValidOutput && createElement(
			//     Placeholder,
			//     {
			//         icon: 'calendar-alt',
			//         label: __('Availability Calendar', 'motopress-hotel-booking'),
			//         key: 'block-placeholder'
			//     }
			// ),
			createElement(
				Placeholder,
				{
					icon: 'calendar-alt',
					label: __('Availability Calendar', 'motopress-hotel-booking'),
					key: 'block-placeholder'
				}
			)
		];
	},
	save: function () {
		return null;
	}
});

registerBlockType('motopress-hotel-booking/search-results', {
	title: __('Availability Search Results', 'motopress-hotel-booking'),
	description: __('Display listing of accommodation types that meet the search criteria.', 'motopress-hotel-booking'),
	category: 'hotel-booking',
	icon: 'filter',
	attributes: {
		title: { type: 'boolean', default: true },
		featured_image: { type: 'boolean', default: true },
		gallery: { type: 'boolean', default: true },
		excerpt: { type: 'boolean', default: true },
		details: { type: 'boolean', default: true },
		price: { type: 'boolean', default: true },
		view_button: { type: 'boolean', default: true },
		orderby: { type: 'string', default: 'menu_order' },
		order: { type: 'string', default: 'DESC' },
		meta_key: { type: 'string', default: '' },
		meta_type: { type: 'string', default: '' },
		alignment: { type: 'string', default: '' }
	},
	getEditWrapperProps: getEditWrapperProps,
	edit: function (props) {
		var isSelected = !!props.isSelected;

		return [
			isSelected && createElement(
				InspectorControls,
				{
					key: 'inspector-controls'
				},
				[
					createElement(
						PanelBody,
						{
							title: __('Settings', 'motopress-hotel-booking'),
							key: 'settings-panel'
						},
						[
							createElement(
								ToggleControl,
								{
									label: __('Title', 'motopress-hotel-booking'),
									help: __('Whether to display title of the accommodation type.', 'motopress-hotel-booking'),
									checked: props.attributes.title,
									onChange: function (value) {
										props.setAttributes({ title: value });
									},
									key: 'title-control'
								}
							),
							createElement(
								ToggleControl,
								{
									label: __('Featured Image', 'motopress-hotel-booking'),
									help: __('Whether to display featured image of the accommodation type.', 'motopress-hotel-booking'),
									checked: props.attributes.featured_image,
									onChange: function (value) {
										props.setAttributes({ featured_image: value });
									},
									key: 'featured_image-control'
								}
							),
							createElement(
								ToggleControl,
								{
									label: __('Gallery', 'motopress-hotel-booking'),
									help: __('Whether to display gallery of the accommodation type.', 'motopress-hotel-booking'),
									checked: props.attributes.gallery,
									onChange: function (value) {
										props.setAttributes({ gallery: value });
									},
									key: 'gallery-control'
								}
							),
							createElement(
								ToggleControl,
								{
									label: __('Excerpt (short description)', 'motopress-hotel-booking'),
									help: __('Whether to display excerpt (short description) of the accommodation type.', 'motopress-hotel-booking'),
									checked: props.attributes.excerpt,
									onChange: function (value) {
										props.setAttributes({ excerpt: value });
									},
									key: 'excerpt-control'
								}
							),
							createElement(
								ToggleControl,
								{
									label: __('Details', 'motopress-hotel-booking'),
									help: __('Whether to display details of the accommodation type.', 'motopress-hotel-booking'),
									checked: props.attributes.details,
									onChange: function (value) {
										props.setAttributes({ details: value });
									},
									key: 'details-control'
								}
							),
							createElement(
								ToggleControl,
								{
									label: __('Price', 'motopress-hotel-booking'),
									help: __('Whether to display price of the accommodation type.', 'motopress-hotel-booking'),
									checked: props.attributes.price,
									onChange: function (value) {
										props.setAttributes({ price: value });
									},
									key: 'price-control'
								}
							),
							createElement(
								ToggleControl,
								{
									label: __('View Button', 'motopress-hotel-booking'),
									help: __('Whether to display "View Details" button with the link to accommodation type.', 'motopress-hotel-booking'),
									checked: props.attributes.view_button,
									onChange: function (value) {
										props.setAttributes({ view_button: value });
									},
									key: 'view_button-control'
								}
							)
						]
					),
					createElement(
						PanelBody,
						{
							title: __('Order', 'motopress-hotel-booking'),
							initialOpen: false,
							key: 'order-panel'
						},
						[
							createElement(
								SelectControl,
								{
									label: __('Order By', 'motopress-hotel-booking'),
									value: props.attributes.orderby,
									options: [
										{ value: 'none', label: __('No order', 'motopress-hotel-booking') },
										{ value: 'ID', label: __('Post ID', 'motopress-hotel-booking') },
										{ value: 'author', label: __('Post author', 'motopress-hotel-booking') },
										{ value: 'title', label: __('Post title', 'motopress-hotel-booking') },
										{ value: 'name', label: __('Post name (post slug)', 'motopress-hotel-booking') },
										{ value: 'date', label: __('Post date', 'motopress-hotel-booking') },
										{ value: 'modified', label: __('Last modified date', 'motopress-hotel-booking') },
										{ value: 'parent', label: __('Parent ID', 'motopress-hotel-booking') },
										{ value: 'rand', label: __('Random order', 'motopress-hotel-booking') },
										{ value: 'comment_count', label: __('Number of comments', 'motopress-hotel-booking') },
										{ value: 'relevance', label: __('Relevance', 'motopress-hotel-booking') },
										{ value: 'menu_order', label: __('Page order', 'motopress-hotel-booking') },
										{ value: 'meta_value', label: __('Meta value', 'motopress-hotel-booking') },
										{ value: 'meta_value_num', label: __('Numeric meta value', 'motopress-hotel-booking') },
										{ value: 'post__in', label: __('Price', 'motopress-hotel-booking') }
									],
									onChange: function (value) {
										props.setAttributes({ orderby: value });
									},
									key: 'orderby-control'
								}
							),
							createElement(
								SelectControl,
								{
									label: __('Order', 'motopress-hotel-booking'),
									value: props.attributes.order,
									options: [
										{ value: 'ASC', label: __('Ascending (1, 2, 3)', 'motopress-hotel-booking') },
										{ value: 'DESC', label: __('Descending (3, 2, 1)', 'motopress-hotel-booking') }
									],
									onChange: function (value) {
										props.setAttributes({ order: value });
									},
									key: 'order-control'
								}
							),
							createElement(
								TextControl,
								{
									label: __('Meta Name', 'motopress-hotel-booking'),
									help: __('Custom field name. Required if "orderby" is one of the "meta_value", "meta_value_num" or "meta_value_*".', 'motopress-hotel-booking'),
									value: props.attributes.meta_key,
									onChange: function (value) {
										props.setAttributes({ meta_key: value });
									},
									key: 'meta_key-control'
								}
							),
							createElement(
								SelectControl,
								{
									label: __('Meta Type', 'motopress-hotel-booking'),
									help: __('Specified type of the custom field. Can be used in conjunction with "orderby" = "meta_value".', 'motopress-hotel-booking'),
									value: props.attributes.meta_type,
									options: [
										{ value: '', label: __('Any', 'motopress-hotel-booking') },
										{ value: 'NUMERIC', label: __('Numeric', 'motopress-hotel-booking') },
										{ value: 'BINARY', label: __('Binary', 'motopress-hotel-booking') },
										{ value: 'CHAR', label: __('String', 'motopress-hotel-booking') },
										{ value: 'DATE', label: __('Date', 'motopress-hotel-booking') },
										{ value: 'TIME', label: __('Time', 'motopress-hotel-booking') },
										{ value: 'DATETIME', label: __('Date and time', 'motopress-hotel-booking') },
										{ value: 'DECIMAL', label: __('Decimal number', 'motopress-hotel-booking') },
										{ value: 'SIGNED', label: __('Signed number', 'motopress-hotel-booking') },
										{ value: 'UNSIGNED', label: __('Unsigned number', 'motopress-hotel-booking') }
									],
									onChange: function (value) {
										props.setAttributes({ meta_type: value });
									},
									key: 'meta_type-control'
								}
							)
						]
					)
				]
			),
			createElement(
				BlockControls,
				{
					key: 'block-controls'
				},
				createElement(
					BlockAlignmentToolbar,
					{
						value: props.attributes.alignment,
						controls: ['wide', 'full'],
						onChange: function (value) {
							props.setAttributes({ alignment: value });
						}
					}
				)
			),
			createElement(
				Placeholder,
				{
					icon: 'filter',
					label: __('Availability Search Results', 'motopress-hotel-booking'),
					key: 'block-placeholder'
				}
			)
		];
	},
	save: function () {
		return null;
	}
});

registerBlockType('motopress-hotel-booking/rooms', {
	title: __('Accommodation Types Listing', 'motopress-hotel-booking'),
	category: 'hotel-booking',
	icon: 'admin-multisite',
	attributes: {
		title: { type: 'boolean', default: true },
		featured_image: { type: 'boolean', default: true },
		gallery: { type: 'boolean', default: true },
		excerpt: { type: 'boolean', default: true },
		details: { type: 'boolean', default: true },
		price: { type: 'boolean', default: true },
		view_button: { type: 'boolean', default: true },
		book_button: { type: 'boolean', default: true },
		ids: { type: 'string', default: '' },
		posts_per_page: { type: 'string', default: '' },
		category: { type: 'string', default: '' },
		tags: { type: 'string', default: '' },
		relation: { type: 'string', default: 'OR' },
		orderby: { type: 'string', default: 'menu_order' },
		order: { type: 'string', default: 'DESC' },
		meta_key: { type: 'string', default: '' },
		meta_type: { type: 'string', default: '' },
		alignment: { type: 'string', default: '' }
	},
	getEditWrapperProps: getEditWrapperProps,
	edit: function (props) {
		var isSelected = !!props.isSelected;

		return [
			isSelected && createElement(
				InspectorControls,
				{
					key: 'inspector-controls'
				},
				[
					createElement(
						PanelBody,
						{
							title: __('Settings', 'motopress-hotel-booking'),
							key: 'settings-panel'
						},
						[
							createElement(
								ToggleControl,
								{
									label: __('Title', 'motopress-hotel-booking'),
									help: __('Whether to display title of the accommodation type.', 'motopress-hotel-booking'),
									checked: props.attributes.title,
									onChange: function (value) {
										props.setAttributes({ title: value });
									},
									key: 'title-control'
								}
							),
							createElement(
								ToggleControl,
								{
									label: __('Featured Image', 'motopress-hotel-booking'),
									help: __('Whether to display featured image of the accommodation type.', 'motopress-hotel-booking'),
									checked: props.attributes.featured_image,
									onChange: function (value) {
										props.setAttributes({ featured_image: value });
									},
									key: 'featured_image-control'
								}
							),
							createElement(
								ToggleControl,
								{
									label: __('Gallery', 'motopress-hotel-booking'),
									help: __('Whether to display gallery of the accommodation type.', 'motopress-hotel-booking'),
									checked: props.attributes.gallery,
									onChange: function (value) {
										props.setAttributes({ gallery: value });
									},
									key: 'gallery-control'
								}
							),
							createElement(
								ToggleControl,
								{
									label: __('Excerpt (short description)', 'motopress-hotel-booking'),
									help: __('Whether to display excerpt (short description) of the accommodation type.', 'motopress-hotel-booking'),
									checked: props.attributes.excerpt,
									onChange: function (value) {
										props.setAttributes({ excerpt: value });
									},
									key: 'excerpt-control'
								}
							),
							createElement(
								ToggleControl,
								{
									label: __('Details', 'motopress-hotel-booking'),
									help: __('Whether to display details of the accommodation type.', 'motopress-hotel-booking'),
									checked: props.attributes.details,
									onChange: function (value) {
										props.setAttributes({ details: value });
									},
									key: 'details-control'
								}
							),
							createElement(
								ToggleControl,
								{
									label: __('Price', 'motopress-hotel-booking'),
									help: __('Whether to display price of the accommodation type.', 'motopress-hotel-booking'),
									checked: props.attributes.price,
									onChange: function (value) {
										props.setAttributes({ price: value });
									},
									key: 'price-control'
								}
							),
							createElement(
								ToggleControl,
								{
									label: __('View Button', 'motopress-hotel-booking'),
									help: __('Whether to display "View Details" button with the link to accommodation type.', 'motopress-hotel-booking'),
									checked: props.attributes.view_button,
									onChange: function (value) {
										props.setAttributes({ view_button: value });
									},
									key: 'view_button-control'
								}
							),
							createElement(
								ToggleControl,
								{
									label: __('Book Button', 'motopress-hotel-booking'),
									help: __('Whether to display Book button.', 'motopress-hotel-booking'),
									checked: props.attributes.book_button,
									onChange: function (value) {
										props.setAttributes({ book_button: value });
									},
									key: 'book_button-control'
								}
							)
						]
					),
					createElement(
						PanelBody,
						{
							title: __('Query Settings', 'motopress-hotel-booking'),
							initialOpen: false,
							key: 'query-settings-panel'
						},
						[
							createElement(
								TextControl,
								{
									label: __('IDs', 'motopress-hotel-booking'),
									help: __('IDs of accommodations that will be shown.', 'motopress-hotel-booking'),
									value: props.attributes.ids,
									onChange: function (value) {
										props.setAttributes({ ids: value });
									},
									key: 'ids-control'
								}
							),
							createElement(
								TextControl,
								{
									label: __('Count per page', 'motopress-hotel-booking'),
									help: __('integer, -1 to display all, default: "Blog pages show at most"', 'motopress-hotel-booking'),
									value: props.attributes.posts_per_page,
									onChange: function (value) {
										props.setAttributes({ posts_per_page: value });
									},
									key: 'posts_per_page-control'
								}
							),
							createElement(
								TextControl,
								{
									label: __('Categories', 'motopress-hotel-booking'),
									help: __('IDs of categories that will be shown.', 'motopress-hotel-booking'),
									value: props.attributes.category,
									onChange: function (value) {
										props.setAttributes({ category: value });
									},
									key: 'category-control'
								}
							),
							createElement(
								TextControl,
								{
									label: __('Tags', 'motopress-hotel-booking'),
									help: __('IDs of tags that will be shown.', 'motopress-hotel-booking'),
									value: props.attributes.tags,
									onChange: function (value) {
										props.setAttributes({ tags: value });
									},
									key: 'tags-control'
								}
							),
							createElement(
								SelectControl,
								{
									label: __('Relation', 'motopress-hotel-booking'),
									help: __('Logical relationship between each taxonomy when there is more than one.', 'motopress-hotel-booking'),
									value: props.attributes.relation,
									options: [
										{ value: 'AND', label: 'AND' },
										{ value: 'OR', label: 'OR' }
									],
									onChange: function (value) {
										props.setAttributes({ relation: value });
									},
									key: 'relation-control'
								}
							)
						]
					),
					createElement(
						PanelBody,
						{
							title: __('Order', 'motopress-hotel-booking'),
							initialOpen: false,
							key: 'order-panel'
						},
						[
							createElement(
								SelectControl,
								{
									label: __('Order By', 'motopress-hotel-booking'),
									value: props.attributes.orderby,
									options: [
										{ value: 'none', label: __('No order', 'motopress-hotel-booking') },
										{ value: 'ID', label: __('Post ID', 'motopress-hotel-booking') },
										{ value: 'author', label: __('Post author', 'motopress-hotel-booking') },
										{ value: 'title', label: __('Post title', 'motopress-hotel-booking') },
										{ value: 'name', label: __('Post name (post slug)', 'motopress-hotel-booking') },
										{ value: 'date', label: __('Post date', 'motopress-hotel-booking') },
										{ value: 'modified', label: __('Last modified date', 'motopress-hotel-booking') },
										{ value: 'parent', label: __('Parent ID', 'motopress-hotel-booking') },
										{ value: 'rand', label: __('Random order', 'motopress-hotel-booking') },
										{ value: 'comment_count', label: __('Number of comments', 'motopress-hotel-booking') },
										{ value: 'relevance', label: __('Relevance', 'motopress-hotel-booking') },
										{ value: 'menu_order', label: __('Page order', 'motopress-hotel-booking') },
										{ value: 'meta_value', label: __('Meta value', 'motopress-hotel-booking') },
										{ value: 'meta_value_num', label: __('Numeric meta value', 'motopress-hotel-booking') },
										{ value: 'post__in', label: __('Price', 'motopress-hotel-booking') }
									],
									onChange: function (value) {
										props.setAttributes({ orderby: value });
									},
									key: 'orderby-control'
								}
							),
							createElement(
								SelectControl,
								{
									label: __('Order', 'motopress-hotel-booking'),
									value: props.attributes.order,
									options: [
										{ value: 'ASC', label: __('Ascending (1, 2, 3)', 'motopress-hotel-booking') },
										{ value: 'DESC', label: __('Descending (3, 2, 1)', 'motopress-hotel-booking') }
									],
									onChange: function (value) {
										props.setAttributes({ order: value });
									},
									key: 'order-control'
								}
							),
							createElement(
								TextControl,
								{
									label: __('Meta Name', 'motopress-hotel-booking'),
									help: __('Custom field name. Required if "orderby" is one of the "meta_value", "meta_value_num" or "meta_value_*".', 'motopress-hotel-booking'),
									value: props.attributes.meta_key,
									onChange: function (value) {
										props.setAttributes({ meta_key: value });
									},
									key: 'meta_key-control'
								}
							),
							createElement(
								SelectControl,
								{
									label: __('Meta Type', 'motopress-hotel-booking'),
									help: __('Specified type of the custom field. Can be used in conjunction with "orderby" = "meta_value".', 'motopress-hotel-booking'),
									value: props.attributes.meta_type,
									options: [
										{ value: '', label: __('Any', 'motopress-hotel-booking') },
										{ value: 'NUMERIC', label: __('Numeric', 'motopress-hotel-booking') },
										{ value: 'BINARY', label: __('Binary', 'motopress-hotel-booking') },
										{ value: 'CHAR', label: __('String', 'motopress-hotel-booking') },
										{ value: 'DATE', label: __('Date', 'motopress-hotel-booking') },
										{ value: 'TIME', label: __('Time', 'motopress-hotel-booking') },
										{ value: 'DATETIME', label: __('Date and time', 'motopress-hotel-booking') },
										{ value: 'DECIMAL', label: __('Decimal number', 'motopress-hotel-booking') },
										{ value: 'SIGNED', label: __('Signed number', 'motopress-hotel-booking') },
										{ value: 'UNSIGNED', label: __('Unsigned number', 'motopress-hotel-booking') }
									],
									onChange: function (value) {
										props.setAttributes({ meta_type: value });
									},
									key: 'meta_type-control'
								}
							)
						]
					)
				]
			),
			createElement(
				BlockControls,
				{
					key: 'block-controls'
				},
				createElement(
					BlockAlignmentToolbar,
					{
						value: props.attributes.alignment,
						controls: ['wide', 'full'],
						onChange: function (value) {
							props.setAttributes({ alignment: value });
						}
					}
				)
			),
			createElement(
				Disabled,
				{
					key: 'server-side-render'
				},
				createElement(
					ServerSideRender,
					{
						block: "motopress-hotel-booking/rooms",
						attributes: props.attributes
					}
				)
			)
		];
	},
	save: function () {
		return null;
	}
});

registerBlockType('motopress-hotel-booking/services', {
	title: __('Services Listing', 'motopress-hotel-booking'),
	category: 'hotel-booking',
	icon: 'forms',
	attributes: {
		ids: { type: 'string', default: '' },
		posts_per_page: { type: 'string', default: '' },
		orderby: { type: 'string', default: 'menu_order' },
		order: { type: 'string', default: 'DESC' },
		meta_key: { type: 'string', default: '' },
		meta_type: { type: 'string', default: '' },
		alignment: { type: 'string', default: '' }
	},
	getEditWrapperProps: getEditWrapperProps,
	edit: function (props) {
		var isSelected = !!props.isSelected;

		return [
			isSelected && createElement(
				InspectorControls,
				{
					key: 'inspector-controls'
				},
				[
					createElement(
						PanelBody,
						{
							title: __('Query Settings', 'motopress-hotel-booking'),
							key: 'query-settings-panel'
						},
						[
							createElement(
								TextControl,
								{
									label: __('IDs of services that will be shown. ', 'motopress-hotel-booking'),
									help: __('Comma-separated IDs.', 'motopress-hotel-booking'),
									value: props.attributes.ids,
									onChange: function (value) {
										props.setAttributes({ ids: value });
									},
									key: 'ids-control'
								}
							),
							createElement(
								TextControl,
								{
									label: __('Count per page', 'motopress-hotel-booking'),
									help: __('Values: integer, -1 to display all, default: "Blog pages show at most"', 'motopress-hotel-booking'),
									value: props.attributes.posts_per_page,
									onChange: function (value) {
										props.setAttributes({ posts_per_page: value });
									},
									key: 'posts_per_page-control'
								}
							)
						]
					),
					createElement(
						PanelBody,
						{
							title: __('Order', 'motopress-hotel-booking'),
							initialOpen: false,
							key: 'order-panel'
						},
						[
							createElement(
								SelectControl,
								{
									label: __('Order By', 'motopress-hotel-booking'),
									value: props.attributes.orderby,
									options: [
										{ value: 'none', label: __('No order', 'motopress-hotel-booking') },
										{ value: 'ID', label: __('Post ID', 'motopress-hotel-booking') },
										{ value: 'author', label: __('Post author', 'motopress-hotel-booking') },
										{ value: 'title', label: __('Post title', 'motopress-hotel-booking') },
										{ value: 'name', label: __('Post name (post slug)', 'motopress-hotel-booking') },
										{ value: 'date', label: __('Post date', 'motopress-hotel-booking') },
										{ value: 'modified', label: __('Last modified date', 'motopress-hotel-booking') },
										{ value: 'parent', label: __('Parent ID', 'motopress-hotel-booking') },
										{ value: 'rand', label: __('Random order', 'motopress-hotel-booking') },
										{ value: 'comment_count', label: __('Number of comments', 'motopress-hotel-booking') },
										{ value: 'relevance', label: __('Relevance', 'motopress-hotel-booking') },
										{ value: 'menu_order', label: __('Page order', 'motopress-hotel-booking') },
										{ value: 'meta_value', label: __('Meta value', 'motopress-hotel-booking') },
										{ value: 'meta_value_num', label: __('Numeric meta value', 'motopress-hotel-booking') },
										{ value: 'post__in', label: __('Price', 'motopress-hotel-booking') }
									],
									onChange: function (value) {
										props.setAttributes({ orderby: value });
									},
									key: 'orderby-control'
								}
							),
							createElement(
								SelectControl,
								{
									label: __('Order', 'motopress-hotel-booking'),
									value: props.attributes.order,
									options: [
										{ value: 'ASC', label: __('Ascending (1, 2, 3)', 'motopress-hotel-booking') },
										{ value: 'DESC', label: __('Descending (3, 2, 1)', 'motopress-hotel-booking') }
									],
									onChange: function (value) {
										props.setAttributes({ order: value });
									},
									key: 'order-control'
								}
							),
							createElement(
								TextControl,
								{
									label: __('Meta Name', 'motopress-hotel-booking'),
									help: __('Custom field name. Required if "orderby" is one of the "meta_value", "meta_value_num" or "meta_value_*".', 'motopress-hotel-booking'),
									value: props.attributes.meta_key,
									onChange: function (value) {
										props.setAttributes({ meta_key: value });
									},
									key: 'meta_key-control'
								}
							),
							createElement(
								SelectControl,
								{
									label: __('Meta Type', 'motopress-hotel-booking'),
									help: __('Specified type of the custom field. Can be used in conjunction with "orderby" = "meta_value".', 'motopress-hotel-booking'),
									value: props.attributes.meta_type,
									options: [
										{ value: '', label: __('Any', 'motopress-hotel-booking') },
										{ value: 'NUMERIC', label: __('Numeric', 'motopress-hotel-booking') },
										{ value: 'BINARY', label: __('Binary', 'motopress-hotel-booking') },
										{ value: 'CHAR', label: __('String', 'motopress-hotel-booking') },
										{ value: 'DATE', label: __('Date', 'motopress-hotel-booking') },
										{ value: 'TIME', label: __('Time', 'motopress-hotel-booking') },
										{ value: 'DATETIME', label: __('Date and time', 'motopress-hotel-booking') },
										{ value: 'DECIMAL', label: __('Decimal number', 'motopress-hotel-booking') },
										{ value: 'SIGNED', label: __('Signed number', 'motopress-hotel-booking') },
										{ value: 'UNSIGNED', label: __('Unsigned number', 'motopress-hotel-booking') }
									],
									onChange: function (value) {
										props.setAttributes({ meta_type: value });
									},
									key: 'meta_type-control'
								}
							)
						]
					)
				]
			),
			createElement(
				BlockControls,
				{
					key: 'block-controls'
				},
				createElement(
					BlockAlignmentToolbar,
					{
						value: props.attributes.alignment,
						controls: ['wide', 'full'],
						onChange: function (value) {
							props.setAttributes({ alignment: value });
						}
					}
				)
			),
			createElement(
				Disabled,
				{
					key: 'server-side-render'
				},
				createElement(
					ServerSideRender,
					{
						block: "motopress-hotel-booking/services",
						attributes: props.attributes
					}
				)
			)
		];
	},
	save: function () {
		return null;
	}
});

registerBlockType('motopress-hotel-booking/room', {
	title: __('Single Accommodation Type', 'motopress-hotel-booking'),
	category: 'hotel-booking',
	icon: 'admin-home',
	attributes: {
		id: { type: 'string', default: '' },
		title: { type: 'boolean', default: true },
		featured_image: { type: 'boolean', default: true },
		gallery: { type: 'boolean', default: true },
		excerpt: { type: 'boolean', default: true },
		details: { type: 'boolean', default: true },
		price: { type: 'boolean', default: true },
		view_button: { type: 'boolean', default: true },
		book_button: { type: 'boolean', default: true },
		alignment: { type: 'string', default: '' }
	},
	getEditWrapperProps: getEditWrapperProps,
	edit: function (props) {
		var isSelected = !!props.isSelected;
		var mayHaveValidOutput = MPHBBlockEditor.isValidRoomTypeId(props.attributes.id);

		return [
			isSelected && createElement(
				InspectorControls,
				{
					key: 'inspector-controls'
				},
				createElement(
					PanelBody,
					{
						title: __('Settings', 'motopress-hotel-booking')
					},
					[
						createElement(
							TextControl,
							{
								label: __('ID', 'motopress-hotel-booking'),
								help: __('ID of accommodation type to display.', 'motopress-hotel-booking'),
								value: props.attributes.id,
								onChange: function (value) {
									props.setAttributes({ id: value });
								},
								key: 'id-control'
							}
						),
						createElement(
							ToggleControl,
							{
								label: __('Title', 'motopress-hotel-booking'),
								help: __('Whether to display title of the accommodation type.', 'motopress-hotel-booking'),
								checked: props.attributes.title,
								onChange: function (value) {
									props.setAttributes({ title: value });
								},
								key: 'title-control'
							}
						),
						createElement(
							ToggleControl,
							{
								label: __('Featured Image', 'motopress-hotel-booking'),
								help: __('Whether to display featured image of the accommodation type.', 'motopress-hotel-booking'),
								checked: props.attributes.featured_image,
								onChange: function (value) {
									props.setAttributes({ featured_image: value });
								},
								key: 'featured_image-control'
							}
						),
						createElement(
							ToggleControl,
							{
								label: __('Gallery', 'motopress-hotel-booking'),
								help: __('Whether to display gallery of the accommodation type.', 'motopress-hotel-booking'),
								checked: props.attributes.gallery,
								onChange: function (value) {
									props.setAttributes({ gallery: value });
								},
								key: 'gallery-control'
							}
						),
						createElement(
							ToggleControl,
							{
								label: __('Excerpt (short description)', 'motopress-hotel-booking'),
								help: __('Whether to display excerpt (short description) of the accommodation type.', 'motopress-hotel-booking'),
								checked: props.attributes.excerpt,
								onChange: function (value) {
									props.setAttributes({ excerpt: value });
								},
								key: 'excerpt-control'
							}
						),
						createElement(
							ToggleControl,
							{
								label: __('Details', 'motopress-hotel-booking'),
								help: __('Whether to display details of the accommodation type.', 'motopress-hotel-booking'),
								checked: props.attributes.details,
								onChange: function (value) {
									props.setAttributes({ details: value });
								},
								key: 'details-control'
							}
						),
						createElement(
							ToggleControl,
							{
								label: __('Price', 'motopress-hotel-booking'),
								help: __('Whether to display price of the accommodation type.', 'motopress-hotel-booking'),
								checked: props.attributes.price,
								onChange: function (value) {
									props.setAttributes({ price: value });
								},
								key: 'price-control'
							}
						),
						createElement(
							ToggleControl,
							{
								label: __('View Button', 'motopress-hotel-booking'),
								help: __('Whether to display "View Details" button with the link to accommodation type.', 'motopress-hotel-booking'),
								checked: props.attributes.view_button,
								onChange: function (value) {
									props.setAttributes({ view_button: value });
								},
								key: 'view_button-control'
							}
						),
						createElement(
							ToggleControl,
							{
								label: __('Book Button', 'motopress-hotel-booking'),
								help: __('Whether to display Book button.', 'motopress-hotel-booking'),
								checked: props.attributes.book_button,
								onChange: function (value) {
									props.setAttributes({ book_button: value });
								},
								key: 'book_button-control'
							}
						)
					]
				)
			),
			createElement(
				BlockControls,
				{
					key: 'block-controls'
				},
				createElement(
					BlockAlignmentToolbar,
					{
						value: props.attributes.alignment,
						controls: ['wide', 'full'],
						onChange: function (value) {
							props.setAttributes({ alignment: value });
						}
					}
				)
			),
			mayHaveValidOutput && createElement(
				Disabled,
				{
					key: 'server-side-render'
				},
				createElement(
					ServerSideRender,
					{
						block: "motopress-hotel-booking/room",
						attributes: props.attributes
					}
				)
			),
			!mayHaveValidOutput && createElement(
				Placeholder,
				{
					icon: 'admin-home',
					label: __('Single Accommodation Type', 'motopress-hotel-booking'),
					key: 'block-placeholder'
				}
			)
		];
	},
	save: function () {
		return null;
	}
});

registerBlockType('motopress-hotel-booking/checkout', {
	title: __('Checkout Form', 'motopress-hotel-booking'),
	description: __('Display checkout form.', 'motopress-hotel-booking'),
	category: 'hotel-booking',
	icon: 'cart',
	attributes: {
		alignment: { type: 'string', default: '' }
	},
	getEditWrapperProps: getEditWrapperProps,
	edit: function (props) {
		return [
			createElement(
				BlockControls,
				{
					key: 'block-controls'
				},
				createElement(
					BlockAlignmentToolbar,
					{
						value: props.attributes.alignment,
						controls: ['wide', 'full'],
						onChange: function (value) {
							props.setAttributes({ alignment: value });
						}
					}
				)
			),
			createElement(
				Placeholder,
				{
					icon: 'cart',
					label: __('Checkout Form', 'motopress-hotel-booking'),
					key: 'block-placeholder'
				}
			)
		];
	},
	save: function () {
		return null;
	}
});

registerBlockType('motopress-hotel-booking/availability', {
	title: __('Booking Form', 'motopress-hotel-booking'),
	category: 'hotel-booking',
	icon: 'feedback',
	attributes: {
		id: { type: 'string', default: '' },
		alignment: { type: 'string', default: '' }
	},
	getEditWrapperProps: getEditWrapperProps,
	edit: function (props) {
		var isSelected = !!props.isSelected;
		var mayHaveValidOutput = MPHBBlockEditor.isValidRoomTypeId(props.attributes.id);

		return [
			isSelected && createElement(
				InspectorControls,
				{
					key: 'inspector-controls'
				},
				createElement(
					PanelBody,
					{
						title: __('Settings', 'motopress-hotel-booking')
					},
					createElement(
						TextControl,
						{
							label: __('Accommodation Type ID', 'motopress-hotel-booking'),
							help: __('ID of Accommodation Type to check availability.', 'motopress-hotel-booking'),
							value: props.attributes.id,
							onChange: function (value) {
								props.setAttributes({ id: value });
							}
						}
					)
				)
			),
			createElement(
				BlockControls,
				{
					key: 'block-controls'
				},
				createElement(
					BlockAlignmentToolbar,
					{
						value: props.attributes.alignment,
						controls: ['wide', 'full'],
						onChange: function (value) {
							props.setAttributes({ alignment: value });
						}
					}
				)
			),
			mayHaveValidOutput && createElement(
				Disabled,
				{
					key: 'server-side-render'
				},
				createElement(
					ServerSideRender,
					{
						block: "motopress-hotel-booking/availability",
						attributes: props.attributes
					}
				)
			),
			!mayHaveValidOutput && createElement(
				Placeholder,
				{
					icon: 'feedback',
					label: __('Booking Form', 'motopress-hotel-booking'),
					key: 'block-placeholder'
				}
			)
		];
	},
	save: function () {
		return null;
	}
});

registerBlockType('motopress-hotel-booking/rates', {
	title: __('Accommodation Rates List', 'motopress-hotel-booking'),
	category: 'hotel-booking',
	icon: 'admin-settings',
	attributes: {
		id: { type: 'string', default: '' },
		alignment: { type: 'string', default: '' }
	},
	getEditWrapperProps: getEditWrapperProps,
	edit: function (props) {
		var isSelected = !!props.isSelected;
		var mayHaveValidOutput = MPHBBlockEditor.isValidRoomTypeId(props.attributes.id);

		return [
			isSelected && createElement(
				InspectorControls,
				{
					key: 'inspector-controls'
				},
				createElement(
					PanelBody,
					{
						title: __('Settings', 'motopress-hotel-booking')
					},
					createElement(
						TextControl,
						{
							label: __('Accommodation Type ID', 'motopress-hotel-booking'),
							help: __('ID of accommodation type.', 'motopress-hotel-booking'),
							value: props.attributes.id,
							onChange: function (value) {
								props.setAttributes({ id: value });
							}
						}
					)
				)
			),
			createElement(
				BlockControls,
				{
					key: 'block-controls'
				},
				createElement(
					BlockAlignmentToolbar,
					{
						value: props.attributes.alignment,
						controls: ['wide', 'full'],
						onChange: function (value) {
							props.setAttributes({ alignment: value });
						}
					}
				)
			),
			mayHaveValidOutput && createElement(
				Disabled,
				{
					key: 'server-side-render'
				},
				createElement(
					ServerSideRender,
					{
						block: "motopress-hotel-booking/rates",
						attributes: props.attributes
					}
				)
			),
			!mayHaveValidOutput && createElement(
				Placeholder,
				{
					icon: 'admin-settings',
					label: __('Accommodation Rates List', 'motopress-hotel-booking'),
					key: 'block-placeholder'
				}
			)
		];
	},
	save: function () {
		return null;
	}
});

registerBlockType('motopress-hotel-booking/booking-confirmation', {
	title: __('Booking Confirmation', 'motopress-hotel-booking'),
	description: __('Display booking and payment details.', 'motopress-hotel-booking'),
	category: 'hotel-booking',
	icon: 'thumbs-up',
	attributes: {
		alignment: { type: 'string', default: '' }
	},
	getEditWrapperProps: getEditWrapperProps,
	edit: function (props) {
		return [
			createElement(
				BlockControls,
				{
					key: 'block-controls'
				},
				createElement(
					BlockAlignmentToolbar,
					{
						value: props.attributes.alignment,
						controls: ['wide', 'full'],
						onChange: function (value) {
							props.setAttributes({ alignment: value });
						}
					}
				)
			),
			createElement(
				Placeholder,
				{
					icon: 'thumbs-up',
					label: __('Booking Confirmation', 'motopress-hotel-booking'),
					key: 'block-placeholder'
				}
			)
		];
	},
	save: function () {
		return null;
	}
});

(function ($) {
	"use strict";

	/**
	 * @since 3.7.1
	 */
	var BlockObserver = function (blockNode) {
		this.block = blockNode;

		this.observer = new MutationObserver(this.onChange.bind(this));
		this.observer.observe(blockNode, { childList: true, subtree: true });
	};

	BlockObserver.prototype = {
		block: null,
		observer: null,

		onChange: function (mutations) {
			var self = this;

			mutations.forEach(function (mutation) {
				if (mutation.addedNodes.length > 0) {
					mutation.addedNodes.forEach(self.onNodeAdded.bind(self));
				}
			});
		},

		onNodeAdded: function (node) {
			var elementsToReinit = node.getElementsByClassName('mphb-gutenberg-reinit');

			if (elementsToReinit.length > 0) {

				var $shortcodeWrapper = $(elementsToReinit[0]); // Only one element possible

				$shortcodeWrapper.children('.mphb-calendar.mphb-datepick:not(.is-datepick)').each(function (_, calendar) {
					new MPHB.RoomTypeCalendar($(calendar));
				});

				$shortcodeWrapper.find('.mphb-flexslider-gallery-wrapper:not(.mphb-flexslider)').each(function (_, galleryWrapper) {
					var gallery = new MPHB.FlexsliderGallery(galleryWrapper);
					gallery.initSliders();
				});
			}
		},

		disconnect: function () {
			this.observer.disconnect();
		}
	};

	/**
	 * @since 3.7.1
	 */
	var BlocksListObserver = function () {
		wp.data.subscribe(this.load.bind(this));
	};

	BlocksListObserver.prototype = {
		isLoaded: false,

		$blocksList: null,

		listObserver: null,
		blockObservers: {},

		load: function () {
			if (this.isLoaded) {
				return;
			}

			var $blocksList = $('.editor-block-list__layout');

			if ($blocksList.length > 0) {

				this.isLoaded = true;

				this.$blocksList = $blocksList;

				this.listObserver = new MutationObserver(this.onChange.bind(this));
				this.listObserver.observe($blocksList[0], { childList: true });
			}
		},

		onChange: function (mutations) {
			var self = this;

			mutations.forEach(function (mutation) {
				if (mutation.addedNodes.length > 0) {
					mutation.addedNodes.forEach(self.onNodeAdded.bind(self));
				}
				if (mutation.removedNodes.length > 0) {
					mutation.removedNodes.forEach(self.onNodeRemoved.bind(self));
				}
			});
		},

		onNodeAdded: function (node) {
			if (this.isCalendarOrSlider(node)) {
				var id = node.getAttribute('id');

				if (id != null && !this.blockObservers.hasOwnProperty(id)) {
					this.blockObservers[id] = new BlockObserver(node);
				}
			}
		},

		onNodeRemoved: function (node) {
			var id = node.getAttribute('id');

			if (id != null && this.blockObservers.hasOwnProperty(id)) {
				this.blockObservers[id].disconnect();
				delete this.blockObservers[id];
			}
		},

		isCalendarOrSlider: function (node) {
			var type = node.getAttribute('data-type') || 'undefined';

			switch (type) {
				case 'motopress-hotel-booking/availability-calendar':
				case 'motopress-hotel-booking/rooms':
				case 'motopress-hotel-booking/room':
					return true;
			}

			return false;
		}
	};

	new BlocksListObserver();
})(jQuery);
