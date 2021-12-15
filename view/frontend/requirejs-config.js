let config = {
    config: {
        mixins: {
            'Magento_Catalog/js/price-box': {
                'Paytr_Payment/js/pricebox': true
            }
        }
    },
    map: {
        '*': {
            iframeResizer: "Paytr_Payment/js/iframeResizer.min"
        }
    }
};
