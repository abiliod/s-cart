<?php
return [
    'menu_titles' => [
        'plugin_shipping'            => 'Envio <span class="right badge badge-warning">' . count(sc_get_all_plugin('Shipping')) . '</span>',
        'plugin_payment'             => 'Pagamento <span class="right badge badge-warning">' . count(sc_get_all_plugin('Payment')) . '</span>',
        'plugin_total'               => 'Total <span class="right badge badge-warning">' . count(sc_get_all_plugin('Total')) . '</span>',
        'plugin_other'               => 'Outro plugin <span class="right badge badge-warning">' . count(sc_get_all_plugin('Other')) . '</span>',
        'plugin_cms'                 => 'Plugins Cms <span class="right badge badge-warning">' . count(sc_get_all_plugin('Cms')) . '</span>',
    ]
];
