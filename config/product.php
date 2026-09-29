<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Product Categories
    |--------------------------------------------------------------------------
    |
    | Main product category. The key is stored in the database, the value is
    | what the user sees in the dropdown. Add a new key/value pair to add
    | a new category everywhere (product form, bill items, reports).
    |
    */

    'categories' => [
        'boys' => 'Boys',
        'girls' => 'Girls',
        'garments' => 'Garments',
        'hosiery' => 'Hosiery',
    ],

    /*
    |--------------------------------------------------------------------------
    | Product Sizes
    |--------------------------------------------------------------------------
    |
    | Suggestions offered in the size input on the product form and on the
    | bill items table. Free text is still allowed.
    |
    */

    'sizes' => [
        'Free', 'XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL', '4XL', '5XL',
        '28', '30', '32', '34', '36', '38', '40', '42', '44', '46',
    ],

];
