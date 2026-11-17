<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dunyapur Public School - Admission Form</title>

<style>
    body {
        font-family: Arial, sans-serif;
        font-size: 18px;
        line-height: 1.35;
        margin: 0;
        color: #000;
        font-weight: 600;
        
    }

    /* PAGE */
    .page {
        width: 210mm;
        /* min-height: 297mm; */
        margin: auto;
        padding: 10mm 12mm;
        box-sizing: border-box;
        page-break-after: always;
    }

    /* SIGN LINE */
    .sign-line {
        border-top: 1px solid #000;
        width: 92%;
        margin: 6px auto;
    }

    /* HEADINGS */
    h1 {
        text-align: center;
        font-size: 22px;
        margin: 4px 0;
        font-weight: 900;
    }

    .school-location {
        text-align: center;
        font-size: 14px;
        font-weight: 700;
        margin: 2px 0 4px;
    }

    .form-subtitle {
        text-align: center;
        font-size: 14px;
        font-weight: 900;
        margin: 6px 0 10px;
    }

    /* PHOTO BOX */
    div[style*="height: 100px"],
    div[style*="height: 110px"] {
        height: 85px !important;
        margin-bottom: 20px !important;
    }

    /* TABLE */
    table {
        width: 100%;
        border-collapse: collapse;
        margin: 4px 0;
    }

    /* SINGLE BORDER SYSTEM */
    td,
    th {
        border: 1px solid #000;
        padding: 5px 6px;
        vertical-align: middle;
        font-size: 11.5px;
        font-weight: 600;
    }

    /* LABEL */
    .label {
        font-weight: 900;
        background: #f4f4f4;
        width: 25%;
    }

    /* SECTION HEADER */
    .section-header {
        background: #e5e5e5;
        font-weight: 900;
        text-align: center;
        padding: 5px;
        margin: 10px 0 4px;
        font-size: 13px;
        border: 1px solid #000;
    }

    /* DECLARATION */
    .declaration {
        margin: 8px 0;
        font-size: 12px;
    }

    .declaration ol {
        margin: 4px 0;
        padding-left: 20px;
    }

    .declaration li {
        margin-bottom: 2px;
    }

    /* SIGNATURE AREA */
    .signatures {
        margin-top: 10px;
        padding-top: 5px;
        clear: both;
    }

    .signatures > div {
        padding-top: 25px;
    }

    /* TEST TABLE */
    .test-table th,
    .test-table td {
        font-size: 10.5px;
        padding: 4px;
        font-weight: 700;
    }

    /* OFFICIAL SECTION */
    .official-section {
        margin-top: 18px;
        page-break-inside: avoid;
    }

    /* OFFICIAL GRID */
    .official-grid {
        display: flex;
        gap: 20px;
    }

    /* COLUMNS */
    .left-column {
        width: 65%;
    }

    .right-column {
        width: 35%;
        font-size: 11px;
        line-height: 1.6;
    }

    /* PRINCIPAL ORDER SECTION - Client requirement */
    .principal-order {
        text-align: center;
        margin: 0px 0 15px 0;
    }

    .principal-heading {
        display: inline-block;
        border: 1px solid #000;
        padding: 6px 24px;
        font-size: 13px;
        font-weight: 900;
        background: #fff;
        margin: 0 auto 12px auto;
    }

    .principal-subline {
        border-top: 1px solid #000;
        width: 75%;
        margin: 0 auto 10px auto;
        height: 1px;
    }

    .principal-decision {
        font-size: 12px;
        font-weight: 700;
        line-height: 1.7;
        margin: 10px 0;
    }

    .principal-signature {
        margin-top: 40px;
        text-align: center;
    }

    .principal-signature .sign-line {
        width: 80%;
        margin: 0 auto 10px auto;
    }

    /* SECOND PAGE */
    .page:nth-of-type(2) {
        display: flex;
        flex-direction: column;
        height: 297mm;       
        padding: 10mm 12mm;
        box-sizing: border-box;
        page-break-after: always;
    }

    /* DECLARATION */
    .page:nth-of-type(2) .declaration {
        margin: 0 0 4px 0;
        flex-shrink: 0;     
    }

    /* OFFICIAL SECTION FULL HEIGHT */
    .page:nth-of-type(2) .official-section {
        display: flex;
        flex-direction: column;
        flex-grow: 1;
        justify-content: flex-start; 
        margin: 0;
        padding: 0;
    }

    /* TEST TABLE FIX */
    .test-table {
        page-break-inside: avoid;
        margin: 0;          
        padding: 0;
        border-collapse: collapse;
        flex-shrink: 0;     
    }

    /* OFFICIAL GRID */
    .page:nth-of-type(2) .official-grid {
        display: flex;
        gap: 20px;
        margin-top: 4px;      
        flex-grow: 1;         
    }

    /* LEFT AND RIGHT COLUMNS */
    .left-column,
    .right-column {
        display: flex;
        flex-direction: column;
        justify-content: flex-start; 
    }

    /* FEE SECTION STRETCH */
    .left-column {
        margin-bottom: auto; 
        font-weight: 800;

    }

    /* REMOVE EXTRA GAPS FROM TABLE CELLS */
    .test-table td,
    .test-table th {
        padding: 2px 4px;      
    }
    

    /* PRINT OPTIMIZATION */
    @media print {
        body {
            margin: 0;
        }

        .page{
            width:210mm;
            height:297mm;
            margin:0 auto;
            padding:10mm 12mm;
            box-sizing:border-box;
        }

        table {
            page-break-inside: auto;
        }

        tr {
            page-break-inside: avoid;
        }

        .declaration,
        .signatures,
        .principal-order {
            page-break-inside: avoid;
        }

        /* PRINCIPAL ORDER SECTION */
            .principal-order{
                text-align:center;
                margin-top:20px;
            }

            .principal-heading{
                display:inline-block;
                border:2px solid #000;
                padding:5px 20px;
                font-size:13px;
                font-weight:900;
            }

            .principal-small-border{
                border-top:1px solid #000;
                width:70%;
                margin:8px auto;
            }

            .principal-decision{
                font-size:12px;
                font-weight:700;
                margin-top:10px;
                line-height:1.8;
            }

            .principal-sign{
                margin-top:25px;
            }

            .principal-sign .sign-line{
                width:80%;
                margin:auto;
            }
    }
</style>
</head>

<body>

    <div class="page">

        <h1>DUNYAPUR PUBLIC SCHOOL</h1>
        <div class="school-location">DUNYAPUR</div>

        <div class="form-subtitle">APPLICATION FOR ADMISSION</div>

        <div style="position: relative; height: 100px; margin-bottom: 30px;">
            <div
                style="width: 85px; height: 100px; float: left; text-align: center; line-height: 100px; font-size: 9px; margin-right: 10px;">
                @if ($student->photo_path && file_exists(public_path($student->photo_path)))
                    <img src="{{ asset($student->photo_path) }}" alt="Student Photo" width="85" height="100"
                        style="display:block; object-fit:cover;">
                @else
                    <img src="{{ asset('default/dummy.png') }}" alt="Student Photo" width="85" height="100"
                        style="display:block; object-fit:cover;">
                @endif
            </div>

            <div style="position:absolute; top:0; right:0; width:130px; font-size:8.5px; line-height:1.4;">

                <p style="margin:7px 0; font-size:bold; display:flex; justify-content:space-between;">
                    <span>Admin.No:</span>
                    <span class="underline-med">______________</span>
                </p>

                <p style="margin:7px 0; font-size:bold; display:flex; justify-content:space-between;">
                    <span>Roll.No:</span>
                    <span class="underline-med">______________</span>
                </p>

                <p style="margin:7px 0; font-size:bold; display:flex; justify-content:space-between;">
                    <span>Regd.No:</span>
                    <span class="underline-med">{{$student->register_no ?? '______________'}}</span>
                </p>

                <p style="margin:7px 0; font-size:bold; display:flex; justify-content:space-between;">
                    <span>Id.Card No:</span>
                    <span class="underline-med">______________</span>
                </p>

                <p style="margin:7px 0; font-size:bold; display:flex; justify-content:space-between;">
                    <span>Account No:</span>
                    <span class="underline-med">______________</span>
                </p>

            </div>
        </div>

        <table>
            {{-- Name --}}
            <tr>
                <td class="label" style="width: 15%;"> Name</td>
                <td colspan="7">
                    {{ strtoupper($student->name ?? '____________________________________________') }}
                </td>
            </tr>

            {{-- Date of Birth + B-Form --}}
            <tr>
                <td class="label">Date of Birth</td>
                <td colspan="2">
                    {{ $student->date_of_birth
                        ? \Carbon\Carbon::parse($student->date_of_birth)->format('d/m/Y')
                        : '______________' }}
                </td>

                <td class="label">B-Form No</td>
                <td colspan="3">
                    {{ $student->b_form_no ?? '____________________________' }}
                </td>

                <td class="label">Religion</td>
            </tr>

            <tr>
                <td class="label">Gender</td>
                <td>
                    {{ $student->gender ?? 'M/F' }}
                </td>

                <td class="label">Caste</td>
                <td>
                    {{ $student->caste ?? '________________' }}
                </td>

                <td class="label">Domicile</td>
                <td colspan="2">
                    {{ $student->domicile ?? '____________________________' }}
                </td>

                <td>
                    {{ $student->religion ?? '________' }}
                </td>
            </tr>

            {{-- Siblings --}}
            <tr>
                <td class="label">Siblings In DPS</td>
                <td colspan="7">
                    {{ $student->sibling->sibling_in_dps ?? '____________________________' }}
                </td>
            </tr>
        </table>

        <div class="section-header">FATHER / MOTHER / GUARDIAN PARTICULARS</div>

        <table>
            <tr>
                <td class="label">Father's Name</td>
                <td colspan="3">
                    {{ $student->parent->father_name ?? '____________________________________________' }}</td>
            </tr>
            <tr>
                <td class="label">Mother's Name</td>
                <td colspan="3">
                    {{ $student->parent->mother_name ?? '____________________________________________' }}</td>
            </tr>
            <tr>
                <td class="label">NIC No</td>
                <td colspan="3">{{ $student->parent->father_nic ?? '_____ - _______ - _____' }} (Copy attached)</td>
            </tr>
            <tr>
                <td class="label">Guardian (if any)</td>
                <td>{{ $student->guardian->guardian_name ?? 'N/A' }}</td>
                <td class="label">Relation</td>
                <td>{{ $student->guardian->relation ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Occupation / Income</td>
                <td>{{ $student->parent->occupation ?? 'N/A' }} — Rs. {{ $student->parent->income ?? '________' }}
                </td>
                <td class="label">Address</td>
                <td colspan="2">
                    {{ $student->parent->address ?? '________________________________________________________________' }}
                </td>
            </tr>
            <tr>
                <td class="label">Contact No</td>
                <td colspan="3">
                    Office: {{ $student->parent->tel_office ?? '__________________' }} | Res:
                    {{ $student->parent->tel_res ?? '________________' }} | Mob:
                    {{ $student->parent->contact_no ?? '________' }}
                </td>
            </tr>
        </table>

        <div class="section-header">Class Applied For</div>
        <table>
            <tr>

                <td class="label">Class</td>
                <td>{{ $student->sessionItem->class ?? '________________' }}</td>
                <td class="label">Section</td>
                <td>{{ $student->sessionItem->section ?? '________' }}</td>
            </tr>
        </table>

        @if (isset($student->sibling))
            <div class="section-header text-center">4. Siblings in D.P.S (if any)</div>

            <table style="width:100%; text-align:center;">
                <tr>
                    <th style="text-align:center;">Name</th>
                    <th style="text-align:center;">Class</th>
                    <th style="text-align:center;">Section</th>
                </tr>
                <tr>
                    <td>{{ $student->sibling->sibling_name ?? '________________' }}</td>
                    <td>{{ $student->sibling->sibling_class ?? '______' }}</td>
                    <td>{{ $student->sibling->sibling_section ?? '______' }}</td>
                </tr>
            </table>
        @endif
        <div class="section-header">Previous School</div>
        <table>
            <tr>
                <td class="label">School Name</td>
                <td>{{ $student->previous_school ?? '____________________________________________' }}</td>
            </tr>
            <tr>
                <td class="label">Fee Paid Upto</td>
                <td>{{ $student->last_fee_paid_upto ? \Carbon\Carbon::parse($student->last_fee_paid_upto)->format('F Y') : '________________' }}
                </td>
            </tr>
        </table>

        <div class="section-header">Games/Sports & Extra Activities</div>
        <table>
            <tr>
                <td class="label">Games/Sports</td>
                <td>{{ $student->games_sports ?? '________________________________' }}</td>
            </tr>
            <tr>
                <td class="label">Extra Curricular</td>
                <td>{{ $student->extra_curricular ?? '________________________________' }}</td>
            </tr>
        </table>

        <!-- First Declaration -->
        <div class="declaration">
            <strong>DECLARATION</strong><br>

            <ol style="margin:4px 0; padding-left:20px;">
                <li>I hereby declare that the particulars and information given above are correct to the best of my
                    knowledge and belief.</li>
                <li>I will not hold the school responsible for any accident resulting in injury to my child/ward during
                    stay in the school.</li>
                <li>I have read the rules and shall abide by them in letter and spirit.</li>
                <li>Admission here does not entitle transfer to any other branch. No objection if applied elsewhere.
                </li>
                <li>I shall promote "Live and Let Live" with the institution for betterment of kids and country.</li>
            </ol>

            <div class="signatures">
                <div style="float: left; width: 45%; text-align: left; padding-top: 16px;">
                    <strong>Date</strong> <span class="underline-med">____________________</span>
                </div>
                <div style="float: right; width: 50%; text-align: center;">
                    <div class="sign-line"></div>
                    <strong>Parents / Guardian Signature</strong><br>
                </div>
            </div>
        </div>

    </div>

   
    <div class="page">

        <!-- Second Declaration -->
        <div class="declaration">
            <strong>DECLARATION BY THE APPLICANT</strong><br>
            <em>(Please Make Sure practice Right Now On)</em><br>

            <ol style="margin:4px 0; padding-left:20px;">
                <li>All particulars and information given are correct to the best of my knowledge and belief.</li>
                <li>I undertake to abide by all rules regarding academics, discipline, conduct and behaviour of D.P.S.</li>
                <li>I accept all decisions of the Principal / Chairman (BOG/BOT).</li>
                <li>We have read the prospectus/rules and shall abide by them in letter and spirit.</li>
                <li>I shall pay all fees/dues on time; school may expel student for non-payment (recoverable by law).</li>
            </ol>

            <div class="signatures">
                <div style="float: left; width: 48%; text-align: center;">
                    <div class="sign-line"></div>
                    <strong>FATHER'S / MOTHER'S / GUARDIAN'S</strong><br>
                    Signature (whichever applicable)<br>
                    Dated <span class="underline-short">____________</span>
                </div>

                <div style="float: right; width: 48%; text-align: center;">
                    <div class="sign-line"></div>
                    <strong>STUDENT'S / APPLICANT'S</strong><br>
                    Signature (only board class students)<br>
                    Dated <span class="underline-short">____________</span>
                </div>
            </div>
        </div>

        <div class="official-section">

            <div class="official-title" style="font-weight:900; text-align:center; margin:15px 0 8px;">
                For OFFICIAL USE ONLY (LOW RATED WARDS ADVISED TO ENHANCE PROFICIENCY)
            </div>

            <table class="test-table" style="width:100%; margin:12px 0 24px 0;">
                <tr>
                    <td>Admission Test</td>
                    <th>Eng.</th>
                    <th>Urdu</th>
                    <th>Maths</th>
                    <th>GSc.</th>
                    <th>Phy.</th>
                    <th>Chem.</th>
                    <th>Bio.</th>
                    <th>Isl.</th>
                    <th>Pak.St.</th>
                    <th>Inter-View</th>
                    <th>G.K</th>
                    <th>G. Total</th>
                </tr>
                <tr>
                    <td>Previous Academic Grades</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>Admission Test Marks</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </table>

            <div class="official-grid">

                <div class="left-column">

                    <strong style="font-size:12px;">Fee Details (RS)</strong>
                    @php $feeTotal = 0; @endphp
                    <div style="font-size:11px; line-height:1.7; margin-top:6px;">
                        {{-- Voucher Items --}}
                        @php
                            $isDiscount = (int) ($student->discount ?? 0) === 1;
                            $feeTotal = 0;
                        @endphp

                        @if ($isDiscount)
                            @php
                                $feeTotal = (float) ($student->discount_amount ?? 0);
                            @endphp

                            <div style="display:flex;">
                                <span>
                                    Discount Amount : 
                                </span>
                                <span style="margin-left:10px;">
                                    {{ number_format($feeTotal, 2) }}
                                </span>
                            </div>
                        @else
                            @if ($voucher && $voucher->items->count())
                                @foreach ($voucher->items as $item)
                                    @php
                                        $amount = (float) ($item->amount ?? 0);
                                        $feeTotal += $amount;
                                    @endphp

                                        <div style="display:flex;">
                                            <span style="width:180px;" >
                                                {{ $item->fee_name }}
                                            </span>
                                            <span style="margin-left:10px;">
                                                {{ number_format($amount, 2) }}
                                            </span>
                                        </div>
                                @endforeach
                            @endif
                        @endif
                        <br>
                        <strong>
                            TOTAL AMOUNT PAID :
                        </strong>

                        <span class="underline-long">
                            @if ($feeTotal > 0)
                                {{ number_format($feeTotal, 2) }}
                            @else
                                ____________________
                            @endif
                        </span>

                        <br>

                        Receipt No : <span class="underline-med">{{ $student->receipt_no ?? '____________' }}</span><br>
                        Dated: <span class="underline-med" >{{ $student->fee_paid_date ? \Carbon\Carbon::parse($student->fee_paid_date)->format('d M Y') : '________' }}</span><br><br>

                        <div style="font-size:13px; line-height:1.8;">
                            <div style="display:flex; justify-content: center; align-items: end;">
                                <span style="width:110px; margin-top:5px;">Documents</span>
                                <span style="flex:1; font-size: 12px;"><strong>COMPLETE / INCOMPLETE</strong></span>
                            </div>

                            <div style="display:flex; margin-top:10px; margin-bottom:12px; ">
                                <span style="width:110px;">Accountant</span>
                                <span style="flex:1;">________________________</span>
                            </div>

                            <div style="display:flex;">
                                <span style="width:110px;">Reg. No :</span>
                                <span style="flex:1;">{{$student->register_no ?? '________________________'}}</span>
                            </div>

                            <div style="display:flex; margin-top:3px;">
                                <span style="width:120px;">Admission Date</span>
                                <span style="flex:1;">
                                    {{ $student->admission_date ? \Carbon\Carbon::parse($student->admission_date)->format('d M Y') : '___________' }}
                                </span>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Right column -->
                <div class="right-column">
                    <div class="principal-order">
                        * Admission Incharge / V.P Remarks <span style="display:inline-block; padding:30px 0;">  ______________________________</span><br><br>
                        <div class="principal-heading">ORDER OF THE PRINCIPAL</div>

                        <div class="principal-decision">
                            ADMITTED <br> <span style="display:inline-block; padding:17px 0;">__________________________</span><br>
                            NOT ADMITTED <span style="display:inline-block; padding:17px 0; margin-bottom:30px;">__________________________</span>
                        </div>

                        <div class="principal-sign">
                            <div class="sign-line"></div>
                            <strong>Principal's Signature</strong>
                        </div>
                    </div>

                </div>

            </div>
        </div>

    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>

</html>
