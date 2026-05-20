<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['code' => 'SA', 'dial' => '+966', 'flag' => '🇸🇦', 'name' => 'Saudi Arabia', 'name_ar' => 'السعودية', 'phone_length' => 9, 'is_default' => true],
            ['code' => 'AE', 'dial' => '+971', 'flag' => '🇦🇪', 'name' => 'United Arab Emirates', 'name_ar' => 'الإمارات', 'phone_length' => 9],
            ['code' => 'KW', 'dial' => '+965', 'flag' => '🇰🇼', 'name' => 'Kuwait', 'name_ar' => 'الكويت', 'phone_length' => 8],
            ['code' => 'QA', 'dial' => '+974', 'flag' => '🇶🇦', 'name' => 'Qatar', 'name_ar' => 'قطر', 'phone_length' => 8],
            ['code' => 'BH', 'dial' => '+973', 'flag' => '🇧🇭', 'name' => 'Bahrain', 'name_ar' => 'البحرين', 'phone_length' => 8],
            ['code' => 'OM', 'dial' => '+968', 'flag' => '🇴🇲', 'name' => 'Oman', 'name_ar' => 'عُمان', 'phone_length' => 8],
            ['code' => 'JO', 'dial' => '+962', 'flag' => '🇯🇴', 'name' => 'Jordan', 'name_ar' => 'الأردن', 'phone_length' => 9],
            ['code' => 'EG', 'dial' => '+20', 'flag' => '🇪🇬', 'name' => 'Egypt', 'name_ar' => 'مصر', 'phone_length' => 10],
            ['code' => 'IQ', 'dial' => '+964', 'flag' => '🇮🇶', 'name' => 'Iraq', 'name_ar' => 'العراق', 'phone_length' => 10],
            ['code' => 'LB', 'dial' => '+961', 'flag' => '🇱🇧', 'name' => 'Lebanon', 'name_ar' => 'لبنان', 'phone_length' => 8],
            ['code' => 'YE', 'dial' => '+967', 'flag' => '🇾🇪', 'name' => 'Yemen', 'name_ar' => 'اليمن', 'phone_length' => 9],
            ['code' => 'TR', 'dial' => '+90', 'flag' => '🇹🇷', 'name' => 'Türkiye', 'name_ar' => 'تركيا', 'phone_length' => 10],
            ['code' => 'PK', 'dial' => '+92', 'flag' => '🇵🇰', 'name' => 'Pakistan', 'name_ar' => 'باكستان', 'phone_length' => 10],
            ['code' => 'IN', 'dial' => '+91', 'flag' => '🇮🇳', 'name' => 'India', 'name_ar' => 'الهند', 'phone_length' => 10],
            ['code' => 'GB', 'dial' => '+44', 'flag' => '🇬🇧', 'name' => 'United Kingdom', 'name_ar' => 'المملكة المتحدة', 'phone_length' => 10],
            ['code' => 'US', 'dial' => '+1',  'flag' => '🇺🇸', 'name' => 'United States', 'name_ar' => 'الولايات المتحدة', 'phone_length' => 10],
        ];

        foreach ($rows as $i => $r) {
            Country::updateOrCreate(
                ['code' => $r['code']],
                $r + ['sort_order' => $i, 'is_active' => true]
            );
        }
    }
}
