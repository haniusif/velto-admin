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
                ['Land Cruiser Pickup', 'لاند كروزر بيك أب'], ['Prado', 'برادو'],
                ['Hilux', 'هايلكس'], ['Yaris', 'يارس'], ['RAV4', 'راف٤'],
                ['Fortuner', 'فورتشنر'], ['Avalon', 'أفالون'], ['Innova', 'إينوفا'],
                ['Rush', 'راش'], ['Highlander', 'هايلاندر'], ['Corolla Cross', 'كورولا كروس'],
                ['C-HR', 'سي إتش آر'], ['Previa', 'بريفيا'], ['Hiace', 'هايس'],
                ['Coaster', 'كوستر'], ['Raize', 'رايز'], ['Urban Cruiser', 'أوربان كروزر'],
                ['Crown', 'كراون'], ['Supra', 'سوبرا'],
            ]],
            ['Hyundai', 'هيونداي', [
                ['Elantra', 'إلنترا'], ['Sonata', 'سوناتا'], ['Accent', 'أكسنت'],
                ['Tucson', 'توسان'], ['Santa Fe', 'سنتافي'], ['Creta', 'كريتا'],
                ['Azera', 'أزيرا'], ['Palisade', 'باليسايد'], ['Staria', 'ستاريا'],
                ['Venue', 'فينيو'], ['H-1', 'إتش١'], ['Kona', 'كونا'],
                ['Ioniq 5', 'أيونيك ٥'], ['Ioniq 6', 'أيونيك ٦'], ['Grand i10', 'جراند آي١٠'],
                ['Bayon', 'بايون'], ['Genesis', 'جينيسيس'],
            ]],
            ['Kia', 'كيا', [
                ['Cerato', 'سيراتو'], ['Optima', 'أوبتيما'], ['K5', 'كي٥'],
                ['K8', 'كي٨'], ['Sportage', 'سبورتاج'], ['Sorento', 'سورنتو'],
                ['Seltos', 'سيلتوس'], ['Carnival', 'كرنفال'], ['Pegas', 'بيجاس'],
                ['Telluride', 'تيلورايد'], ['Rio', 'ريو'], ['Picanto', 'بيكانتو'],
                ['Sonet', 'سونيت'], ['Carens', 'كارينز'], ['EV6', 'إي في٦'],
                ['Niro', 'نيرو'],
            ]],
            ['Nissan', 'نيسان', [
                ['Sunny', 'صني'], ['Altima', 'ألتيما'], ['Maxima', 'ماكسيما'],
                ['Patrol', 'باترول'], ['Patrol Safari', 'باترول سفاري'],
                ['X-Trail', 'إكس-تريل'], ['Kicks', 'كيكس'], ['Pathfinder', 'باثفايندر'],
                ['Navara', 'نافارا'], ['Urvan', 'أورفان'], ['Sentra', 'سنترا'],
                ['Xterra', 'إكستيرا'], ['Armada', 'أرمادا'], ['Juke', 'جوك'],
                ['Micra', 'ميكرا'],
            ]],
            ['Chevrolet', 'شفروليه', [
                ['Malibu', 'ماليبو'], ['Impala', 'إمبالا'], ['Tahoe', 'تاهو'],
                ['Suburban', 'سوبربان'], ['Traverse', 'ترافيرس'], ['Captiva', 'كابتيفا'],
                ['Silverado', 'سلفرادو'], ['Groove', 'جروف'], ['Blazer', 'بليزر'],
                ['Trailblazer', 'تريل بليزر'], ['Equinox', 'إكوينوكس'],
                ['Camaro', 'كمارو'], ['Corvette', 'كورفيت'], ['Optra', 'أوبترا'],
            ]],
            ['Ford', 'فورد', [
                ['Taurus', 'تورس'], ['Explorer', 'إكسبلورر'], ['Expedition', 'إكسبيديشن'],
                ['F-150', 'إف-١٥٠'], ['Edge', 'إيدج'], ['Ranger', 'رينجر'],
                ['Territory', 'تيريتوري'], ['Mustang', 'موستانج'], ['Bronco', 'برونكو'],
                ['Escape', 'إسكيب'], ['EcoSport', 'إيكوسبورت'], ['Transit', 'ترانزيت'],
                ['Everest', 'إيفرست'],
            ]],
            ['Honda', 'هوندا', [
                ['Accord', 'أكورد'], ['Civic', 'سيفيك'], ['CR-V', 'سي آر-في'],
                ['Pilot', 'بايلوت'], ['City', 'سيتي'], ['HR-V', 'إتش آر-في'],
                ['Odyssey', 'أوديسي'], ['ZR-V', 'زد آر-في'],
            ]],
            ['Lexus', 'لكزس', [
                ['ES', 'إي إس'], ['LS', 'إل إس'], ['LX', 'إل إكس'],
                ['GX', 'جي إكس'], ['RX', 'آر إكس'], ['NX', 'إن إكس'],
                ['UX', 'يو إكس'], ['IS', 'آي إس'], ['LM', 'إل إم'],
                ['RZ', 'آر زد'], ['LC', 'إل سي'],
            ]],
            ['GMC', 'جي إم سي', [
                ['Yukon', 'يوكن'], ['Yukon XL', 'يوكن إكس إل'], ['Sierra', 'سييرا'],
                ['Acadia', 'أكاديا'], ['Terrain', 'تيرين'], ['Hummer EV', 'هامر إي في'],
                ['Canyon', 'كانيون'],
            ]],
            ['Mercedes-Benz', 'مرسيدس-بنز', [
                ['A-Class', 'الفئة A'], ['C-Class', 'الفئة C'], ['E-Class', 'الفئة E'],
                ['S-Class', 'الفئة S'], ['G-Class', 'الفئة G'], ['CLA', 'سي إل إيه'],
                ['CLS', 'سي إل إس'], ['GLA', 'جي إل إيه'], ['GLB', 'جي إل بي'],
                ['GLC', 'جي إل سي'], ['GLE', 'جي إل إي'], ['GLS', 'جي إل إس'],
                ['V-Class', 'الفئة V'], ['Sprinter', 'سبرينتر'], ['Maybach', 'مايباخ'],
            ]],
            ['BMW', 'بي إم دبليو', [
                ['1 Series', 'الفئة الأولى'], ['2 Series', 'الفئة الثانية'],
                ['3 Series', 'الفئة الثالثة'], ['4 Series', 'الفئة الرابعة'],
                ['5 Series', 'الفئة الخامسة'], ['6 Series', 'الفئة السادسة'],
                ['7 Series', 'الفئة السابعة'], ['8 Series', 'الفئة الثامنة'],
                ['X1', 'إكس١'], ['X3', 'إكس٣'], ['X4', 'إكس٤'], ['X5', 'إكس٥'],
                ['X6', 'إكس٦'], ['X7', 'إكس٧'], ['i4', 'آي٤'], ['iX', 'آي إكس'],
            ]],
            ['Mazda', 'مازدا', [
                ['Mazda 2', 'مازدا ٢'], ['Mazda 3', 'مازدا ٣'], ['Mazda 6', 'مازدا ٦'],
                ['CX-3', 'سي إكس-٣'], ['CX-5', 'سي إكس-٥'], ['CX-9', 'سي إكس-٩'],
                ['CX-30', 'سي إكس-٣٠'], ['CX-60', 'سي إكس-٦٠'], ['MX-5', 'إم إكس-٥'],
            ]],
            ['Mitsubishi', 'ميتسوبيشي', [
                ['Lancer', 'لانسر'], ['Attrage', 'أتراج'], ['Pajero', 'باجيرو'],
                ['Montero Sport', 'مونتيرو سبورت'], ['Outlander', 'أوتلاندر'],
                ['L200', 'إل٢٠٠'], ['Xpander', 'إكسباندر'], ['Eclipse Cross', 'إكليبس كروس'],
                ['ASX', 'إيه إس إكس'],
            ]],
            ['Land Rover', 'لاند روفر', [
                ['Range Rover', 'رنج روفر'], ['Range Rover Sport', 'رنج روفر سبورت'],
                ['Range Rover Vogue', 'رنج روفر فوج'], ['Range Rover Velar', 'رنج روفر فيلار'],
                ['Range Rover Evoque', 'رنج روفر إيفوك'], ['Defender', 'ديفندر'],
                ['Discovery', 'ديسكفري'], ['Discovery Sport', 'ديسكفري سبورت'],
            ]],
            ['Volkswagen', 'فولكس فاجن', [
                ['Golf', 'جولف'], ['Passat', 'باسات'], ['Jetta', 'جيتا'],
                ['Tiguan', 'تيجوان'], ['Teramont', 'تيرامونت'], ['Touareg', 'طوارق'],
                ['T-Roc', 'تي-روك'], ['Polo', 'بولو'],
            ]],
            ['Audi', 'أودي', [
                ['A3', 'إيه٣'], ['A4', 'إيه٤'], ['A5', 'إيه٥'], ['A6', 'إيه٦'],
                ['A7', 'إيه٧'], ['A8', 'إيه٨'], ['Q3', 'كيو٣'], ['Q5', 'كيو٥'],
                ['Q7', 'كيو٧'], ['Q8', 'كيو٨'], ['e-tron', 'إي-ترون'],
                ['TT', 'تي تي'], ['R8', 'آر٨'],
            ]],
            ['Infiniti', 'إنفينيتي', [
                ['Q50', 'كيو٥٠'], ['Q60', 'كيو٦٠'], ['QX50', 'كيو إكس٥٠'],
                ['QX55', 'كيو إكس٥٥'], ['QX60', 'كيو إكس٦٠'], ['QX80', 'كيو إكس٨٠'],
            ]],
            ['Jeep', 'جيب', [
                ['Wrangler', 'رانجلر'], ['Grand Cherokee', 'جراند شيروكي'],
                ['Cherokee', 'شيروكي'], ['Compass', 'كومباس'], ['Renegade', 'رينيجيد'],
                ['Gladiator', 'جلادييتر'], ['Grand Wagoneer', 'جراند واجونير'],
            ]],
            ['Suzuki', 'سوزوكي', [
                ['Baleno', 'بالينو'], ['Dzire', 'ديزاير'], ['Ertiga', 'إرتيجا'],
                ['Vitara', 'فيتارا'], ['Grand Vitara', 'جراند فيتارا'], ['Jimny', 'جيمني'],
                ['Swift', 'سويفت'], ['Ciaz', 'سياز'], ['Fronx', 'فرونكس'],
            ]],
            ['Isuzu', 'إيسوزو', [
                ['D-Max', 'دي-ماكس'], ['MU-X', 'إم يو-إكس'], ['NPR', 'إن بي آر'],
            ]],
            ['Renault', 'رينو', [
                ['Duster', 'داستر'], ['Koleos', 'كوليوس'], ['Megane', 'ميجان'],
                ['Symbol', 'سيمبول'], ['Captur', 'كابتور'], ['Talisman', 'تاليسمان'],
                ['Dokker', 'دوكر'],
            ]],
            ['Peugeot', 'بيجو', [
                ['301', '٣٠١'], ['208', '٢٠٨'], ['2008', '٢٠٠٨'],
                ['3008', '٣٠٠٨'], ['5008', '٥٠٠٨'], ['Landtrek', 'لاندتريك'],
            ]],
            ['Chery', 'شيري', [
                ['Tiggo 2', 'تيجو ٢'], ['Tiggo 4', 'تيجو ٤'], ['Tiggo 7', 'تيجو ٧'],
                ['Tiggo 8', 'تيجو ٨'], ['Arrizo 5', 'أريزو ٥'], ['Arrizo 6', 'أريزو ٦'],
            ]],
            ['MG', 'إم جي', [
                ['MG5', 'إم جي٥'], ['MG6', 'إم جي٦'], ['MG7', 'إم جي٧'],
                ['RX5', 'آر إكس٥'], ['RX8', 'آر إكس٨'], ['ZS', 'زد إس'],
                ['HS', 'إتش إس'], ['GT', 'جي تي'], ['Whale', 'ويل'],
            ]],
            ['Changan', 'شانجان', [
                ['CS35', 'سي إس٣٥'], ['CS55', 'سي إس٥٥'], ['CS75', 'سي إس٧٥'],
                ['CS85', 'سي إس٨٥'], ['CS95', 'سي إس٩٥'], ['Eado', 'إيدو'],
                ['Alsvin', 'ألسفين'], ['UNI-T', 'يوني-تي'],
            ]],
            ['Geely', 'جيلي', [
                ['Emgrand', 'إمجراند'], ['Coolray', 'كولراي'], ['Azkarra', 'أزكارا'],
                ['Okavango', 'أوكافانجو'], ['Tugella', 'توجيلا'], ['Monjaro', 'مونجارو'],
                ['Starray', 'ستاراي'],
            ]],
            ['Porsche', 'بورش', [
                ['Cayenne', 'كايين'], ['Macan', 'ماكان'], ['Panamera', 'باناميرا'],
                ['911', '٩١١'], ['Taycan', 'تايكان'], ['718', '٧١٨'],
            ]],
            ['Cadillac', 'كاديلاك', [
                ['Escalade', 'إسكاليد'], ['XT4', 'إكس تي٤'], ['XT5', 'إكس تي٥'],
                ['XT6', 'إكس تي٦'], ['CT4', 'سي تي٤'], ['CT5', 'سي تي٥'],
            ]],
            ['Dodge', 'دودج', [
                ['Charger', 'تشارجر'], ['Challenger', 'تشالنجر'], ['Durango', 'دورانجو'],
                ['Ram 1500', 'رام ١٥٠٠'],
            ]],
            ['Tesla', 'تسلا', [
                ['Model 3', 'موديل ٣'], ['Model Y', 'موديل واي'],
                ['Model S', 'موديل إس'], ['Model X', 'موديل إكس'],
            ]],
            ['GWM', 'جي دبليو إم', [
                ['Haval H6', 'هافال إتش٦'], ['Haval Jolion', 'هافال جوليون'],
                ['Haval H9', 'هافال إتش٩'], ['Poer', 'باور'], ['Tank 300', 'تانك ٣٠٠'],
                ['Tank 500', 'تانك ٥٠٠'],
            ]],
            ['Jetour', 'جيتور', [
                ['X70', 'إكس٧٠'], ['X90', 'إكس٩٠'], ['Dashing', 'داشينج'],
                ['T2', 'تي٢'],
            ]],
            ['Exeed', 'إكسيد', [
                ['TXL', 'تي إكس إل'], ['VX', 'في إكس'], ['LX', 'إل إكس'],
                ['RX', 'آر إكس'],
            ]],
            ['Hongqi', 'هونشي', [
                ['H5', 'إتش٥'], ['H9', 'إتش٩'], ['HS5', 'إتش إس٥'], ['E-HS9', 'إي إتش إس٩'],
            ]],
            ['Volvo', 'فولفو', [
                ['XC40', 'إكس سي٤٠'], ['XC60', 'إكس سي٦٠'], ['XC90', 'إكس سي٩٠'],
                ['S60', 'إس٦٠'], ['S90', 'إس٩٠'],
            ]],
            ['Subaru', 'سوبارو', [
                ['Impreza', 'إمبريزا'], ['Forester', 'فورستر'], ['Outback', 'أوت باك'],
                ['XV', 'إكس في'],
            ]],
            ['Jaguar', 'جاكوار', [
                ['XE', 'إكس إي'], ['XF', 'إكس إف'], ['F-Pace', 'إف-بيس'],
                ['E-Pace', 'إي-بيس'], ['I-Pace', 'آي-بيس'],
            ]],
            ['Lincoln', 'لينكولن', [
                ['Navigator', 'نافيجيتور'], ['Aviator', 'أفياتور'],
                ['Corsair', 'كورسير'], ['Nautilus', 'نوتيلوس'],
            ]],
            ['Chrysler', 'كرايسلر', [
                ['300C', '٣٠٠ سي'], ['Pacifica', 'باسيفيكا'],
            ]],
            ['Daihatsu', 'دايهاتسو', [
                ['Terios', 'تيريوس'], ['Sirion', 'سيريون'], ['Gran Max', 'جران ماكس'],
            ]],
            ['Bentley', 'بنتلي', [
                ['Bentayga', 'بنتايجا'], ['Continental GT', 'كونتيننتال جي تي'],
                ['Flying Spur', 'فلاينج سبير'],
            ]],
            ['Maserati', 'مازيراتي', [
                ['Levante', 'ليفانتي'], ['Ghibli', 'غيبلي'], ['Grecale', 'جريكالي'],
            ]],
        ];
    }
}
