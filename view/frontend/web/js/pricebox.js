define(
    [
    'jquery',
    'Magento_Catalog/js/price-utils',
    'underscore',
    'mage/template',
    ],
    function ($, utils, _, mageTemplate) {
        'use strict';

        return function (priceBox) {
            return $.widget(
                'mage.priceBox',
                priceBox,
                {
                    reloadPrice: function reDrawPrices()
                    {
                        let priceFormat = (this.options.priceConfig && this.options.priceConfig.priceFormat) || {},
                        priceTemplate = mageTemplate(this.options.priceTemplate);
                        let result = _.each(
                            this.cache.displayPrices,
                            function (price, priceCode) {
                                price.final = _.reduce(
                                    price.adjustments,
                                    function (memo, amount) {
                                        return memo + amount;
                                    },
                                    price.amount
                                );
                                // you can put your custom code here.
                                price.formatted = utils.formatPrice(price.final, priceFormat);
                                $('[data-price-type="' + priceCode + '"]', this.element).html(priceTemplate({data: price}));
                            },
                            this
                        );
                        $('#paytr_installment_table_amount').val(result.finalPrice.final).trigger('change');
                    }
                }
            );
        };
    }
);
