<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Lga;
use App\Models\Ward;

class AdamawaLgaWardSeeder extends Seeder
{
    public function run(): void
    {
        $adamawaData = [
            'Demsa' => ['Bille', 'Bororong', 'Demsa', 'Dilli', 'Dong', 'Dwawam', 'Gwamba', 'Kpasham', 'Mbula Kuli', 'Nassarawo Demsa'],
            'Fufore' => ['Beti', 'Daware', 'Farang', 'Fufore', 'Gurin', 'Karlahi', 'Mayo Ine', 'Pariya', 'Ribadu', 'Uki Tuki', 'Yadiman'],
            'Ganye' => ['Bakari Guso', 'Gamadio', 'Ganye I', 'Ganye II', 'Gurum', 'Jaggu', 'Sugu', 'Timdore', 'Yebbi'],
            'Girei' => ['Dakri', 'Damare', 'Girei I', 'Girei II', 'Gereng', 'Jera Bakari', 'Jera Bonyo', 'Modire/Vunoklang', 'Tambo', 'Wuro Dole'],
            'Gombi' => ['Boga/Dingai', 'Duwa', 'Ga\'anda', 'Garkida', 'Gombi North', 'Gombi South', 'Guyaku', 'Tawa', 'Yang'],
            'Guyuk' => ['Banjiram', 'Bobini', 'Bodeno', 'Chikila', 'Dumna', 'Guyuk', 'Kola', 'Lokoro', 'Purokayo'],
            'Hong' => ['Bangshika', 'Daksiri', 'Garaha', 'Gaya', 'Hildi', 'Hong', 'Husere Zum', 'Kwarhi', 'Mayo Lope', 'Shangui', 'Thilbang', 'Uba'],
            'Jada' => ['Danaba', 'Jada I', 'Jada II', 'Koma I', 'Koma II', 'Leko', 'Mapeo', 'Mayokalaye', 'Mbulo', 'Nyibango'],
            'Lamurde' => ['Dubwangun', 'Gyawana', 'Lafiya', 'Lamurde', 'Mgbede', 'Ngbakowo', 'Rigange', 'Suwa', 'Waduku', 'Zekun'],
            'Madagali' => ['Babel', 'Duhu/Shuwa', 'Gulak', 'Hyambula', 'Krichinga', 'Madagali', 'Pallam', 'Shelmi/Sukur/Vapura', 'Wagga', 'Wula'],
            'Maiha' => ['Belel', 'Humbutudi', 'Konkol', 'Maiha Gari', 'Manjekin', 'Mayonguli', 'Pakka', 'Sorau', 'Tambilijigvel'],
            'Mayo-Belwa' => ['Binyeri', 'Chukkol', 'Gorobi', 'Gengle', 'Mayo Farang', 'Mayo-Belwa', 'Ndikong', 'Ribadu', 'Tola', 'Yoffo'],
            'Michika' => ['Bazza Margi', 'Bazza Michika', 'Jigalambu', 'Madzi', 'Michika I', 'Michika II', 'Minkisi/Wuro Ngiki', 'Moda/Dlaka', 'Munkavic/Sina/Buda', 'Obah/Boka', 'Sukumu/Zah', 'Thumbere'],
            'Mubi North' => ['Bahuli', 'Betso', 'Digil', 'Kolere', 'Lokuwa', 'Mayo Bani', 'Mijilu', 'Muchalla', 'Sabon Layi', 'Vimtim', 'Yelwa'],
            'Mubi South' => ['Dirbishi/Gaya', 'Duvu/Chaba', 'Gella', 'Gude', 'Lamurde', 'Mujilu', 'Muvur', 'Nassarawo'],
            'Numan' => ['Bare', 'Bolki', 'Gamadio', 'Imburu', 'Kodomti', 'Numan I', 'Numan II', 'Numan III', 'Sabon Pegi', 'Vulpi'],
            'Shelleng' => ['Bakta', 'Bodwai', 'Gundo', 'Gwabun', 'Jumbul', 'Ketembere', 'Kiri', 'Libbo', 'Shelleng', 'Tallum'],
            'Song' => ['Dirma', 'Dumne', 'Fotta', 'Gudu Mboi', 'Kilkba', 'Sigire', 'Song', 'Suktu', 'Zumo'],
            'Toungo' => ['Dawo I', 'Dawo II', 'Kiri I', 'Kiri II', 'Toungo I', 'Toungo II'],
            'Yola North' => ['Alkalawa', 'Ajiya', 'Doubeli', 'Gwadabawa', 'Jambutu', 'Limawa', 'Luggere', 'Karewa', 'Nasarawo', 'Rumde', 'Yelwa'],
            'Yola South' => ['Adarawo', 'Bako', 'Bole Yolde Pate', 'Makama A', 'Makama B', 'Mbamba', 'Mbamoi', 'Ngurore', 'Toungo', 'Yolde Kohi'],
        ];

        foreach ($adamawaData as $lgaName => $wards) {
            $lga = Lga::firstOrCreate(['name' => $lgaName]);
            foreach ($wards as $wardName) {
                Ward::firstOrCreate([
                    'lga_id' => $lga->id,
                    'name' => $wardName
                ]);
            }
        }
    }
}
