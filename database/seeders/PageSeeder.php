<?php
// database/seeders/PageSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            ['title'=>'Về chúng tôi', 'slug'=>'ve-chung-toi', 'status'=>'published', 'content'=>'<p>Nội dung giới thiệu.</p>'],
            ['title'=>'Liên hệ', 'slug'=>'lien-he', 'status'=>'published', 'content'=>'<p>Hotline / địa chỉ / form.</p>'],
            ['title'=>'Chính sách bảo mật', 'slug'=>'chinh-sach-bao-mat', 'status'=>'published', 'content'=>'<p>Chính sách…</p>'],
        ];
        foreach ($pages as $p) {
            Page::updateOrCreate(['slug'=>$p['slug']], $p);
        }
    }
}
