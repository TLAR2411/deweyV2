<table width="595px">
    <tr>
        <td colspan="6" style="text-align: center; font-family: Khmer OS MOul;">ព្រះរាជាណាចក្រកម្ពុជា</td>
    </tr>
    <tr>
        <td colspan="6" style="text-align: center; font-family: Khmer OS MOul;">ជាតិ សាសនា ព្រះមហាក្សត្រ</td>
    </tr>
    <tr>
        <td colspan="3" style="text-align: left; font-family: Khmer OS MOul;">សាលាចំណេះទូទៅអន្តរជាតិ វីឌូ</td>
    </tr>
    <tr>
        <td colspan="2" style="text-align: left; font-family: Khmer OS MOul;">ការិយាល័យសិក្សា</td>
    </tr>
    <tr>
        <td colspan="2" style="text-align: left;"></td>
    </tr>
    <tr>
        <td colspan="6" style="text-align: center;font-family: Khmer OS MOul;">បញ្ជីរាយនាមសិស្សានុសិស្ស ថ្នាក់ទី <span>{{ $classroom->first()->grade_name ?? '' }}</span></td>
    </tr>
    <thead>
        <tr style="border: 1px solid black;">
            <th width="30px" style="text-align: center;font-size: 10px; font-family: Khmer OS Battambang;">ល.រ</th>
            <th style="font-family: Khmer OS Battambang; text-align: center;" width="140px">គោត្តនាម និងនាម</th>
            <th style="font-family: Khmer OS Battambang; text-align: center;" width="200px">អក្សរឡាតាំង</th>
            <th width="40px" style="font-family: Khmer OS Battambang; text-align: center;">ភេទ</th>
            <th width="120px" style="font-family: Khmer OS Battambang; text-align: center;">ថ្ងៃខែឆ្នាំកំណើត</th>
            <th width="50px" style="font-family: Khmer OS Battambang; text-align: center;">ផ្សេងៗ</th>

        </tr>
    </thead>
    <tbody>
        @foreach ($students as $index => $student)
        <tr>
            <td style="text-align: center;">{{ $index + 1 }}</td>
            <!-- <td style="text-align: left;">{{ $student->student_card_id }}</td> -->
            <td style="font-family: Khmer OS Battambang;font-size: 11px;">{{ $student->kh_name }}</td>
            <td>{{ $student->en_name }}</td>
            <td style="font-family: Khmer OS Battambang;">{{ $student->gender == 'M' ? 'ប្រុស' : 'ស្រី' }}</td>
            <td>{{ $student->dob }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="3" style="font-family: Khmer OS Battambang;">
                <p>ចំនួនសិស្សទាំងអស់&emsp; <span> {{ $total_student }}នាក់</span>&emsp;&emsp;&emsp;&emsp;សិស្សស្រី <span>{{$female_student}}</span></p>
            </td>
            <td colspan="3" style="text-align: center;">ថ្ងៃ ខែ ឆ្នាំ ឆស័ក ព.ស. </td>
        </tr>
        ​
    </tbody>
</table>