<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; margin: 0; padding: 20px; }
h1 { font-size: 16px; margin: 0 0 4px; }
.sub { color: #64748b; font-size: 10px; margin-bottom: 8px; }
.kpi { display: inline-block; margin-right: 24px; }
.kpi-val { font-size: 18px; font-weight: bold; color: #3b82f6; }
.kpi-lbl { font-size: 9px; color: #64748b; }
table { width: 100%; border-collapse: collapse; margin-top: 16px; }
th { background: #f1f5f9; text-align: left; padding: 6px 8px; font-size: 9px; text-transform: uppercase; letter-spacing: .05em; color: #64748b; border-bottom: 1px solid #e2e8f0; }
td { padding: 5px 8px; border-bottom: 1px solid #f1f5f9; }
.bar-wrap { width: 80px; background: #e2e8f0; height: 6px; display: inline-block; vertical-align: middle; }
.bar-fill { background: #3b82f6; height: 6px; display: inline-block; }
</style>
</head>
<body>
<h1>Загрузка номеров</h1>
<p class="sub">{{ $start->format('d.m.Y') }} — {{ $end->format('d.m.Y') }}</p>
<div class="kpi"><div class="kpi-val">{{ $avgPct }}%</div><div class="kpi-lbl">Средняя загрузка</div></div>
<div class="kpi"><div class="kpi-val">{{ $totalRooms }}</div><div class="kpi-lbl">Номеров</div></div>

<table>
    <thead>
        <tr>
            <th>Дата</th>
            <th>Занято</th>
            <th>Всего</th>
            <th>Загрузка</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
        <tr>
            <td>{{ $row['date'] }}</td>
            <td>{{ $row['booked'] }}</td>
            <td>{{ $row['total'] }}</td>
            <td>
                <span class="bar-wrap"><span class="bar-fill" style="width:{{ $row['pct'] * 0.8 }}px"></span></span>
                {{ $row['pct'] }}%
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
