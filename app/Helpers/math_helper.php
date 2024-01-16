<?php

function divideFloat($numerator, $denominator, $precision = 3)
{
    $numerator *= pow(10, $precision);
    $result = (int)($numerator / $denominator);
    if (strlen($result) == $precision) return '0.' . $result;
    else return preg_replace('/(\d{' . $precision . '})$/', '.\1', $result);
}
