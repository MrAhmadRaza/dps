<!DOCTYPE html>
<html>
<head>
    <title>Accounts Report</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        h2 {
            text-align: center;
            margin-bottom: 5px;
        }

        h4 {
            text-align: center;
            margin-top: 0;
            margin-bottom: 20px;
        }

        .row {
            display: flex;
            justify-content: space-between;
        }

        .col {
            width: 48%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            border-bottom: 2px solid #000;
            padding: 6px;
        }

        td {
            padding: 6px;
            border-bottom: 1px solid #ccc;
        }

        .total {
            font-weight: bold;
            background: #f2f2f2;
        }

        .text-right {
            text-align: right;
        }

        @media print {
            body {
                margin: 10px;
            }
        }
    </style>
</head>

<body onload="window.print()">

    <h2>Dunyapur Public School Dunaypur</h2>
    <h4>Accounts Summary Report</h4>

    <div class="row">

        {{-- INCOME --}}
        @if($incomes->count())
        <div class="col">
            <table>
                <thead>
                    <tr>
                        <th>Income</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($incomes as $cat)
                    <tr>
                        <td>{{ $loop->iteration }}. {{ $cat->category_name }}</td>
                        <td class="text-right">
                            {{ number_format($cat->total_amount ?? 0, 2) }}
                        </td>
                    </tr>
                    @endforeach

                    <tr class="total">
                        <td>Total Income</td>
                        <td class="text-right">
                            {{ number_format($totalIncome, 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif


        {{-- EXPENSE --}}
        @if($expenses->count())
        <div class="col">
            <table>
                <thead>
                    <tr>
                        <th>Expense</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($expenses as $cat)
                    <tr>
                        <td>{{ $loop->iteration }}. {{ $cat->category_name }}</td>
                        <td class="text-right">
                            {{ number_format($cat->total_amount ?? 0, 2) }}
                        </td>
                    </tr>
                    @endforeach

                    <tr class="total">
                        <td>Total Expense</td>
                        <td class="text-right">
                            {{ number_format($totalExpense, 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif

    </div>

</body>
</html>