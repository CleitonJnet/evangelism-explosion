<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <title>Programação do Evento</title>
    <style>
        @page {
            margin: 12mm 10mm 12mm 10mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #0f172a;
            background: #ffffff;
            font-size: 11px;
            line-height: 1.35;
        }

        .brand-shell {
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            overflow: hidden;
        }

        .brand-header {
            background: #052f4a;
            color: #ffffff;
            padding: 9px 12px;
        }

        .brand-table {
            width: 100%;
            border-collapse: collapse;
        }

        .brand-left {
            width: 62%;
            vertical-align: middle;
        }

        .brand-right {
            width: 38%;
            text-align: right;
            vertical-align: middle;
            font-size: 10px;
            color: #e2e8f0;
        }

        .logo {
            max-width: 70px;
            max-height: 34px;
            display: block;
        }

        .brand-inline {
            width: 100%;
            border-collapse: collapse;
        }

        .brand-inline-logo {
            width: 30px;
            vertical-align: middle;
            padding-right: 2mm;
        }

        .brand-inline-texts {
            vertical-align: middle;
        }

        .brand-name {
            display: block;
            font-family: Cinzel, "DejaVu Serif", Georgia, serif;
            font-weight: 400;
            letter-spacing: 0.05em;
            color: #f8fafc;
            font-size: 12px;
        }

        .brand-slogan {
            margin-top: 0;
            font-size: 9px;
            letter-spacing: 0.04em;
            font-family: Roboto, "DejaVu Sans", Arial, sans-serif;
            line-height: 1.15;
        }

        .brand-slogan-strong {
            color: #facc15;
            font-weight: 800;
        }

        .brand-slogan-soft {
            color: #e2e8f0;
            font-weight: 600;
        }

        .brand-gold-line {
            height: 3px;
            background: #c7a840;
        }

        .hero {
            margin-top: 8px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 10px 12px;
            background: #f8fafc;
        }

        .hero-title {
            margin: 0;
            text-align: center;
            text-transform: uppercase;
            font-size: 18px;
            color: #052f4a;
            letter-spacing: 0.03em;
            font-weight: 800;
        }

        .hero-subtitle {
            margin: 4px 0 0;
            text-align: center;
            font-size: 11px;
            color: #334155;
            font-weight: 700;
        }

        .highlight-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 8px;
            margin-top: 8px;
        }

        .highlight-grid td {
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            background: #ffffff;
            padding: 7px 8px;
            vertical-align: top;
        }

        .field-label {
            color: #475569;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            font-weight: 700;
            margin: 0;
        }

        .field-value {
            margin: 2px 0 0;
            color: #0f172a;
            font-size: 12px;
            font-weight: 800;
        }

        .schedule-wrap {
            margin-top: 10px;
        }

        .day-block {
            margin-bottom: 10px;
        }

        .day-title {
            page-break-after: avoid;
            page-break-inside: avoid;
            text-align: center;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #e2e8f0;
            color: #052f4a;
            padding: 6px 8px;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .turn-card {
            margin-top: 6px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            overflow: hidden;
            page-break-inside: avoid;
            break-inside: avoid;
            page-break-before: auto;
        }

        .turn-title {
            background: #0369a1;
            color: #ffffff;
            text-align: center;
            padding: 5px 8px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .table th {
            background: #052f4a;
            color: #ffffff;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            text-align: left;
            padding: 6px 7px;
            border-bottom: 1px solid #cbd5e1;
        }

        .table tr {
            page-break-inside: avoid;
        }

        .table td {
            border-bottom: 1px solid #e2e8f0;
            padding: 6px 7px;
            vertical-align: top;
            color: #0f172a;
            font-size: 10px;
            page-break-inside: avoid;
        }

        .table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .col-time {
            width: 19%;
            white-space: nowrap;
            font-weight: 800;
            color: #052f4a;
        }

        .col-session {
            width: 66%;
        }

        .col-duration {
            width: 15%;
            white-space: nowrap;
            text-align: right;
            font-weight: 700;
            color: #1e293b;
        }

        .subtitle {
            margin-top: 2px;
            font-size: 9px;
            color: #475569;
        }

        .empty-state {
            margin-top: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #f8fafc;
            text-align: center;
            font-size: 11px;
            font-weight: 700;
            color: #334155;
            padding: 12px;
        }

        .footer {
            margin-top: 8px;
            border-top: 1px solid #cbd5e1;
            padding-top: 5px;
            font-size: 9px;
            color: #334155;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-right {
            text-align: right;
        }

        .footer-note {
            margin-top: 4px;
            color: #78350f;
            font-size: 9px;
            font-weight: 700;
        }
    </style>
</head>

<body>
    @php
        $courseName = trim(
            (string) (($training->course?->type ? $training->course->type . ': ' : '') .
                ($training->course?->name ?? 'Treinamento')),
        );
        $churchName = $training->church?->name ?? 'Igreja anfitriã a confirmar';
        $cityState = trim((string) (($training->city ?? '') . ' - ' . ($training->state ?? '')), ' -');
    @endphp

    <div class="brand-shell">
        <div class="brand-header">
            <table class="brand-table">
                <tr>
                    <td class="brand-left">
                        <table class="brand-inline">
                            <tr>
                                <td class="brand-inline-logo">
                                    @if ($logoDataUri)
                                        <img src="{{ $logoDataUri }}" alt="Evangelismo Explosivo" class="logo">
                                    @endif
                                </td>
                                <td class="brand-inline-texts">
                                    <span class="brand-name">EVANGELISMO EXPLOSIVO</span>
                                    <div class="brand-slogan">
                                        <span class="brand-slogan-strong">NO BRASIL</span>
                                        <span class="brand-slogan-soft"> - Até Que Todos Ouçam!</span>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td class="brand-right">
                        <div>Programação oficial do evento</div>
                        <div>{{ $datesSummary }}</div>
                    </td>
                </tr>
            </table>
        </div>
        <div class="brand-gold-line"></div>
    </div>

    <div class="hero">
        <h1 class="hero-title">Programação do Evento</h1>
        <p class="hero-subtitle">Horários oficiais por dia e turno</p>

        <table class="highlight-grid">
            <tr>
                <td>
                    <p class="field-label">Evento</p>
                    <p class="field-value">{{ $courseName !== '' ? $courseName : 'Treinamento' }}</p>
                </td>
                <td>
                    <p class="field-label">Igreja</p>
                    <p class="field-value">{{ $churchName }}</p>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="field-label">Carga horária completa</p>
                    <p class="field-value">{{ $workloadDuration ?? '--' }}</p>
                </td>
                <td>
                    <p class="field-label">Cidade</p>
                    <p class="field-value">{{ $cityState !== '' ? $cityState : '--' }}</p>
                </td>
            </tr>
        </table>

    </div>

    @if (count($scheduleDays) === 0)
        <div class="empty-state">Programação ainda não publicada.</div>
    @else
        <div class="schedule-wrap">
            @foreach ($scheduleDays as $day)
                <div class="day-block">
                    <div class="day-title">{{ $day['dayLabel'] }}</div>

                    @foreach ($day['groups'] as $group)
                        @php
                            $turnLabel = match ($group['turn']) {
                                'MANHA' => 'Manhã',
                                'TARDE' => 'Tarde',
                                'NOITE' => 'Noite',
                                default => 'Turno',
                            };
                        @endphp
                        <div class="turn-card">
                            <div class="turn-title">{{ $turnLabel }}</div>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th class="col-time">Horário</th>
                                        <th class="col-session">Sessão</th>
                                        <th class="col-duration">Duração</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($group['items'] as $item)
                                        <tr>
                                            <td class="col-time">{{ $item['timeRange'] }}</td>
                                            <td class="col-session">
                                                <div>{{ $item['title'] }}</div>
                                                @if (!empty($item['devotional']))
                                                    <div class="subtitle">Devocional: {{ $item['devotional'] }}</div>
                                                @endif
                                            </td>
                                            <td class="col-duration">{{ $item['duration'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    @endif

    <div class="footer">
        <table class="footer-table">
            <tr>
                <td>Horário de Brasília</td>
                <td class="footer-right">Gerado em {{ $generatedAt->format('d/m/Y H:i') }}</td>
            </tr>
        </table>
        <div class="footer-note">Nota: esse é um horário provisório, podendo sofrer ajustes durante o evento.</div>
    </div>
</body>

</html>
