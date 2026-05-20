<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class AppSettingSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // Support contacts
            ['group' => 'support', 'key' => 'support.phone',         'label' => 'Support phone',    'value' => '+966559809687',  'type' => 'tel'],
            ['group' => 'support', 'key' => 'support.whatsapp',      'label' => 'WhatsApp number',  'value' => '966559809687',   'type' => 'tel'],
            ['group' => 'support', 'key' => 'support.email_general', 'label' => 'General email',    'value' => 'info@velto.sa',  'type' => 'email'],
            ['group' => 'support', 'key' => 'support.email_support', 'label' => 'Support email',    'value' => 'support@velto.sa','type' => 'email'],
            ['group' => 'support', 'key' => 'support.website_display','label' => 'Website (label)', 'value' => 'www.velto.sa',   'type' => 'string'],
            ['group' => 'support', 'key' => 'support.website_url',   'label' => 'Website URL',      'value' => 'https://velto.sa/','type' => 'url'],

            // Brand
            ['group' => 'brand',   'key' => 'brand.name',            'label' => 'Brand name',       'value' => 'Velto',          'type' => 'string'],
            ['group' => 'brand',   'key' => 'brand.tagline',         'label' => 'Tagline',          'value' => 'Mobile car care that comes to you', 'type' => 'string'],
        ];

        foreach ($rows as $row) {
            AppSetting::updateOrCreate(
                ['key' => $row['key']],
                $row,
            );
        }
    }
}
