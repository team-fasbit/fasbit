<?php
$uri = $_SERVER['REQUEST_URI'];
$host = explode('api/',$uri);

$endpoint = $host[1];

include '/var/www/html/frontend/lib/common.php';


if($_POST['c_currency_select']!='' && $_POST['currency_select']!='')
{	
	include '/var/www/html/frontend/htdocs/api/index.php';
}

else
{
echo '<form action="market-price" method="post" id="market_price_frm" name="market_price_frm">';
echo '<table style="padding:10px;border: solid 2px #4f5050;visibility:visible;">';

echo '<tr>';
echo '<td style="padding:10px;">Currency Pair</td>';
echo '<td style="padding:10px;">:</td>';
echo '<td style="padding:10px;">';
?>
<select class="form-control custom-select" id="c_currency_select" name="c_currency_select" style="width:100px;">
<? if ($CFG->currencies): ?>
<? foreach ($CFG->currencies as $key => $currency): ?>
<? if (is_numeric($key) || $currency['is_crypto'] != 'Y') continue; ?>
<option <?= $currency['id'] == $c_currency1 ? 'selected="selected"' : '' ?>  value="<?=$currency['id']?>">
<?=$currency['currency'] ?>
</option>
<? endforeach; ?>
<? endif; ?>
</select>
<?php
echo '</td>';
echo '<td style="padding:10px;">';
?>
<select class="form-control custom-select" id="currency_select" name="currency_select" style="margin-left:5px;width:100px;">
<? if ($CFG->currencies): ?>
<? foreach ($CFG->currencies as $key => $currency): ?>
<? if (is_numeric($key) || $currency['id'] == $c_currency1) continue; ?>
<?php if ($currency['id']  !=27) {?>
<option <?= $currency['id'] == $currency1 ? 'selected="selected"' : '' ?>  value="<?=$currency['id']?>">
<?=$currency['currency'] ?>
</option>
<?php } ?>
<? endforeach; ?>
<? endif; ?>
</select>
<?php
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td style="padding:10px;text-align:center;" colspan="4"><input type="submit" value="submit"></td>';	
echo '</tr>';
echo '</table>';
echo '</form>';
}
?>