<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bulk eChecks</title>

    <style>
        @font-face {
            font-family: 'MICR';
            src: url("{{ public_path('fonts/MICR_E13B.ttf') }}") format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @page { margin: 18px; }

        body{
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            color:#000;
            font-size: 15px;
        }

        .check{
            width: 95%;
            height: 300px;
            padding: 25px 28px;
            box-sizing: border-box;
        }

        /* top info */
        .name{ font-size: 17px; font-weight: 700; margin-bottom: 2px; }
        .addr{ font-size: 15px; line-height: 1.2; }
        .checkno{ font-size: 16px; font-weight: 700; margin-bottom: 8px; }
        .bankline{ font-size: 15px; margin-bottom: 4px; text-align: left; }
        .datebox{
            display:inline-block;
            width:95px;
            border-bottom:1px solid #000;
            padding-bottom:2px;
            text-align:right;
            font-size:15px;
        }

        /* pay row */
        .label{ width: 88px; font-size: 15px; vertical-align: middle; }
        .payee{
            padding: 6px 10px;
            height: 20px;
            font-size: 20px;
            text-transform: uppercase;
            border-bottom: 2px solid #000;
            border-left: 2px solid #000;
        }

        .amountcell{ width:150px; padding-left:10px; vertical-align: middle; }
        .amountbox{
            border: 1px solid #000;
            height: 30px;
            padding: 4px 8px;
            width: 100%;
        }
        .amountbox td{ vertical-align: middle; }
        .dollar{ width:16px; font-weight:700; }
        .amt{ text-align:left; font-weight:700; font-size:15px; }

        /* words line */
        .wordsline{
            padding: 6px 10px;
            height: 24px;
            font-size: 20px;
            border-bottom: 2px solid #000;
            border-left: 2px solid #000;
        }
        .dollars{ width: 62px; padding-left: 6px; font-weight: 700; font-size: 15px; }

        /* memo */
        .small{ font-size: 10px; line-height: 1.25; }
        .memoLabel{ width: 50px; font-size: 15px; vertical-align: bottom; }
        .memoLine{ border-bottom: 2px solid #000; height: 14px; }

        /* micr */
        .micr{
            margin-top: 25px;
            text-align: center;
            font-family: 'MICR';
            font-size: 24px;
            letter-spacing: 1px;
            line-height: 1;
        }
        .micr span{ display:inline-block; margin: 0 15px; }
        .top .right{
            width: 35%;
            text-align: right;
        }
        .payrow .amountcell{
            width: 135px;      /* ✅ was 150px */
            padding-left: 10px;
            vertical-align: middle;
        }
    </style>
</head>
<body>

@foreach($echecks as $echeck)

    @include('merchant.echeck._check', ['echeck' => $echeck])

    @if (! $loop->last)
        <div class="page-break"></div>
    @endif

@endforeach

</body>
</html>
