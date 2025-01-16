const { registerBlockType } = wp.blocks;
const { createElement } = wp.element;
const { InnerBlocks } = wp.blockEditor;

registerBlockType('acf-searcher/pas-block', {
    title: 'PAS Block',
    icon: 'smiley',
    category: 'common',
    edit: () => {
        return createElement(
            'div',
            {},
            createElement(
                InnerBlocks,
                {
                    allowedBlocks: ['core/columns'],
                    template: [
                        ['core/columns', {}, [
                            ['core/column', {}, [
                                ['core/paragraph', { placeholder: 'Enter text...' }]
                            ]],
                            ['core/column', {}, [
                                ['core/paragraph', { placeholder: 'Enter text...' }]
                            ]]
                        ]]
                    ]
                }
            )
        );
    },
    save: () => null,
});