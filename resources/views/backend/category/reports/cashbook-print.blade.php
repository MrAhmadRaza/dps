<!DOCTYPE html>
<html>

<head>
    <title>Cash Book</title>

    <style>
        body {
            font-family: Arial;
            margin: 20px;
        }

        h2,
        h3 {
            text-align: center;
            margin: 5px;
        }

        .section-title {
            background: #d9d9d9;
            padding: 8px;
            font-weight: bold;
            text-align: center;
            margin-top: 20px;
            border: 1px solid #000;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .col {
            width: 49%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            font-size: 13px;
            text-align: center;
            vertical-align: middle;
        }

        th {
            background: #f2f2f2;
            font-weight: bold;
        }

        .amount {
            text-align: right;
        }

        .total {
            font-weight: bold;
            background: #eee;
        }

        @media print {
            body {
                margin: 10px;
            }
        }
    </style>
</head>

<body onload="window.print()">

    <h2>Dunyapur Public School Dunyapur</h2>
    <h3>Cash Book Month of {{ $monthName }} {{ $year }}</h3>

    {{-- ================= SUMMARY ================= --}}
    <div class="row">

        {{-- EXPENSE --}}
        <div class="col">
            <table>
                <thead>
                    <tr>
                        <th>Expenses</th>
                        <th>Amount</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($expense as $item)
                        <tr>
                            <td>{{ $item->head_name }}</td>
                            <td class="amount">{{ number_format($item->amount) }}</td>
                        </tr>
                    @endforeach

                    <tr class="total">
                        <td>Total Expense</td>
                        <td class="amount">{{ number_format($totalExpense) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- INCOME --}}
        <div class="col">
            <table>
                <thead>
                    <tr>
                        <th>Income</th>
                        <th>Amount</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($income as $item)
                        <tr>
                            <td>{{ $item->head_name }}</td>
                            <td class="amount">{{ number_format($item->amount) }}</td>
                        </tr>
                    @endforeach

                    <tr class="total">
                        <td>Total Income</td>
                        <td class="amount">{{ number_format($totalIncome) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>

    {{-- ================= DATE WISE ================= --}}
    <div class="section-title">
        Date Wise Detailed - {{ $monthName }} {{ $year }}
    </div>
    <div class="row">

        {{-- EXPENSE --}}
        <div class="col">

            <div class="section-title">Expense</div>

            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Details</th>
                        <th>Amount</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($groupedExpense as $date => $items)
                        @foreach ($items as $item)
                            <tr>
                                <td>{{ $date }}</td>
                                <td>{{ $item->head_name }}</td>
                                <td class="amount">{{ number_format($item->amount) }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- INCOME --}}
        <div class="col">

            <div class="section-title">Income</div>

            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Details</th>
                        <th>Amount</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($groupedIncome as $date => $items)
                        @foreach ($items as $item)
                            <tr>
                                <td>{{ $date }}</td>
                                <td>{{ $item->head_name }}</td>
                                <td class="amount">{{ number_format($item->amount) }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>

    {{-- ================= FINAL RECONCILIATION ================= --}}
    <div class="section-title">
        Final Reconciliation
    </div>

    <div class="row">

        {{-- EXPENSE --}}
        <div class="col">
            <table>
                <tr class="total">
                    <td>Total Expense</td>
                    <td class="amount">{{ number_format($totalExpense) }}</td>
                </tr>

            </table>
        </div>

        {{-- INCOME --}}
        <div class="col">
            <table>
                <tr class="total">
                    <td>Total Income</td>
                    <td class="amount">{{ number_format($totalIncome) }}</td>
                </tr>

            </table>
        </div>

    </div>

    {{-- NET BALANCE --}}
    <div class="section-title">
        Net Balance: {{ number_format($netBalance) }}
    </div>

</body>

</html>
