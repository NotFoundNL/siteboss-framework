<?php

namespace NotFound\Framework\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CmsFormPropertiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cms_form_properties')->insertOrIgnore(
            [
                [
                    'name' => 'Kopje',
                    'type' => 'header',
                    'options' => json_encode([['type' => 'checkbox', 'label' => 'Tussenkopje', 'internal' => 'subtitle', 'has_value' => 0]]),
                ],
                [
                    'name' => 'Tussentekst',
                    'type' => 'info',
                    'options' => json_encode([['type' => 'textarea', 'label' => 'Tekst', 'internal' => 'text', 'has_value' => 0]]),
                ],
                [
                    'name' => 'Tekstveld',
                    'type' => 'text',
                    'options' => json_encode(
                        [
                            [
                                'internal' => 'required',
                                'label' => 'Verplicht veld',
                                'type' => 'checkbox',
                            ],
                            [
                                'defaultValue' => 12,
                                'internal' => 'width',
                                'label' => 'Breedte veld',
                                'maxValue' => 12,
                                'minValue' => 1,
                                'type' => 'number',
                            ],
                            [
                                'defaultValue' => 12,
                                'internal' => 'mobilewidth',
                                'label' => 'Breedte op mobiel',
                                'maxValue' => 12,
                                'minValue' => 1,
                                'type' => 'number',
                            ],
                            [
                                'internal' => 'placeholder',
                                'label' => 'Placeholder',
                                'type' => 'input',
                            ],
                        ]
                    ),
                ],
                [
                    'name' => 'Datum',
                    'type' => 'date',
                    'options' => json_encode([['type' => 'checkbox', 'label' => 'Verplicht veld', 'internal' => 'required'], ['type' => 'number', 'label' => 'Breedte veld', 'internal' => 'width', 'defaultValue' => 6, 'minValue' => 1, 'maxValue' => 12], ['type' => 'number', 'label' => 'Breedte op mobiel', 'internal' => 'mobilewidth', 'defaultValue' => 12, 'minValue' => 1, 'maxValue' => 12], ['type' => 'input', 'label' => 'Placeholder', 'internal' => 'placeholder'], ['type' => 'checkbox', 'label' => 'Geboortedatum', 'internal' => 'dob']]),
                ],
                [
                    'name' => 'Textarea',
                    'type' => 'textarea',
                    'options' => json_encode([['type' => 'checkbox', 'label' => 'Verplicht veld', 'internal' => 'required', 'defaultValue' => true], ['type' => 'info', 'format' => 'primary', 'text' => 'primary'], ['type' => 'number', 'label' => 'Breedte veld', 'internal' => 'width', 'defaultValue' => 12, 'minValue' => 1, 'maxValue' => 12], ['type' => 'number', 'label' => 'Breedte op mobiel', 'internal' => 'mobilewidth', 'defaultValue' => 12, 'minValue' => 1, 'maxValue' => 12], ['type' => 'input', 'label' => 'Placeholder', 'internal' => 'placeholder']]),
                ],
                [
                    'name' => 'Getal',
                    'type' => 'number',
                    'options' => json_encode([['type' => 'checkbox', 'label' => 'Verplicht veld', 'internal' => 'required'], ['type' => 'number', 'label' => 'Breedte veld', 'internal' => 'width', 'defaultValue' => 12, 'minValue' => 1, 'maxValue' => 12], ['type' => 'number', 'label' => 'Breedte op mobiel', 'internal' => 'mobilewidth', 'defaultValue' => 12, 'minValue' => 1, 'maxValue' => 12], ['type' => 'input', 'label' => 'Placeholder', 'internal' => 'placeholder']]),
                ],
                [
                    'name' => 'E-mailadres',
                    'type' => 'email',
                    'options' => json_encode(
                        [
                            [
                                'type' => 'checkbox',
                                'label' => 'Verplicht veld',
                                'internal' => 'required',
                            ],
                            [
                                'type' => 'checkbox',
                                'label' => 'Primary email',
                                'internal' => 'primary',
                            ],
                            [
                                'type' => 'number',
                                'label' => 'Breedte veld',
                                'internal' => 'width',
                                'defaultValue' => 12,
                                'minValue' => 1,
                                'maxValue' => 12,
                            ],
                            [
                                'type' => 'number',
                                'label' => 'Breedte op mobiel',
                                'internal' => 'mobilewidth',
                                'defaultValue' => 12,
                                'minValue' => 1,
                                'maxValue' => 12,
                            ],
                            [
                                'type' => 'input',
                                'label' => 'Placeholder',
                                'internal' => 'placeholder',
                            ],
                        ]
                    ),
                ],
                [
                    'name' => 'Checkbox',
                    'type' => 'checkbox',
                    'options' => json_encode(
                        [
                            [
                                'type' => 'checkbox',
                                'label' => 'Verplicht veld',
                                'internal' => 'required',
                            ],
                            [
                                'type' => 'number',
                                'label' => 'Breedte veld',
                                'internal' => 'width',
                                'defaultValue' => 12,
                                'minValue' => 1,
                                'maxValue' => 12,
                            ],
                            [
                                'type' => 'number',
                                'label' => 'Breedte op mobiel',
                                'internal' => 'mobilewidth',
                                'defaultValue' => 12,
                                'minValue' => 1,
                                'maxValue' => 12,
                            ],
                            [
                                'type' => 'list',
                                'label' => 'Opties',
                                'internal' => 'options',
                                'defaultValue' => [],
                            ],
                        ]
                    ),
                ],
                [
                    'name' => 'Bestand',
                    'type' => 'file',
                    'options' => json_encode(
                        [
                            [
                                'type' => 'checkbox',
                                'label' => 'Verplicht veld',
                                'internal' => 'required',
                            ],
                            [
                                'type' => 'number',
                                'label' => 'Breedte veld',
                                'internal' => 'width',
                                'defaultValue' => 12,
                                'minValue' => 1,
                                'maxValue' => 12,
                            ],
                            [
                                'type' => 'number',
                                'label' => 'Breedte op mobiel',
                                'internal' => 'mobilewidth',
                                'defaultValue' => 12,
                                'minValue' => 1,
                                'maxValue' => 12,
                            ],
                            [
                                'type' => 'checkbox',
                                'label' => 'Sta meerdere bestanden toe',
                                'internal' => 'multiple',
                            ],
                            [
                                'type' => 'optionlist',
                                'label' => 'Bestand types',
                                'internal' => 'filetypes',
                                'options' => [],
                            ],
                        ]
                    ),
                ],
                [
                    'name' => 'Radio button',
                    'type' => 'radio',
                    'options' => json_encode(
                        [
                            [
                                'type' => 'checkbox',
                                'label' => 'Verplicht veld',
                                'internal' => 'required',
                            ],
                            [
                                'type' => 'number',
                                'label' => 'Breedte veld',
                                'internal' => 'width',
                                'defaultValue' => 12,
                                'minValue' => 1,
                                'maxValue' => 12,
                            ],
                            [
                                'type' => 'number',
                                'label' => 'Breedte op mobiel',
                                'internal' => 'mobilewidth',
                                'defaultValue' => 12,
                                'minValue' => 1,
                                'maxValue' => 12,
                            ],
                            [
                                'type' => 'list',
                                'label' => 'Opties',
                                'internal' => 'options',
                                'defaultValue' => [],
                            ],
                        ]
                    ),
                ],
                [
                    'name' => 'Dropdown',
                    'type' => 'dropdown',
                    'options' => json_encode(
                        [
                            [
                                'type' => 'checkbox',
                                'label' => 'Verplicht veld',
                                'internal' => 'required',
                            ],
                            [
                                'type' => 'number',
                                'label' => 'Breedte veld',
                                'internal' => 'width',
                                'defaultValue' => 12,
                                'minValue' => 1,
                                'maxValue' => 12,
                            ],
                            [
                                'type' => 'number',
                                'label' => 'Breedte op mobiel',
                                'internal' => 'mobilewidth',
                                'defaultValue' => 12,
                                'minValue' => 1,
                                'maxValue' => 12,
                            ],
                            [
                                'type' => 'list',
                                'label' => 'Opties',
                                'internal' => 'options',
                                'defaultValue' => [],
                            ],
                        ]
                    ),
                ],
                [
                    'name' => 'Toggle',
                    'type' => 'toggle',
                    'options' => json_encode(
                        [
                            [
                                'type' => 'checkbox',
                                'label' => 'Verplicht veld',
                                'internal' => 'required',
                            ],
                            [
                                'type' => 'number',
                                'label' => 'Breedte veld',
                                'internal' => 'width',
                                'defaultValue' => 12,
                                'minValue' => 1,
                                'maxValue' => 12,
                            ],
                            [
                                'type' => 'number',
                                'label' => 'Breedte op mobiel',
                                'internal' => 'mobilewidth',
                                'defaultValue' => 12,
                                'minValue' => 1,
                                'maxValue' => 12,
                            ],
                        ]
                    ),
                ],

            ]
        );
    }
}
