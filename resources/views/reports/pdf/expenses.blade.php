<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; margin: 0; padding: 20px; }
h1 { font-size: 16px; margin: 0 0 4px; }
.sub { color: #64748b; font-size: 10px; margin-bottom: 16px; }
table { width: 100%; border-collapse: collapse; margin-top: 8px; }
th { background: #f1f5f9; text-align: left; padding: 6px 8px; font-size: 9px; text-transform: uppercase; letter-spacing: .05em; color: #64748b; border-bottom: 1px solid #e2e8f0; }
td { padding: 5px 8px; border-bottom: 1px solid #f1f5f9; }
.total-row td { font-weight: bold; background: #f8fafc; border-top: 2px solid #e2e8f0; }
.right { text-align: right; }
</style>
</head>
<body>
<h1>Расходы по категориям</h1>
<p class="sub">{{ $start->format('d.m.Y') }} — {{ $end->format('d.m.Y') }}</p>

<table>
    <thead>
        <tr>
            <th>Дата</th>
            <th>Категория</th>
            <th>Описание</th>
            <th class="right">Сумма</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $expense)
        <tr>
            <td>{{ $expense->expense_date->format('d.m.Y') }}</td>
            <td>{{ $expense->category }}</td>
            <td>{{ $expense->description }}</td>
            <td class="right">{{ number_format($expense->amount, 0, '.', ' ') }}</td>
        </tr>
        @endforeach
        <tr class="total-row">
            <td colspan="3">Итого</td>
            <td class="right">{{ number_format($total, 0, '.', ' ') }} сум</td>
        </tr>
    </tbody>
</table>
</body>
</html>
