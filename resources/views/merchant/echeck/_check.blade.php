@php
    $payerName = merchant_name_by_id($echeck->merchant_id);

    $bankLabel = $echeck->bank_name ?? 'Bank';
    $checkNo   = $echeck->id;
    $date      = optional($echeck->created_at)->format('m/d/Y') ?? date('m/d/Y');

    $amount = number_format((float)$echeck->amount, 2);

    $amountWords = function_exists('amount_to_words')
        ? amount_to_words((float)$echeck->amount)
        : $amount.' dollars';

    $rt          = $echeck->routing_number ?? '000000000';
    $micrAccount = $echeck->account_number ?? '000000000000';
    $micrCheck   = str_pad((string)$checkNo, 4, '0', STR_PAD_LEFT);

    $bankHolderName = $echeck->account_holder_name ?? '';
    $payerAddr1     = $echeck->account_holder_address1 ?? '';
    $payerAddr2     = $echeck->account_holder_address2 ?? '';

    // MICR symbols mapped by your font
    $transit = "t";
    $onus    = "o";
@endphp

<div class="check">

        <!-- TOP -->
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:6px; table-layout:fixed;">
            <tr>
                <td width="65%" valign="top">
                    <div class="name">{{ $bankHolderName }}</div>
                    <div class="addr">{{ $payerAddr1 }}</div>
                    <div class="addr">{{ $payerAddr2 }}</div>
                </td>
                <td width="35%" valign="top" align="right">
                    <div class="checkno">{{ $micrCheck }}</div>
                    <div class="bankline">{{ $bankLabel }}</div>
                    <div class="datebox">{{ $date }}</div>
                </td>
            </tr>
        </table>

        <!-- PAYEE + AMOUNT -->
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:6px;">
            <tr>
                <td class="label">Pay To The<br>Order Of</td>
                <td class="payee">{{ strtoupper($payerName) }}</td>
                <td class="amountcell">
                    <table class="amountbox" cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="dollar">$</td>
                            <td class="amt">{{ $amount }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- WORDS -->
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:10px;">
            <tr>
                <td class="wordsline">{{ $amountWords }}</td>
                <td class="dollars">Dollars</td>
            </tr>
        </table>

        <!-- MID -->
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:30px;">
            <tr>
                <td width="55%" valign="top">
                    <div style="margin-top:18px;font-size:15px;">
                        Customer Authorization Obtained:&nbsp;&nbsp;&nbsp;&nbsp;{{ $date }}
                    </div>

                    <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:10px;">
                        <tr>
                            <td class="memoLabel">Memo</td>
                            <td class="memoLine"></td>
                        </tr>
                    </table>
                </td>
                <td width="45%" valign="top" style="padding-left:14px;">
                    <div style="text-align:center;font-weight:800;font-size:14px;margin-bottom:4px;">SIGNATURE NOT REQUIRED</div>
                    <div class="small" style="margin-left:100px;">
                        Payment has been authorized by the depositor.<br>
                        Payee to hold you harmless for payment of this document.<br>
                        This document shall be deposited only to credit of payee.<br>
                        Absence of endorsement is guaranteed by payee's bank.
                    </div>
                </td>
            </tr>
        </table>

        <!-- MICR -->
        <div class="micr">
            <span>{{ $onus }}{{ $micrCheck }}{{ $onus }}</span>
            <span>{{ $transit }}{{ $rt }}{{ $transit }}</span>
            <span>{{ $micrAccount }}{{ $onus }}</span>
        </div>

    </div>
