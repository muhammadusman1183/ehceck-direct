<!DOCTYPE html>
<html>
<head>
<style>
body {
    font-family: Arial, sans-serif;
    background: #fff;
}
.check {
    width: 900px;
    border: 2px solid #000;
    padding: 20px;
}
.row { display: flex; justify-content: space-between; }
.bold { font-weight: bold; }
.line { border-bottom: 2px solid #000; padding: 4px; }
.amount-box {
    border: 2px solid #000;
    padding: 8px 14px;
    font-size: 20px;
}
.small { font-size: 12px; }
.micr {
    font-family: 'OCR A Std', monospace;
    font-size: 18px;
    margin-top: 20px;
}
</style>
</head>

<body>
<div class="check">

<div class="row">
    <div>
        <div class="bold">{{ $echeck->payer_name }}</div>
        <div class="small">{{ $echeck->payer_address }}</div>
    </div>
    <div class="bold">{{ $echeck->check_number }}</div>
</div>

<br>

<div class="row">
    <div>Pay To The Order Of</div>
    <div class="amount-box">${{ number_format($echeck->amount, 2) }}</div>
</div>

<div class="line">{{ strtoupper($echeck->payee_name) }}</div>

<br>

<div class="line">
    {{ strtoupper($echeck->amount_words) }} DOLLARS
</div>

<br>

<div class="row">
    <div class="small">
        Customer Authorization Obtained: {{ $echeck->authorization_date }}
    </div>
    <div class="bold">SIGNATURE NOT REQUIRED</div>
</div>

<div class="micr">
    {{ $echeck->routing_number }} | {{ $echeck->account_number }} | {{ $echeck->check_number }}
</div>

</div>
</body>
</html>
