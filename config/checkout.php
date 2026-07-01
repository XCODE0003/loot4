<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Express delivery fee
    |--------------------------------------------------------------------------
    |
    | Flat fee added to the order total when the customer selects the "Express"
    | delivery option at checkout. "Standard" is always free. Server-authoritative
    | — the client never sends the fee. Change via the CHECKOUT_EXPRESS_FEE env.
    |
    */

    'express_delivery_fee' => (float) env('CHECKOUT_EXPRESS_FEE', 20.99),

];
