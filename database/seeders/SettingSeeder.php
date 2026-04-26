<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Seed the default membership & receipt settings.
     */
    public function run(): void
    {
        $defaults = [
            // Member System
            ['key' => 'member_enabled', 'value' => 'true', 'type' => 'boolean', 'group' => 'member', 'label' => 'Aktifkan Member System', 'description' => 'Mengaktifkan/menonaktifkan seluruh fitur membership'],
            ['key' => 'auto_register_on_phone', 'value' => 'true', 'type' => 'boolean', 'group' => 'member', 'label' => 'Auto Register dari No. HP', 'description' => 'Otomatis daftarkan customer baru saat input no. HP di kasir'],

            // Point Earning
            ['key' => 'point_earn_amount', 'value' => '10000', 'type' => 'integer', 'group' => 'point', 'label' => 'Setiap Belanja (Rp)', 'description' => 'Setiap belanja Rp X akan mendapat poin'],
            ['key' => 'point_earn_value', 'value' => '1', 'type' => 'integer', 'group' => 'point', 'label' => 'Dapat Poin', 'description' => 'Jumlah poin yang didapat per kelipatan belanja'],
            ['key' => 'point_earn_channels', 'value' => '["offline"]', 'type' => 'json', 'group' => 'point', 'label' => 'Channel Berlaku', 'description' => 'Channel yang berlaku untuk earning poin'],

            // Point Redemption
            ['key' => 'point_redeem_value', 'value' => '100', 'type' => 'integer', 'group' => 'point', 'label' => 'Nilai 1 Poin (Rp)', 'description' => '1 poin = Rp X diskon'],
            ['key' => 'point_min_redeem', 'value' => '100', 'type' => 'integer', 'group' => 'point', 'label' => 'Minimum Redeem', 'description' => 'Minimum poin untuk redeem'],
            ['key' => 'point_expired_enabled', 'value' => 'true', 'type' => 'boolean', 'group' => 'point', 'label' => 'Aktifkan Expiry Poin', 'description' => 'Aktifkan masa berlaku poin'],
            ['key' => 'point_expired_months', 'value' => '12', 'type' => 'integer', 'group' => 'point', 'label' => 'Masa Berlaku (Bulan)', 'description' => 'Poin expired setelah X bulan'],

            // Member Discount
            ['key' => 'member_discount_enabled', 'value' => 'true', 'type' => 'boolean', 'group' => 'discount', 'label' => 'Aktifkan Diskon Member', 'description' => 'Diskon otomatis untuk member setiap belanja'],
            ['key' => 'member_discount_percent', 'value' => '5', 'type' => 'integer', 'group' => 'discount', 'label' => 'Persentase Diskon (%)', 'description' => 'Persentase diskon member (0-100)'],
            ['key' => 'member_discount_channels', 'value' => '["offline"]', 'type' => 'json', 'group' => 'discount', 'label' => 'Channel Berlaku', 'description' => 'Channel yang berlaku untuk diskon member'],
            ['key' => 'member_discount_min_purchase', 'value' => '0', 'type' => 'integer', 'group' => 'discount', 'label' => 'Minimum Belanja (Rp)', 'description' => 'Minimum belanja untuk dapat diskon member'],

            // Birthday Treat
            ['key' => 'birthday_enabled', 'value' => 'true', 'type' => 'boolean', 'group' => 'birthday', 'label' => 'Aktifkan Birthday Treat', 'description' => 'Diskon spesial di periode ulang tahun'],
            ['key' => 'birthday_discount_percent', 'value' => '20', 'type' => 'integer', 'group' => 'birthday', 'label' => 'Diskon Birthday (%)', 'description' => 'Persentase diskon ulang tahun'],
            ['key' => 'birthday_valid_days', 'value' => '7', 'type' => 'integer', 'group' => 'birthday', 'label' => 'Periode Berlaku (Hari)', 'description' => 'Hari sebelum & sesudah ulang tahun'],
            ['key' => 'birthday_max_per_year', 'value' => '1', 'type' => 'integer', 'group' => 'birthday', 'label' => 'Maks Per Tahun', 'description' => 'Maksimum penggunaan birthday discount per tahun'],

            // Receipt/Struk
            ['key' => 'receipt_show_logo', 'value' => 'true', 'type' => 'boolean', 'group' => 'receipt', 'label' => 'Tampilkan Logo', 'description' => 'Tampilkan logo toko di struk'],
            ['key' => 'receipt_show_member_info', 'value' => 'true', 'type' => 'boolean', 'group' => 'receipt', 'label' => 'Tampilkan Info Member', 'description' => 'Tampilkan section info member di struk'],
            ['key' => 'receipt_show_point_info', 'value' => 'true', 'type' => 'boolean', 'group' => 'receipt', 'label' => 'Tampilkan Info Poin', 'description' => 'Tampilkan section poin reward di struk'],
            ['key' => 'receipt_show_savings', 'value' => 'true', 'type' => 'boolean', 'group' => 'receipt', 'label' => 'Tampilkan Total Hemat', 'description' => 'Tampilkan total hemat hari ini di struk'],
            ['key' => 'receipt_show_promo_footer', 'value' => 'true', 'type' => 'boolean', 'group' => 'receipt', 'label' => 'Tampilkan Promo Footer', 'description' => 'Tampilkan promo di bagian bawah struk'],
            ['key' => 'receipt_header_text', 'value' => 'Dlatif Store', 'type' => 'string', 'group' => 'receipt', 'label' => 'Nama Toko', 'description' => 'Nama toko yang ditampilkan di header struk'],
            ['key' => 'receipt_address', 'value' => 'Buntit RT 27, Pare, Mondokan, Sragen', 'type' => 'string', 'group' => 'receipt', 'label' => 'Alamat Toko', 'description' => 'Alamat toko di header struk'],
            ['key' => 'receipt_phone', 'value' => '0822-3439-2553', 'type' => 'string', 'group' => 'receipt', 'label' => 'No. Telepon', 'description' => 'Nomor telepon toko'],
            ['key' => 'receipt_social_media', 'value' => '', 'type' => 'string', 'group' => 'receipt', 'label' => 'Social Media', 'description' => 'Instagram/TikTok handle'],
            ['key' => 'receipt_footer_text', 'value' => 'Terima kasih sudah belanja!', 'type' => 'string', 'group' => 'receipt', 'label' => 'Pesan Footer', 'description' => 'Pesan di bagian bawah struk'],
            ['key' => 'receipt_promo_text', 'value' => 'Daftar member untuk dapat diskon & poin reward!', 'type' => 'string', 'group' => 'receipt', 'label' => 'Teks Promo', 'description' => 'Pesan promo/marketing di struk'],
            ['key' => 'receipt_paper_size', 'value' => '80mm', 'type' => 'string', 'group' => 'receipt', 'label' => 'Ukuran Kertas', 'description' => 'Lebar struk: 58mm, 80mm, atau digital'],
        ];

        foreach ($defaults as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting,
            );
        }
    }
}
