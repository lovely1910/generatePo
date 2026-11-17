<?php

return [

    // TEMPLATE PERTAMA
    'template_po.docx' => [
        'label' => 'PO BIS SCG',
        'description' => 'Template Purchase Order standar.',
        'fields' => [
            'kapal' => [
                'label' => 'Nama Kapal',
                'type' => 'text',
                'required' => true
            ],
            'quantity' => [
                'label' => 'Quantity (Zak)',
                'type' => 'number',
                'required' => true
            ],
            'harga' => [
                'label' => 'Harga Satuan (Rp)',
                'type' => 'number',
                'required' => true
            ],
        ],
        'placeholders' => [
            'kapal' => 'kapal',
            'quantity' => 'quantity',
            'harga' => 'harga',
        ],
    ],

    // TEMPLATE KEDUA
    'template_IUK_OPC.docx' => [
        'label' => 'PO IUK OPC',
        'description' => 'Template Purchase Order standar.',
        'fields' => [
            'kapal' => [
                'label' => 'Nama Kapal',
                'type' => 'text',
                'required' => true
            ],
            'quantity' => [
                'label' => 'Quantity (Ton)',
                'type' => 'number',
                'required' => true
            ],
            'harga' => [
                'label' => 'Harga Satuan (Rp)',
                'type' => 'number',
                'required' => true
            ],
        ],
        'placeholders' => [
            'kapal' => 'kapal',
            'quantity' => 'quantity',
            'harga' => 'harga',
        ],
    ],

    // TEMPLATE KETIGA
    'template_BIS_Rajawali_fixed.docx' => [
        'label' => 'PO BIS Rajawali',
        'description' => 'Template Purchase Order standar.',
        'fields' => [
            'kapal' => [
                'label' => 'Nama Kapal',
                'type' => 'text',
                'required' => true
            ],
            'quantity' => [
                'label' => 'Quantity (Zak)',
                'type' => 'number',
                'required' => true
            ],
            'harga' => [
                'label' => 'Harga Satuan (Rp)',
                'type' => 'number',
                'required' => true
            ],
            'hari' => [
                'label' => 'Hari',
                'type' => 'text',
                'required' => true
            ],
        ],
        'placeholders' => [
            'kapal' => 'kapal',
            'quantity' => 'quantity',
            'harga' => 'harga',
            'hari' => 'hari',
        ],
    ],

    // TEMPLATE KEEMPAT
        'template_IUK_TigaRoda.docx' => [
        'label' => 'PO IUK Tiga Roda',
        'description' => 'Template Purchase Order standar.',
        'fields' => [
            'kapal' => [
                'label' => 'Nama Kapal',
                'type' => 'text',
                'required' => true
            ],
            'quantity' => [
                'label' => 'Quantity (Ton)',
                'type' => 'number',
                'required' => true
            ],
            'harga' => [
                'label' => 'Harga Satuan (Rp)',
                'type' => 'number',
                'required' => true
            ],
        ],
        'placeholders' => [
            'kapal' => 'kapal',
            'quantity' => 'quantity',
            'harga' => 'harga',
        ],
    ],
    // TEMPLATE KELIMA
        'template_IUK_ultraPro.docx' => [
        'label' => 'PO IUK Ultra Pro',
        'description' => 'Template Purchase Order standar.',
        'fields' => [
            'kapal' => [
                'label' => 'Nama Kapal',
                'type' => 'text',
                'required' => true
            ],
            'quantity' => [
                'label' => 'Quantity (Ton)',
                'type' => 'number',
                'required' => true
            ],
            'harga' => [
                'label' => 'Harga Satuan (Rp)',
                'type' => 'number',
                'required' => true
            ],
        ],
        'placeholders' => [
            'kapal' => 'kapal',
            'quantity' => 'quantity',
            'harga' => 'harga',
        ],
    ],  
    //TEMPLATE KEENAM
        'template_JUK_SCG_fixed.docx' => [
        'label' => 'PO JUK SCG',
        'description' => 'Template Purchase Order standar.',
        'fields' => [
            'kapal' => [
                'label' => 'Nama Kapal',
                'type' => 'text',
                'required' => true
            ],
            'quantity' => [
                'label' => 'Quantity (Ton)',
                'type' => 'number',
                'required' => true
            ],
            'harga' => [
                'label' => 'Harga Satuan (Rp)',
                'type' => 'number',
                'required' => true
            ],
        ],
        'placeholders' => [
            'kapal' => 'kapal',
            'quantity' => 'quantity',
            'harga' => 'harga',
        ],
    ],  

    // TEMPLATE KETUJUH
        'template_JUK_powerPro.docx' => [
        'label' => 'PO JUK Power Pro',
        'description' => 'Template Purchase Order standar.',
        'fields' => [
            'kapal' => [
                'label' => 'Nama Kapal',
                'type' => 'text',
                'required' => true
            ],
            'quantity' => [
                'label' => 'Quantity (Ton)',
                'type' => 'number',
                'required' => true
            ],
            'harga' => [
                'label' => 'Harga Satuan (Rp)',
                'type' => 'number',
                'required' => true
            ],
        ],
        'placeholders' => [
            'kapal' => 'kapal',
            'quantity' => 'quantity',
            'harga' => 'harga',
        ],
    ], 

    // TEMPLATE KEDELAPAN
        'template_JUK_TigaRoda_fixed_v3.docx' => [
        'label' => 'PO JUK Tiga Roda',
        'description' => 'Template Purchase Order standar.',
        'fields' => [
            'kapal' => [
                'label' => 'Nama Kapal',
                'type' => 'text',
                'required' => true
            ],
            'quantity' => [
                'label' => 'Quantity (Ton)',
                'type' => 'number',
                'required' => true
            ],
            'harga' => [
                'label' => 'Harga Satuan (Rp)',
                'type' => 'number',
                'required' => true
            ],
        ],
        'placeholders' => [
            'kapal' => 'kapal',
            'quantity' => 'quantity',
            'harga' => 'harga',
        ],
    ],            
];