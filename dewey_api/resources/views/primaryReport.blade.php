@php
    if (!function_exists('toKhmerNumber')) {
        function toKhmerNumber($number)
        {
            $khmer_numbers = [
                '0' => '០',
                '1' => '១',
                '2' => '២',
                '3' => '៣',
                '4' => '៤',
                '5' => '៥',
                '6' => '៦',
                '7' => '៧',
                '8' => '៨',
                '9' => '៩',
            ];
            return strtr($number, $khmer_numbers);
        }
    }
@endphp
<table width="2480px">
    <tr>
        <td colspan="{{ $type != 'ខែ' ? 45 : 24 }}" style="text-align: center; font-family: Khmer OS MOul;">
            ព្រះរាជាណាចក្រកម្ពុជា</td>
    </tr>
    <tr>
        <td colspan="{{ $type != 'ខែ' ? 45 : 24 }}" style="text-align: center; font-family: Khmer OS MOul;">ជាតិ សាសនា
            ព្រះមហាក្សត្រ</td>
    </tr>
    {{-- <tr colspan="{{ $type != 'ខែ' ? 45 : 24 }}"></tr> --}}
    <tr>
        <td colspan="{{ $type != 'ខែ' ? 5 : 1 }} "></td>

        <td colspan="{{ $type != 'ខែ' ? 40 : 24 }}"
            style="text-align: left; font-family: Khmer OS MouL; font-size: 10px;">
            <div style="display: flex; align-items: center; gap: 5px">

                {{-- <img src="https://app.disreportcard.com/assets/DIS-BFWF5DQ5.png" alt="Logo"
                    style="height: 16px; width: 10px; display: inline-block;"> --}}

                <span>សាលាចំណេះទូទៅអន្តរជាតិ ឌូវី</span>
            </div>
        </td>
    </tr>
    <tr>
        <td colspan="{{ $type != 'ខែ' ? 45 : 24 }}" style="text-align: center; font-family: Khmer OS MOul; ">
            តារាងស្រង់ពិន្ទុ​
            ថ្នាក់ទី<span style="color: #FFA500">
                {{ toKhmerNumber($info->first()->grade_name ?? '') }}
            </span></td>
    </tr>
    <tr>
        <td colspan="{{ $type != 'ខែ' ? 45 : 24 }}" style="text-align: center; font-family: Khmer OS MOul;">ប្រចាំ​



            <span>
                @if ($type != 'ឆ្នាំ' && $type == 'semester1')
                    ឆមាសទី១
                @endif
            </span>
            <span>
                @if ($type != 'ឆ្នាំ' && $type == 'semester2')
                    ឆមាសទី២
                @endif
            </span>
            <span>
                @if ($month)
                    {{ $month->first()->name_kh ?? '' }}
                @endif
            </span>
            <span>
                ឆ្នាំសិក្សា <span> {{ toKhmerNumber($info->first()->name ?? '') }}</span>
            </span>
        </td>
    </tr>

    @if ($type != 'ខែ' && $level == 6)
        <tr>
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 30px;height: 85px"
                rowspan="2">
                ល.រ
            </th>
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 200px; " rowspan="2">
                គោត្តនាម និងនាម
            </th>
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 30px;" rowspan="2">
                ភេទ
            </th>
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;" rowspan="2">
                ស្ដាប់
            </th>

            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;font-weight: bold;"
                rowspan="2">
                ចំ.ថា្នក់
            </th>

            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;" rowspan="2">
                និយាយ
            </th>

            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;font-weight: bold;"
                rowspan="2">
                ចំ.ថា្នក់

            </th>

            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;" rowspan="2">
                អំណាន
            </th>

            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;font-weight: bold;"
                rowspan="2">
                ចំ.ថា្នក់

            </th>


            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;" rowspan="2">
                សរសេរ
            </th>


            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;font-weight: bold;"
                rowspan="2">
                ចំ.ថា្នក់

            </th>


            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;" rowspan="2">
                តែងសេចក្ដី
            </th>


            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;font-weight: bold;"
                rowspan="2">
                ចំ.ថា្នក់

            </th>

            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;" rowspan="2">
                វេយ្យាករណ៍
            </th>


            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;font-weight: bold;"
                rowspan="2">
                ចំ.ថា្នក់

            </th>

            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;" rowspan="2">
                គណិត
            </th>


            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;font-weight: bold;"
                rowspan="2">
                ចំ.ថា្នក់

            </th>


            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;" rowspan="2">
                វិទ្យាសាស្រ្ត
            </th>


            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;font-weight: bold;"
                rowspan="2">
                ចំ.ថា្នក់

            </th>


            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;" rowspan="2">
                ភូមិ
            </th>

            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;font-weight: bold;"
                rowspan="2">
                ចំ.ថា្នក់

            </th>



            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;" rowspan="2">
                ប្រវត្តិ
            </th>

            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;font-weight: bold;"
                rowspan="2">
                ចំ.ថា្នក់

            </th>


            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;" rowspan="2">
                សីលធម៍
            </th>


            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;font-weight: bold;"
                rowspan="2">
                ចំ.ថា្នក់

            </th>


            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;" rowspan="2">
                គំនូរ
            </th>


            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;font-weight: bold;"
                rowspan="2">
                ចំ.ថា្នក់

            </th>


            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;" rowspan="2">
                អក្សរផ្ចង់
            </th>


            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;font-weight: bold;"
                rowspan="2">
                ចំ.ថា្នក់

            </th>


            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;" rowspan="2">
                អប់រំកាយ
            </th>


            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;font-weight: bold;"
                rowspan="2">
                ចំ.ថា្នក់

            </th>


            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;" rowspan="2">
                កិច្ចការផ្ទះ
            </th>


            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;font-weight: bold;"
                rowspan="2">
                ចំ.ថា្នក់

            </th>


            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;" rowspan="2">
                អនាម័យ
            </th>

            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;font-weight: bold;"
                rowspan="2">
                ចំ.ថា្នក់

            </th>


            <th style="text-align: center; font-family: Khmer OS Siemreap;font-size: 9px;width: 35px;font-weight: bold;"
                rowspan="2">
                STEAM
            </th>


            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;font-weight: bold;"
                rowspan="2">
                ចំ.ថា្នក់

            </th>

            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 45px;" rowspan="2">
                ភាសារខ្មែរ
            </th>
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 45px;" rowspan="2">
                មធ្យមខ្មែរ
            </th>
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;font-weight: bold;"
                rowspan="2">
                ចំ.ថា្នក់
            </th>

            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 50px;" rowspan="2">
                សិក្សាសង្គម
            </th>
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 45px;" rowspan="2">
                មធ្យមសង្គម
            </th>
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;font-weight: bold;"
                rowspan="2">
                ចំ.ថា្នក់
            </th>

            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 55px;" rowspan="2">
                ពិន្ទុឆមាសសរុប
            </th>
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 55px;" rowspan="2">
                មធ្យមឆមាស
            </th>
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;" rowspan="2">
                ចំ.ឆមាស
            </th>

            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;" colspan="3">
                លទ្ធផលឆមាសទី១
            </th>

            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 45px;" rowspan="2">
                ខែមធ្យមភាគ
            </th>
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;" rowspan="2">
                ចំ.ខែឆមាស
            </th>

            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 45px;" rowspan="2">
                មធ្យមប្រចាំឆមាស
            </th>
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;" rowspan="2">
                ចំ.ប្រចាំឆមាស
            </th>
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 55px;" rowspan="2">
                និទ្ទេសន៍
            </th>
        </tr>
        <tr>
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 65px;">
                ពិន្ទុសរុប
            </th>
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 85px;">មធ្យមភាគ</th>
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 55px;">ចំ.ថ្នាក់</th>
        </tr>



        <tbody style="text-align: center; border: 1px;">
            @foreach ($data as $idx => $item)
                <tr>

                    <td style="text-align: center">{{ $idx + 1 }}</td>
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px">{{ $item['kh_name'] }}</td>
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        @if ($item['gender'] == 1 || $item['gender'] == 'M')
                            <p>ប</p>
                        @else
                            <p>ស</p>
                        @endif
                    </td>

                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['listent'] }}
                    </td>
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['rankListent'] }}
                    </td>

                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['speaking'] }}
                    </td>
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['rankSpeaking'] }}
                    </td>

                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['reading'] }}
                    </td>
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['rankReading'] }}
                    </td>

                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['writing'] }}
                    </td>
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['rankWriting'] }}
                    </td>


                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['essay'] }}
                    </td>
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['rankEssay'] }}
                    </td>

                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['grammar'] }}
                    </td>
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['rankGrammar'] }}
                    </td>

                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['math'] }}
                    </td>
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['rankMath'] }}
                    </td>

                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['chemistry'] }}
                    </td>
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['rankChemistry'] }}
                    </td>

                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['physical'] }}
                    </td>
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['rankPhysical'] }}
                    </td>

                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['history'] }}
                    </td>
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['rankHistory'] }}
                    </td>

                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['morality'] }}
                    </td>
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['rankMorality'] }}
                    </td>

                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['art'] }}
                    </td>
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['rankArt'] }}
                    </td>

                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['word'] }}
                    </td>
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['rankWord'] }}
                    </td>

                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['pe'] }}
                    </td>
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['rankPe'] }}
                    </td>

                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['homework'] }}
                    </td>
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['rankHomework'] }}
                    </td>


                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['healthy'] }}
                    </td>
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['rankHealthy'] }}
                    </td>


                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['steam'] }}
                    </td>
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['rankSteam'] }}
                    </td>

                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['khmer'] }}
                    </td>
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['averageKhmer'] }}
                    </td>
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['rankKhmer'] }}
                    </td>

                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['social_scient'] }}
                    </td>
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['averageSocial'] }}
                    </td>
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['rankSocial'] }}
                    </td>

                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['total_score'] }}
                    </td>
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['average_month_semester'] }}
                    </td>
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['rank_month_semester'] }}
                    </td>

                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['total_score_kcms'] }}
                    </td>
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['average_kcms'] }}
                    </td>
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['rankKcms'] }}
                    </td>

                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['average_3_month'] }}
                    </td>
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['rank_3_month'] }}
                    </td>

                    @if ($type == 'semester1')
                        <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                            {{ $item['average_semester1'] }}
                        </td>
                    @endif
                    @if ($type == 'semester2')
                        <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                            {{ $item['average_semester2'] }}
                        </td>
                    @endif

                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['rank'] }}
                    </td>
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['grade'] }}
                    </td>

                </tr>
            @endforeach
        </tbody>
    @else
        <tr>
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 30px;">
                ល.រ
            </th>
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 200px;">
                គោត្តនាម និងនាម
            </th>
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 30px;">
                ភេទ
            </th>
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
                ស្ដាប់
            </th>

            @if ($type != 'ខែ')
                <th
                    style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;font-weight: bold;">
                    ចំ.ថា្នក់

                </th>
            @endif

            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
                និយាយ
            </th>

            @if ($type != 'ខែ')
                <th
                    style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;font-weight: bold;">
                    ចំ.ថា្នក់

                </th>
            @endif

            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
                អំណាន
            </th>

            @if ($type != 'ខែ')
                <th
                    style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;font-weight: bold;">
                    ចំ.ថា្នក់

                </th>
            @endif

            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
                សរសេរ
            </th>

            @if ($type != 'ខែ')
                <th
                    style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;font-weight: bold;">
                    ចំ.ថា្នក់

                </th>
            @endif

            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
                តែងសេចក្ដី
            </th>

            @if ($type != 'ខែ')
                <th
                    style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;font-weight: bold;">
                    ចំ.ថា្នក់

                </th>
            @endif
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
                វេយ្យាករណ៍
            </th>

            @if ($type != 'ខែ')
                <th
                    style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;font-weight: bold;">
                    ចំ.ថា្នក់

                </th>
            @endif
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
                គណិត
            </th>

            @if ($type != 'ខែ')
                <th
                    style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;font-weight: bold;">
                    ចំ.ថា្នក់

                </th>
            @endif

            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
                វិទ្យាសាស្រ្ត
            </th>

            @if ($type != 'ខែ')
                <th
                    style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;font-weight: bold;">
                    ចំ.ថា្នក់

                </th>
            @endif

            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
                ភូមិ
            </th>

            @if ($type != 'ខែ')
                <th
                    style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;font-weight: bold;">
                    ចំ.ថា្នក់

                </th>
            @endif

            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
                ប្រវត្តិ
            </th>

            @if ($type != 'ខែ')
                <th
                    style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;font-weight: bold;">
                    ចំ.ថា្នក់

                </th>
            @endif

            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
                សីលធម៍
            </th>

            @if ($type != 'ខែ')
                <th
                    style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;font-weight: bold;">
                    ចំ.ថា្នក់

                </th>
            @endif

            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
                គំនូរ
            </th>

            @if ($type != 'ខែ')
                <th
                    style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;font-weight: bold;">
                    ចំ.ថា្នក់

                </th>
            @endif

            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
                អក្សរផ្ចង់
            </th>

            @if ($type != 'ខែ')
                <th
                    style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;font-weight: bold;">
                    ចំ.ថា្នក់

                </th>
            @endif

            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
                អប់រំកាយ
            </th>

            @if ($type != 'ខែ')
                <th
                    style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;font-weight: bold;">
                    ចំ.ថា្នក់

                </th>
            @endif

            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
                កិច្ចការផ្ទះ
            </th>

            @if ($type != 'ខែ')
                <th
                    style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;font-weight: bold;">
                    ចំ.ថា្នក់

                </th>
            @endif

            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
                អនាម័យ
            </th>

            @if ($type != 'ខែ')
                <th
                    style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;font-weight: bold;">
                    ចំ.ថា្នក់

                </th>
            @endif

            <th
                style="text-align: center; font-family: Khmer OS Siemreap;font-size: 9px;width: 35px;font-weight: bold;">
                STEAM
            </th>

            @if ($type != 'ខែ')
                <th
                    style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;font-weight: bold;">
                    ចំ.ថា្នក់

                </th>
            @endif

            @if ($type == 'ខែ')
                <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 55px;">
                    ពិន្ទុសរុប
                </th>
                <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 55px;">
                    មធ្យមភាគ
                </th>
                <th
                    style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;font-weight: bold;">
                    ចំ.ថា្នក់
                </th>
            @endif

            @if ($type != 'ខែ')
                <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 55px;">
                    ពិន្ទុឆមាសសរុប
                </th>
                <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 55px;">
                    មធ្យមភាគឆមាស
                </th>
                <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 55px;">
                    ចំ.ឆមាស
                </th>
                <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 55px;">
                    មធ្យមភាគខែឆមាស
                </th>
                <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 55px;">
                    ចំ.ខែឆមាស
                </th>
                <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 55px;">
                    មធ្យមភាគពិន្ទុឆមាស
                </th>
                <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 55px;">
                    ចំ.ប្រចាំឆមាស
                </th>
            @endif

            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 65px;">
                និទ្ទេសន៍
            </th>
            {{-- <th>{{ $type }}</th> --}}

        </tr>


        <tbody style="text-align: center; border: 1px;">
            @foreach ($data as $idx => $item)
                <tr>
                    <td style="text-align: center">{{ $idx + 1 }}</td>
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px">{{ $item['kh_name'] }}</td>
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        @if ($item['gender'] == 1 || $item['gender'] == 'M')
                            <p>ប</p>
                        @else
                            <p>ស</p>
                        @endif
                    </td>
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['listent'] }}
                    </td>

                    @if ($type != 'ខែ')
                        <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                            {{ $item['rankListent'] }}
                        </td>
                    @endif
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['speaking'] }}
                    </td>

                    @if ($type != 'ខែ')
                        <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                            {{ $item['rankSpeaking'] }}
                        </td>
                    @endif

                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['reading'] }}
                    </td>
                    @if ($type != 'ខែ')
                        <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                            {{ $item['rankReading'] }}
                        </td>
                    @endif

                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['writing'] }}
                    </td>
                    @if ($type != 'ខែ')
                        <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                            {{ $item['rankWriting'] }}
                        </td>
                    @endif

                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['essay'] }}
                    </td>
                    @if ($type != 'ខែ')
                        <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                            {{ $item['rankEssay'] }}
                        </td>
                    @endif

                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['grammar'] }}
                    </td>
                    @if ($type != 'ខែ')
                        <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                            {{ $item['rankGrammar'] }}
                        </td>
                    @endif

                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['math'] }}
                    </td>
                    @if ($type != 'ខែ')
                        <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                            {{ $item['rankMath'] }}
                        </td>
                    @endif

                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['chemistry'] }}
                    </td>
                    @if ($type != 'ខែ')
                        <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                            {{ $item['rankChemistry'] }}
                        </td>
                    @endif

                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['physical'] }}
                    </td>
                    @if ($type != 'ខែ')
                        <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                            {{ $item['rankPhysical'] }}
                        </td>
                    @endif

                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['history'] }}
                    </td>
                    @if ($type != 'ខែ')
                        <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                            {{ $item['rankHistory'] }}
                        </td>
                    @endif

                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['morality'] }}
                    </td>
                    @if ($type != 'ខែ')
                        <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                            {{ $item['rankMorality'] }}
                        </td>
                    @endif

                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['art'] }}
                    </td>
                    @if ($type != 'ខែ')
                        <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                            {{ $item['rankArt'] }}
                        </td>
                    @endif

                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['word'] }}
                    </td>
                    @if ($type != 'ខែ')
                        <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                            {{ $item['rankWord'] }}
                        </td>
                    @endif

                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['pe'] }}
                    </td>
                    @if ($type != 'ខែ')
                        <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                            {{ $item['rankPe'] }}
                        </td>
                    @endif

                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['homework'] }}
                    </td>
                    @if ($type != 'ខែ')
                        <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                            {{ $item['rankHomework'] }}
                        </td>
                    @endif

                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['healthy'] }}
                    </td>
                    @if ($type != 'ខែ')
                        <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                            {{ $item['rankHealthy'] }}
                        </td>
                    @endif

                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['steam'] }}
                    </td>
                    @if ($type != 'ខែ')
                        <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                            {{ $item['rankSteam'] }}
                        </td>
                    @endif

                    @if ($type == 'ខែ')
                        <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                            {{ $item['total_score'] }}
                        </td>
                    @endif
                    @if ($type == 'ខែ')
                        <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                            {{ $item['avg'] }}
                        </td>
                    @endif
                    @if ($type == 'ខែ')
                        <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                            {{ $item['rank'] }}
                        </td>
                    @endif

                    @if ($type != 'ខែ')
                        <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                            {{ $item['total_score'] }}
                        </td>
                        <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                            {{ $item['average_month_semester'] }}
                        </td>
                        <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                            {{ $item['rank_month_semester'] }}
                        </td>
                        <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                            {{ $item['average_3_month'] }}
                        </td>
                        <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                            {{ $item['rank_3_month'] }}
                        </td>

                        @if ($type == 'semester1')
                            <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                                {{ $item['average_semester1'] }}
                            </td>
                        @endif
                        @if ($type == 'semester2')
                            <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                                {{ $item['average_semester2'] }}
                            </td>
                        @endif

                        <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                            {{ $item['rank'] }}
                        </td>
                    @endif
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['grade'] }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    @endif

    <tr>
        <td></td>
        <td colspan="3" style="font-family: Khmer OS Siemreap;font-size: 10px ">
            សិស្សសរុបមាន​ ៖ {{ $allStudent }}នាក់
        </td>
    </tr>
    <tr>
        <td></td>
        <td colspan="3" style="font-family: Khmer OS Siemreap;font-size: 10px ">
            សិស្សស្រីមាន ៖ {{ $student_female }}នាក់
        </td>
        <td colspan="{{ $type != 'ខែ' ? 35 : 12 }} ">
        </td>
        <td colspan="6" style="font-family: Khmer OS Siemreap;font-size: 10px;text-align: center">
            បាត់ដំបង, ថ្ងៃ.......... ទី...... ខែ..........ឆ្នាំ {{ toKhmerNumber(date('Y')) }}
        </td>
    </tr>
    <tr>
        <td></td>
        <td colspan="3" style="font-family: Khmer OS Siemreap;font-size: 10px;text-align: center">
            បាត់ដំបង, ថ្ងៃ.......... ទី...... ខែ..........ឆ្នាំ {{ toKhmerNumber(date('Y')) }}
        </td>
        <td colspan="{{ $type != 'ខែ' ? 35 : 12 }} ">
        </td>
        <td colspan="6" style="font-family: Khmer OS Siemreap;font-size: 10px;text-align: center">
            គ្រូបន្ទុកថ្នាក់
        </td>
    </tr>
    <tr>
        <td></td>
        <td colspan="3" style="font-family: Khmer OS Siemreap;font-size: 10px;text-align: center">
            បានឃើញ នឹងឯកភាព
        </td>
    </tr>
    <tr>
        <td></td>
        <td colspan="3" style="font-family: Khmer OS MOul;font-size: 9px;text-align: center">
            នាយកសាលា
        </td>
    </tr>


</table>
