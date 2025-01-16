const { registerBlockType } = wp.blocks;
const { createElement } = wp.element;

registerBlockType('acf-searcher/pas-block', {
    title: 'PAS Block',
    icon: 'smiley',
    category: 'common',
    edit: () => createElement('div', {}, 'PAS Block Editor'),
    save: () => null,
});