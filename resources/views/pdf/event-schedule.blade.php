<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Programacao do Evento</title>
    <style>
        @page {
            margin: 12mm 10mm 10mm 10mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #082f49;
            font-size: 11px;
            line-height: 1.2;
        }

        .topbar {
            width: 100%;
            background: #082f49;
            color: #ffffff;
            padding: 7px 10px;
        }

        .topbar-table {
            width: 100%;
            border-collapse: collapse;
        }

        .topbar-brand {
            width: 45%;
            vertical-align: middle;
        }

        .logo {
            max-width: 96px;
            max-height: 38px;
        }

        .brand-fallback {
            color: #c8a34a;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.06em;
        }

        .topbar-info {
            width: 55%;
            text-align: right;
            vertical-align: middle;
            font-size: 9px;
        }

        .hero {
            text-align: center;
            padding: 9px 8px 7px;
            border-bottom: 2px solid #0284c7;
        }

        .hero h1 {
            margin: 0;
            font-size: 19px;
            color: #082f49;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }

        .hero p {
            margin: 3px 0 0;
            color: #0c4a6e;
            font-size: 9px;
        }

        .columns {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 4px;
            margin-top: 6px;
            table-layout: fixed;
        }

        .columns td {
            width: 50%;
            vertical-align: top;
        }

        .columns tr {
            page-break-inside: avoid;
        }

        .card {
            border: 1px solid #d7e3ef;
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 4px;
            page-break-inside: avoid;
        }

        .section-bar {
            background: #075985;
            background: linear-gradient(90deg, #075985 0%, #0284c7 100%);
            color: #ffffff !important;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            padding: 5px 8px;
            font-size: 10px;
        }

        .item-table {
            width: 100%;
            border-collapse: collapse;
        }

        .item-table thead th {
            background: #082f49;
            color: #ffffff;
            text-align: left;
            padding: 5px 7px;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .item-table tbody tr:nth-child(odd) {
            background: #f3f7fb;
        }

        .item-table tbody tr:nth-child(even) {
            background: #e6eef6;
        }

        .item-table td {
            padding: 4px 7px;
            border-bottom: 1px solid #d7e3ef;
            vertical-align: top;
        }

        .item-table td.time {
            width: 34%;
            font-weight: 700;
            color: #082f49;
            white-space: nowrap;
        }

        .item-table td.content {
            width: 66%;
            color: #0c4a6e;
        }

        .subtitle {
            font-size: 9px;
            color: #075985;
            margin-top: 1px;
        }

        .empty-state {
            margin: 10px 0;
            border: 1px solid #d7e3ef;
            background: #f3f7fb;
            padding: 10px;
            text-align: center;
            border-radius: 6px;
            color: #0c4a6e;
            font-weight: 600;
        }

        .footer {
            margin-top: 4px;
            padding-top: 4px;
            border-top: 1px solid #d7e3ef;
            color: #0c4a6e;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <div class="topbar">
        <table class="topbar-table">
            <tr>
                <td class="topbar-brand">
                    @if ($logoDataUri)
                        <img src="{{ $logoDataUri }}" alt="Evangelismo Explosivo" class="logo">
                    @else
                        <span class="brand-fallback">EVANGELISMO EXPLOSIVO</span>
                    @endif
                </td>
                <td class="topbar-info">
                    <div>{{ $training->church?->name ?? 'Igreja anfitria a confirmar' }}</div>
                    <div>{{ $training->city }} - {{ $training->state }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="hero">
        <h1>Programacao do Evento</h1>
        <p>{{ $training->course?->type }}: {{ $training->course?->name }}</p>
        <p>{{ $datesSummary }}</p>
    </div>

    @if (count($scheduleDays) === 0)
        <div class="empty-state">Programacao ainda nao publicada.</div>
    @else
        @php
            $maxRows = max(count($pdfColumns['left']), count($pdfColumns['right']));
        @endphp
        <table class="columns">
            @for ($row = 0; $row < $maxRows; $row++)
                <tr>
                    <td>
                        @if (isset($pdfColumns['left'][$row]))
                            @php($block = $pdfColumns['left'][$row])
                            <div class="card">
                                <div class="section-bar">{{ $block['heading'] }}</div>
                                <table class="item-table">
                                    <thead>
                                        <tr>
                                            <th>Horario</th>
                                            <th>Conteudo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($block['items'] as $item)
                                            <tr>
                                                <td class="time">{{ $item['timeRange'] }}</td>
                                                <td class="content">
                                                    <div>{{ $item['title'] }}</div>
                                                    @if (!empty($item['devotional']))
                                                        <div class="subtitle">Devocional: {{ $item['devotional'] }}</div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </td>
                    <td>
                        @if (isset($pdfColumns['right'][$row]))
                            @php($block = $pdfColumns['right'][$row])
                            <div class="card">
                                <div class="section-bar">{{ $block['heading'] }}</div>
                                <table class="item-table">
                                    <thead>
                                        <tr>
                                            <th>Horario</th>
                                            <th>Conteudo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($block['items'] as $item)
                                            <tr>
                                                <td class="time">{{ $item['timeRange'] }}</td>
                                                <td class="content">
                                                    <div>{{ $item['title'] }}</div>
                                                    @if (!empty($item['devotional']))
                                                        <div class="subtitle">Devocional: {{ $item['devotional'] }}</div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </td>
                </tr>
            @endfor
        </table>
    @endif

    <div class="footer">
        Horario de Brasilia | Gerado em {{ $generatedAt->format('d/m/Y H:i') }}
    </div>
</body>
</html>
