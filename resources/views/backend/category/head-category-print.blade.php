<!DOCTYPE html>
<html>
<head>
    <title>Print Heads</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        h2 {
            text-align: center;
            border: 1px solid #000;
            padding: 8px;
            margin-bottom: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px;
            font-size: 14px;
        }

        th {
            background: #f2f2f2;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }

        .total-row td {
            font-weight: bold;
        }
    </style>
</head>

<body onload="window.print()">

    {{-- CATEGORY TITLE --}}
    <h2>{{ $category->category_name }}</h2>

    <table class="text-center">
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th>Date</th>
                <th>Bill #</th>
                <th>Head</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp

            @foreach($heads as $index => $head)
                @php $total += $head->amount; @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($head->date)->format('d/m/Y') }}</td>
                    <td>{{ $head->bill_no ?? '-' }}</td>
                    <td>{{ $head->head_name }}</td>
                    <td class="text-right">{{ number_format($head->amount, 0) }}</td>
                </tr>
            @endforeach

            {{-- TOTAL --}}
            <tr class="total-row">
                <td colspan="4" class="text-center">Total</td>
                <td class="text-right">{{ number_format($total, 0) }}</td>
            </tr>
        </tbody>
    </table>

</body>
</html>