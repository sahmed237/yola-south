<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Lga;
use App\Models\Ward;

class TarabaLgaWardSeeder extends Seeder
{
    public function run(): void
    {
        $tarabaData = [
            'Ardo Kola' => ['Alim Gora', 'Ardo Kola', 'Iware', 'Jauro Yinu', 'Lamido Borno', 'Mayo Ranewo', 'Sarkin Dutse', 'Sunkani', 'Tau', 'Zongon Kombi'],
            'Bali' => ['Badakoshi', 'Bali A', 'Bali B', 'Gang Dole', 'Gang Mata', 'Ganglari', 'Gangtiba', 'Kaigama', 'Maihula', 'Suntai', 'Takalafiya'],
            'Donga' => ['Akate', 'Asibiti', 'Bikadarko', 'Fada', 'Gayama', 'Gindin Dutse', 'Gyatta Aure', 'Mararraba', 'Nyita', 'Suntai'],
            'Gashaka' => ['Galumjina', 'Gangumi', 'Garbabi', 'Gashaka', 'Gayam', 'Jamtari', 'Mai-Idanu', 'Mayo Selbe', 'Serti A', 'Serti B'],
            'Gassol' => ['Gassol', 'Gunduma', 'Mutum Biyu I', 'Mutum Biyu II', 'Nam Nai', 'Sabon Gida', 'Sarkin Shira', 'Sendirde', 'Tutare', 'Wurojam', 'Wuryo', 'Yarima'],
            'Ibi' => ['Dampar I', 'Dampar II', 'Dampar III', 'Ibi Nwonyo I', 'Ibi Nwonyo II', 'Ibi Rimi Uku I', 'Ibi Rimi Uku II', 'Sarkin Kudu I', 'Sarkin Kudu II', 'Sarkin Kudu III'],
            'Jalingo' => ['Abbare Yelwa', 'Barade', 'Kachalla Sembe', 'Kona', 'Majidadi', 'Mayo Goi', 'Sarkin Dawaki', 'Sintali', 'Turaki A', 'Turaki B'],
            'Karim Lamido' => ['Amar', 'Andamin', 'Bachama', 'Bikwin', 'Darofai', 'Didango', 'Jen Ardido', 'Jen Kaigama', 'Karim A', 'Karim B', 'Kwanchi'],
            'Kurmi' => ['Abong', 'Akwento/Boko', 'Ashuku/Eneme', 'Baissa', 'Bente/Galea', 'Bissaula', 'Didan', 'Ndaforo/Geanda', 'Njuwande', 'Nyido/Tosso'],
            'Lau' => ['Abbare I', 'Abbere II', 'Donadda', 'Garin Dogo', 'Garin Magaji', 'Jimlari', 'Kunini', 'Lau I', 'Lau II', 'Mayo Lope'],
            'Sardauna' => ['Gembu A', 'Gembu B', 'Kabri', 'Kakara', 'Magu', 'Mayo-Ndaga', 'Mbamnga', 'Ndum-Yaji', 'Nguroje', 'Titong', 'Warwar'],
            'Takum' => ['Bete', 'Bikashibila', 'Chanchanji', 'Dutse', 'Fete', 'Gahweton', 'Manya', 'Rogo', 'Shibong', 'Tikari', 'Yukuben'],
            'Ussa' => ['Bika', 'Fikyu', 'Jenuwa', 'Kpambo', 'Kpambo Puri', 'Kwambai', 'Kwesati', 'Lissam I', 'Lissam II', 'Lumbu', 'Rufu'],
            'Wukari' => ['Akwana', 'Avyi', 'Bantaje', 'Chonku', 'Hospital', 'Jibu', 'Kente', 'Puje', 'Rafin Kada', 'Tsokundi'],
            'Yorro' => ['Bikassa I', 'Bikassa II', 'Nyaja I', 'Nyaja II', 'Pantisawa I', 'Pantisawa II', 'Pupule I', 'Pupule II', 'Pupule III', 'Sumbu I', 'Sumbu II'],
            'Zing' => ['Bitako', 'Bubong', 'Dinding', 'Lamma', 'Monkin A', 'Monkin B', 'Yakoko', 'Zing A I', 'Zing A II', 'Zing B'],
        ];

        foreach ($tarabaData as $lgaName => $wards) {
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
