const { registerBlockType } = window.wp.blocks;
const { useSelect } = wp.data;
const { Fragment } = wp.element;
const { InspectorControls, InnerBlocks } = wp.blockEditor;
const { PanelBody, SelectControl } = wp.components;
const { addFilter } = wp.hooks;
const { __ } = wp.i18n;
const conditionalContentBlockName = 'crowdfavorite/conditional-content-block';

registerBlockType(conditionalContentBlockName, {
	title: 'Conditional Content Block',
	icon: 'table-row-after',
	category: 'common',
	keywords: [
		__('crowdfavorite', 'cf-conditional-content'),
		__('conditional', 'cf-conditional-content'),
	],
	example: {},
	supports: {
		anchor: true
	},
	attributes: {
		condition: {
			type: 'array',
			default: [''],
		},
	},
	edit(props) {
		// Available conditions
		let conditionsAvailable = [
			{
				label: __('None', 'cf-conditional-content'),
				value: '',
			}
		];

		Object.keys(CFCCGBAdminSettings.conditions).forEach(function (key) {
			conditionsAvailable.push({
				label: CFCCGBAdminSettings.conditions[key].label,
				value: CFCCGBAdminSettings.conditions[key].value
			});
		});

		const { condition } = props.attributes;

		const hasInnerBlocks = useSelect(
			(select) => {
				const { getBlock } = select('core/block-editor');
				const block = getBlock(props.clientId);
				return !!(block && block.innerBlocks.length);
			},
			[props.clientId]
		);

		const allowedBlocks = [];

		wp.blocks.getBlockTypes().forEach((blockType) => {
			if (conditionalContentBlockName !== blockType.name) {
				allowedBlocks.push(blockType.name);
			}
		});

		return (
			<Fragment>
				<InspectorControls>
					<PanelBody
						title={__('Conditional Content', 'cf-conditional-content')}
						initialOpen={true}
					>
						<SelectControl
							label={__('Applied Condition', 'cf-conditional-content')}
							value={condition}
							options={conditionsAvailable}
							onChange={(selectedCondition) => {
								props.setAttributes({
									condition: selectedCondition,
								});
							}}
						/>
					</PanelBody>
				</InspectorControls>
				<div className={props.className} >
					<InnerBlocks
						allowedBlocks={allowedBlocks}

						renderAppender={
							hasInnerBlocks
								? undefined
								: () => <InnerBlocks.ButtonBlockAppender />
						}
					/>
				</div>
			</Fragment>
		);
	},
	save(props) {
		return (
			<div className={props.className}>
				<InnerBlocks.Content />
			</div>
		);

	},
});
