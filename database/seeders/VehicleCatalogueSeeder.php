<?php

namespace Database\Seeders;

use App\Models\VehicleBrand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The brands and models a Velto customer can pick from.
 *
 * The catalogue shipped with six brands of exactly eight models each — seed
 * data, not a real list. A customer driving a Kia, Ford or Chevrolet had
 * nothing to choose, and adding a vehicle is a required step before booking.
 *
 * This is curated for Saudi Arabia rather than imported wholesale. The public
 * datasets that cover "every" make are a poor fit: the widely used one is
 * mostly snowmobiles and ATVs, stops at 2016, and carries no Arabic at all —
 * and every row here needs a name_ar, because the app is Arabic-first and the
 * existing rows use real transliterations (Camry / كامري), not copies of the
 * Latin name.
 *
 * Idempotent: matched on slug for brands and on brand+name for models, so it
 * can be re-run and will not duplicate or disturb existing ids. Nothing is
 * deleted — a brand removed from this list stays in the database, because a
 * customer may already own that car.
 */
class VehicleCatalogueSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->catalogue() as $order => [$name, $nameAr, $models]) {
            // Matched on name, not slug: the existing Mercedes-Benz row was
            // slugged "mercedes_benz" while Str::slug() yields "mercedes-benz",
            // so keying on the slug silently created a second brand.
            $brand = VehicleBrand::firstWhere('name', $name);

            if ($brand === null) {
                $brand = VehicleBrand::create([
                    'slug' => Str::slug($name),
                    'name' => $name,
                    'name_ar' => $nameAr,
                    'is_active' => true,
                    'sort_order' => $order,
                ]);
            } else {
                // Leave the existing slug alone — it may be referenced elsewhere.
                $brand->update(['name_ar' => $nameAr, 'is_active' => true, 'sort_order' => $order]);
            }

            foreach (array_values($models) as $i => [$model, $modelAr]) {
                DB::table('vehicle_model_entries')->updateOrInsert(
                    ['vehicle_brand_id' => $brand->id, 'name' => $model],
                    [
                        'name_ar' => $modelAr,
                        'is_active' => true,
                        'sort_order' => $i,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ],
                );
            }
        }
    }

    /**
     * Ordered by rough share of cars on Saudi roads, so the most likely pick is
     * nearest the top of the list rather than alphabetical.
     *
     * @return array<int, array{0:string,1:string,2:array<int,array{0:string,1:string}>}>
     */
    private function catalogue(): array
    {
        return [
            ['Toyota', 'تويوتا', [
                ['Camry', 'كامري'], ['Corolla', 'كورولا'], ['Land Cruiser', 'لاند كروزر'],
                ['Prado', 'برادو'], ['Hilux', 'هايلكس'], ['Yaris', 'يارس'],
                ['RAV4', 'راف٤'], ['Fortuner', 'فورتشنر'], ['Avalon', 'أفالون'],
                ['Innova', 'إينوفا'], ['Rush', 'راش'], ['Highlander', 'هايلاندر'],
            ]],
            ['Hyundai', 'هيونداي', [
                ['Elantra', 'إلنترا'], ['Sonata', 'سوناتا'], ['Accent', 'أكسنت'],
                ['Tucson', 'توسان'], ['Santa Fe', 'سنتافي'], ['Creta', 'كريتا'],
                ['Azera', 'أزيرا'], ['Palisade', 'باليسايد'], ['Staria', 'ستاريا'],
                ['Venue', 'فينيو'],
            ]],
            ['Kia', 'كيا', [
                ['Cerato', 'سيراتو'], ['Optima', 'أوبتيما'], ['K5', 'كي٥'],
                ['Sportage', 'سبورتاج'], ['Sorento', 'سورنتو'], ['Seltos', 'سيلتوس'],
                ['Carnival', 'كرنفال'], ['Pegas', 'بيجاس'], ['Telluride', 'تيلورايد'],
                ['Rio', 'ريو'],
            ]],
            ['Nissan', 'نيسان', [
                ['Sunny', 'صني'], ['Altima', 'ألتيما'], ['Maxima', 'ماكسيما'],
                ['Patrol', 'باترول'], ['X-Trail', 'إكس-تريل'], ['Kicks', 'كيكس'],
                ['Pathfinder', 'باثفايندر'], ['Navara', 'نافارا'], ['Urvan', 'أورفان'],
            ]],
            ['Chevrolet', 'شفروليه', [
                ['Malibu', 'ماليبو'], ['Impala', 'إمبالا'], ['Tahoe', 'تاهو'],
                ['Suburban', 'سوبربان'], ['Traverse', 'ترافيرس'], ['Captiva', 'كابتيفا'],
                ['Silverado', 'سلفرادو'], ['Groove', 'جروف'],
            ]],
            ['Ford', 'فورد', [
                ['Taurus', 'تورس'], ['Explorer', 'إكسبلورر'], ['Expedition', 'إكسبيديشن'],
                ['F-150', 'إف-١٥٠'], ['Edge', 'إيدج'], ['Ranger', 'رينجر'],
                ['Territory', 'تيريتوري'], ['Mustang', 'موستانج'],
            ]],
            ['Honda', 'هوندا', [
                ['Accord', 'أكورد'], ['Civic', 'سيفيك'], ['CR-V', 'سي آر-في'],
                ['Pilot', 'بايلوت'], ['City', 'سيتي'], ['HR-V', 'إتش آر-في'],
            ]],
            ['Lexus', 'لكزس', [
                ['ES', 'إي إس'], ['LS', 'إل إس'], ['LX', 'إل إكس'],
                ['GX', 'جي إكس'], ['RX', 'آر إكس'], ['NX', 'إن إكس'],
                ['UX', 'يو إكس'], ['IS', 'آي إس'],
            ]],
            ['GMC', 'جي إم سي', [
                ['Yukon', 'يوكن'], ['Sierra', 'سييرا'], ['Acadia', 'أكاديا'],
                ['Terrain', 'تيرين'],
            ]],
            ['Mercedes-Benz', 'مرسيدس-بنز', [
                ['C-Class', 'الفئة C'], ['E-Class', 'الفئة E'], ['S-Class', 'الفئة S'],
                ['G-Class', 'الفئة G'], ['GLC', 'جي إل سي'], ['GLE', 'جي إل إي'],
                ['GLS', 'جي إل إس'], ['A-Class', 'الفئة A'],
            ]],
            ['BMW', 'بي إم دبليو', [
                ['3 Series', 'الفئة الثالثة'], ['5 Series', 'الفئة الخامسة'],
                ['7 Series', 'الفئة السابعة'], ['X1', 'إكس١'], ['X3', 'إكس٣'],
                ['X5', 'إكس٥'], ['X6', 'إكس٦'], ['X7', 'إكس٧'],
            ]],
            ['Mazda', 'مازدا', [
                ['Mazda 3', 'مازدا ٣'], ['Mazda 6', 'مازدا ٦'], ['CX-5', 'سي إكس-٥'],
                ['CX-9', 'سي إكس-٩'], ['CX-30', 'سي إكس-٣٠'],
            ]],
            ['Mitsubishi', 'ميتسوبيشي', [
                ['Lancer', 'لانسر'], ['Attrage', 'أتراج'], ['Pajero', 'باجيرو'],
                ['Montero Sport', 'مونتيرو سبورت'], ['Outlander', 'أوتلاندر'],
                ['L200', 'إل٢٠٠'],
            ]],
            ['Land Rover', 'لاند روفر', [
                ['Range Rover', 'رنج روفر'], ['Range Rover Sport', 'رنج روفر سبورت'],
                ['Range Rover Vogue', 'رنج روفر فوج'], ['Defender', 'ديفندر'],
                ['Discovery', 'ديسكفري'],
            ]],
            ['Volkswagen', 'فولكس فاجن', [
                ['Golf', 'جولف'], ['Passat', 'باسات'], ['Tiguan', 'تيجوان'],
                ['Teramont', 'تيرامونت'],
            ]],
            ['Audi', 'أودي', [
                ['A4', 'إيه٤'], ['A6', 'إيه٦'], ['A8', 'إيه٨'],
                ['Q5', 'كيو٥'], ['Q7', 'كيو٧'], ['Q8', 'كيو٨'],
            ]],
            ['Infiniti', 'إنفينيتي', [
                ['Q50', 'كيو٥٠'], ['QX50', 'كيو إكس٥٠'], ['QX60', 'كيو إكس٦٠'],
                ['QX80', 'كيو إكس٨٠'],
            ]],
            ['Jeep', 'جيب', [
                ['Wrangler', 'رانجلر'], ['Grand Cherokee', 'جراند شيروكي'],
                ['Cherokee', 'شيروكي'], ['Compass', 'كومباس'],
            ]],
            ['Suzuki', 'سوزوكي', [
                ['Baleno', 'بالينو'], ['Dzire', 'ديزاير'], ['Ertiga', 'إرتيجا'],
                ['Vitara', 'فيتارا'], ['Jimny', 'جيمني'],
            ]],
            ['Isuzu', 'إيسوزو', [
                ['D-Max', 'دي-ماكس'], ['MU-X', 'إم يو-إكس'],
            ]],
            ['Renault', 'رينو', [
                ['Duster', 'داستر'], ['Koleos', 'كوليوس'], ['Megane', 'ميجان'],
                ['Symbol', 'سيمبول'],
            ]],
            ['Peugeot', 'بيجو', [
                ['301', '٣٠١'], ['3008', '٣٠٠٨'], ['5008', '٥٠٠٨'],
            ]],
            ['Chery', 'شيري', [
                ['Tiggo 4', 'تيجو ٤'], ['Tiggo 7', 'تيجو ٧'], ['Tiggo 8', 'تيجو ٨'],
                ['Arrizo 5', 'أريزو ٥'],
            ]],
            ['MG', 'إم جي', [
                ['MG5', 'إم جي٥'], ['MG6', 'إم جي٦'], ['RX5', 'آر إكس٥'],
                ['ZS', 'زد إس'], ['HS', 'إتش إس'],
            ]],
            ['Changan', 'شانجان', [
                ['CS35', 'سي إس٣٥'], ['CS75', 'سي إس٧٥'], ['Eado', 'إيدو'],
            ]],
            ['Geely', 'جيلي', [
                ['Emgrand', 'إمجراند'], ['Coolray', 'كولراي'], ['Okavango', 'أوكافانجو'],
            ]],
            ['Porsche', 'بورش', [
                ['Cayenne', 'كايين'], ['Macan', 'ماكان'], ['Panamera', 'باناميرا'],
                ['911', '٩١١'],
            ]],
            ['Cadillac', 'كاديلاك', [
                ['Escalade', 'إسكاليد'], ['XT5', 'إكس تي٥'], ['CT5', 'سي تي٥'],
            ]],
            ['Dodge', 'دودج', [
                ['Charger', 'تشارجر'], ['Durango', 'دورانجو'], ['Challenger', 'تشالنجر'],
            ]],
            ['Tesla', 'تسلا', [
                ['Model 3', 'موديل ٣'], ['Model Y', 'موديل واي'],
                ['Model S', 'موديل إس'], ['Model X', 'موديل إكس'],
            ]],
        ];
    }
}
