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

<table width="2480">
    <tr>
        <td colspan="{{ $type != 'ខែ' ? 40 : 20 }}" style="text-align: center; font-family: Khmer OS MOul;">
            ព្រះរាជាណាចក្រកម្ពុជា</td>
    </tr>
    <tr>
        <td colspan="{{ $type != 'ខែ' ? 40 : 20 }}" style="text-align: center; font-family: Khmer OS MOul;">ជាតិ សាសនា
            ព្រះមហាក្សត្រ</td>
    </tr>

    <tr>
        <td colspan="{{ $type != 'ខែ' ? 5 : 1 }} "></td>

        <td colspan="{{ $type != 'ខែ' ? 35 : 20 }}"
            style="text-align: left; font-family: Khmer OS MouL; font-size: 10px;">
            <div style="display: flex; align-items: center; gap: 5px">

                {{-- <img src="https://app.disreportcard.com/assets/DIS-BFWF5DQ5.png" alt="Logo"
                    style="height: 16px; width: 10px; display: inline-block;"> --}}

                <span>សាលាចំណេះទូទៅអន្តរជាតិ ឌូវី</span>
            </div>
        </td>
    </tr>

    <tr>
        <td colspan="{{ $type != 'ខែ' ? 40 : 20 }}" style="text-align: center; font-family: Khmer OS MOul; ">
            តារាងស្រង់ពិន្ទុ​
            ថ្នាក់ទី<span style="color: #FFA500">
                {{ toKhmerNumber($info->first()->grade_name ?? '') }}
            </span></td>
    </tr>
    <tr>
        <td colspan="{{ $type != 'ខែ' ? 40 : 20 }}" style="text-align: center; font-family: Khmer OS MOul;">ប្រចាំ​



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

    <tr>
        <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 30px;height: 85px">
            ល.រ
        </th>
        <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 200px; ">
            គោត្តនាម និងនាម
        </th>
        <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 30px;">
            ភេទ
        </th>
        <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
            ភាសារខ្មែរ
        </th>

        @if ($type != 'ខែ')
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
                ចំ.ថ្នាក់
            </th>
        @endif

        <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
            សីលធម៍
        </th>

        @if ($type != 'ខែ')
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
                ចំ.ថ្នាក់
            </th>
        @endif

        <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
            ប្រវត្តិវិទ្យា
        </th>

        @if ($type != 'ខែ')
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
                ចំ.ថ្នាក់
            </th>
        @endif

        <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
            ភូមិវិទ្យា
        </th>

        @if ($type != 'ខែ')
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
                ចំ.ថ្នាក់
            </th>
        @endif

        <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
            គណិតវិទ្យា
        </th>

        @if ($type != 'ខែ')
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
                ចំ.ថ្នាក់
            </th>
        @endif

        <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
            រូបវិទ្យា
        </th>

        @if ($type != 'ខែ')
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
                ចំ.ថ្នាក់
            </th>
        @endif

        <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
            គីមីវិទ្យា
        </th>

        @if ($type != 'ខែ')
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
                ចំ.ថ្នាក់
            </th>
        @endif

        <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
            ជីវវិទ្យា
        </th>

        @if ($type != 'ខែ')
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
                ចំ.ថ្នាក់
            </th>
        @endif

        <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
            ផែនដី
        </th>

        @if ($type != 'ខែ')
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
                ចំ.ថ្នាក់
            </th>
        @endif

        <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
            កីឡា
        </th>

        @if ($type != 'ខែ')
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
                ចំ.ថ្នាក់
            </th>
        @endif

        <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
            អង់គ្លេស
        </th>

        @if ($type != 'ខែ')
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
                ចំ.ថ្នាក់
            </th>
        @endif

        <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
            កុំព្យូទ័រ
        </th>

        @if ($type != 'ខែ')
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
                ចំ.ថ្នាក់
            </th>
        @endif

        @if ($type == 'ខែ' || $type == 'month')
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
                ពិន្ទុសរុប
            </th>
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 45px;">
                មធ្យមភាគ
            </th>
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
                ចំ.ថ្នាក់
            </th>
        @endif

        @if ($type != 'ខែ')
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
                ពិន្ទុឆមាស
            </th>
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
                ម.ឆមាស
            </th>
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
                ចំ.ឆមាស
            </th>
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
                ម.ខែឆមាស
            </th>
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
                ចំ.ខែឆមាស
            </th>
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
                ម.ពិន្ទុឆមាស
            </th>
            <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 35px;">
                ចំ.ប្រចាំឆមាស
            </th>
        @endif
        <th style="text-align: center; font-family: Khmer OS MOul;font-size: 9px;width: 55px;">
            និទ្ទេសន៍
        </th>
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
                <td style="font-family: Khmer OS Siemreap;font-size: 10px">{{ $item['khmer'] }}</td>

                @if ($type != 'ខែ')
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['rankKhmer'] }}
                    </td>
                @endif

                <td style="font-family: Khmer OS Siemreap;font-size: 10px">{{ $item['morality'] }}</td>

                @if ($type != 'ខែ')
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['rankMorality'] }}
                    </td>
                @endif

                <td style="font-family: Khmer OS Siemreap;font-size: 10px">{{ $item['history'] }}</td>

                @if ($type != 'ខែ')
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['rankHistory'] }}
                    </td>
                @endif

                <td style="font-family: Khmer OS Siemreap;font-size: 10px">{{ $item['geography'] }}</td>

                @if ($type != 'ខែ')
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['rankGeography'] }}
                    </td>
                @endif

                <td style="font-family: Khmer OS Siemreap;font-size: 10px">{{ $item['math'] }}</td>

                @if ($type != 'ខែ')
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['rankMath'] }}
                    </td>
                @endif

                <td style="font-family: Khmer OS Siemreap;font-size: 10px">{{ $item['physical'] }}</td>

                @if ($type != 'ខែ')
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['rankPhysic'] }}
                    </td>
                @endif

                <td style="font-family: Khmer OS Siemreap;font-size: 10px">{{ $item['chemistry'] }}</td>

                @if ($type != 'ខែ')
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['rankChemistry'] }}
                    </td>
                @endif

                <td style="font-family: Khmer OS Siemreap;font-size: 10px">{{ $item['biology'] }}</td>

                @if ($type != 'ខែ')
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['rankBiology'] }}
                    </td>
                @endif

                <td style="font-family: Khmer OS Siemreap;font-size: 10px">{{ $item['earth_science'] }}</td>

                @if ($type != 'ខែ')
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['rankEarth'] }}
                    </td>
                @endif

                <td style="font-family: Khmer OS Siemreap;font-size: 10px">{{ $item['pe'] }}</td>

                @if ($type != 'ខែ')
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['rankPe'] }}
                    </td>
                @endif

                <td style="font-family: Khmer OS Siemreap;font-size: 10px">{{ $item['english'] }}</td>

                @if ($type != 'ខែ')
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['rankEnglish'] }}
                    </td>
                @endif

                <td style="font-family: Khmer OS Siemreap;font-size: 10px">{{ $item['computer'] }}</td>

                @if ($type != 'ខែ')
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px ">
                        {{ $item['rankComputer'] }}
                    </td>
                @endif

                <td style="font-family: Khmer OS Siemreap;font-size: 10px">{{ $item['total_score'] }}</td>

                @if ($type == 'ខែ' || $type == 'month')
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px">{{ $item['avg'] }}</td>

                    <td style="font-family: Khmer OS Siemreap;font-size: 10px">{{ $item['rank'] }}</td>
                @endif

                @if ($type != 'ខែ')
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px">{{ $item['average_month_semester'] }}
                    </td>
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px">{{ $item['rank_month_semester'] }}
                    </td>
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px">{{ $item['average_3_month'] }}
                    </td>
                    <td style="font-family: Khmer OS Siemreap;font-size: 10px">{{ $item['rank_3_month'] }}
                    </td>

                    @if ($type == 'semester1' || $type == 'ឆមាសទី១')
                        <td style="font-family: Khmer OS Siemreap;font-size: 10px">{{ $item['average_semester1'] }}
                        </td>
                    @endif
                    @if ($type == 'semester2' || $type == 'ឆមាសទី២')
                        <td style="font-family: Khmer OS Siemreap;font-size: 10px">{{ $item['average_semester1'] }}
                        </td>
                    @endif

                    <td style="font-family: Khmer OS Siemreap;font-size: 10px">{{ $item['rank'] }}
                    </td>
                @endif
                <td style="font-family: Khmer OS Siemreap;font-size: 10px">{{ $item['grade'] }}
                </td>
            </tr>
        @endforeach
    </tbody>

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
        <td colspan="{{ $type != 'ខែ' ? 25 : 9 }} ">
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
        <td colspan="{{ $type != 'ខែ' ? 25 : 9 }} ">
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
