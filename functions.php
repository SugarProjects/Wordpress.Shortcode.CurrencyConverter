<?php
// author: Daniel Stenson
// example: [sp-price price="9.99" currencies="EUR,USD"]
function render_price($params, $content = null) {
    $attrs = shortcode_atts( array(
        'price' => array( ),
        'currencies' => array( )
    ), $params );


    if (empty($attrs['price'])) {
        return "API Key is required.";
    }    

    $price = $attrs['price'];
    $currencies = explode(',', $attrs['currencies']);
    if (!empty($attrs['currencies']) && is_array($attrs['currencies'])) {
        return "Currencies must be an array.";
    }

    wp_enqueue_script( 'money-script' );
    wp_enqueue_script( 'accounting' );

    $fmt = new NumberFormatter( 'en_GB', NumberFormatter::CURRENCY );
    $html = '<div class="sp-price-wrapper"><div class="sp-price sp-price-base">'. numfmt_format_currency($fmt, $price, "EUR") .'</div>';
    $script = '(function ($) { var conversionCallback = function(data) { ' . "\n";
    $script .= 'var element = $(".sp-price-wrapper");' . "\n";
    $script .= '$currency_symbols = { "USD": "$", "EUR": "€", "GBP": "£" };';
    $script .= 'var rate;' . "\n";
    $script .= 'fx.rates = data.rates;' . "\n";
    if (sizeof($currencies) > 0) {
        $script .= 'element.append(\'<div class="sp-price"></div>\');' . "\n";
    }
    foreach ($currencies as $currency){
        $script .= 'rate = fx(' . $price . ').to("' . $currency . '")' . "\n";
        // $script .= 'rate = fx(1).from("GBP").to("' . $currency . '"); ' . "\n";
        $script .= 'var string = \'<div class="sp-price sp-price-'. strtolower($currency) . '">\' + $currency_symbols["'. $currency .'"] + rate.toFixed(2) + \' *</div>\';';
        $script .= 'element.append(string);' . "\n";
    }
    $script .= 'element.append(\'<div class="metadata">* Accurate at \' + getFormattedDate() + \'</div>\');' . "\n";  
    $script .= '};' . "\n";
    $script .= '$.getJSON("//api.fixer.io/latest?base=EUR&symbols='. implode(',', $currencies) .'", conversionCallback);' . "\n";  
    $script .= '})(jQuery);' . "\n";

    wp_add_inline_script('money-script', $script);

    $html .= '</div>';

    return $html;
}
add_shortcode('sp-price', 'render_price');
