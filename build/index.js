const settings = window.wc.wcSettings.getSetting('payuni_data', {});
const label = window.wp.htmlEntities.decodeEntities(settings.title) || window.wp.i18n.__('Payuni', 'payuni');
const Content = () => {
    return window.wp.htmlEntities.decodeEntities(settings.description || '');
};
const Payuni = {
    name: 'payuni',
    label: label,
    content: Object(window.wp.element.createElement)(Content, null),
    edit: Object(window.wp.element.createElement)(Content, null),
    canMakePayment: () => true,
    ariaLabel: label,
    supports: {
        features: settings.supports,
    },
};
window.wc.wcBlocksRegistry.registerPaymentMethod(Payuni);