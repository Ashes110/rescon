<?php
$dbname = "database_name";
$user = "user_name";
$pass = "password";
$host = "domain";

$mysqli = new mysqli($host, $user, $pass, $dbname);

function getToday($date = "Y-m-d")
{
    $today = new DateTime();
    return $today->format($date);
}

function isToday($year, $month, $day)
{
    $today = getToday('Y-n-j');
    if ($today == $year . "-" . $month . "-" . $day) {
        return true;
    }
    return false;
}

function isSaturday($year, $month, $day)
{
    $day = new DateTime($year . "-" . $month . "-" . $day);
    if ((int)$day->format('w') == 6) {
        return true;
    }
    return false;
}

function isSunday($year, $month, $day)
{
    $day = new DateTime($year . "-" . $month . "-" . $day);
    if ((int)$day->format('w') == 0) {
        return true;
    }
    return false;
}

function getSunday()
{
    $today = new DateTime();
    $w = $today->format('w') - 1;
    $ymd = $today->format('Y-m-d');
    $next_prev = new DateTime($ymd);
    $next_prev->modify("-{$w} day");
    return $next_prev->format('Ymd');
}

function getWeekname($year, $month, $day, $n)
{
    $datetime = new DateTime($year . "-" . $month . "-" . $day);
    $datetime->modify($n);
    $week = array("日曜日", "月曜日", "火曜日", "水曜日", "木曜日", "金曜日", "土曜日");
    $num = (int)$datetime->format("w");
    return $week[$num];
}

function getNthDay($year, $month, $day, $n)
{
    $next_prev = new DateTime($year . '-' . $month . '-' . $day);
    $next_prev->modify($n);
    return $next_prev->format('Ymd');
}

if (isset($_GET['date'])) {
    $year_month_day = $_GET['date'];
} else {
    $year_month_day = getSunday();
}

$year  = substr($year_month_day, 0, 4);
$month = substr($year_month_day, 4, 2);
$day   = substr($year_month_day, 6, 2);
$month = sprintf("%01d", $month);
$day   = sprintf("%01d", $day);

$next_week = getNthDay($year, $month, $day, '+1 week');
$pre_week  = getNthDay($year, $month, $day, '-1 week');

$table = null;

for ($i = 0; $i < 7; $i++) {
    $ymd = getNthDay($year, $month, $day, '+' . $i . ' day');
    $week = getWeekname($year, $month, $day, '+' . $i . ' day');
    $y = substr($ymd, 0, 4);
    $m = substr($ymd, 4, 2);
    $d = substr($ymd, 6, 2);
    $n = sprintf("%01d", $m);
    $j = sprintf("%01d", $d);
    $t = sprintf("%01d", $m) . '/';
    $t .= sprintf("%01d", $d);


    if (isToday($y, $n, $j)) {
        $table .= '<td class="today">' . $week . '<br>' . $t . '</td>' . PHP_EOL;
    } elseif (isSaturday($y, $n, $j)) {
        $table .= '<td class="Sat">' . $week . '<br>' . $t . '</td>' . PHP_EOL;
    } elseif (isSunday($y, $n, $j)) {
        $table .= '<td class="Sun">' . $week . '<br>' . $t . '</td>' . PHP_EOL;
    } else {
        $table .= '<td>' . $week . '<br>' . $t . '</td>' . PHP_EOL;
    }
}

$timetable = null;
$time = strtotime("09:00");

for ($i = 0; $i < 21; $i++) {
    $timetable .= '<tr><td bgcolor=#f4d7f4>' . date("G:i", $time) . '〜</td>' . PHP_EOL;
    for ($j = 0; $j < 7; $j++) {
        $ymd = getNthDay($year, $month, $day, '+' . $j . ' day');
        $y = substr($ymd, 0, 4);
        $m = substr($ymd, 4, 2);
        $d = substr($ymd, 6, 2);
        $ymd = $y . '-' . $m . '-' . $d;
        $sql = "SELECT yn FROM checktable WHERE ymd='" . $ymd . "' AND hms='" . date('H:i', $time) . "'";
        $yn = mysqli_query($mysqli, $sql);
        $reserve = "×";
        $res = mysqli_fetch_assoc($yn);
        if ($res["yn"] == 1) {
            $reserve = "〇";
        }
        if ($reserve == "×") {
            $timetable .= '<td class="batu">' . $reserve . '</td>' . PHP_EOL;
        } else {
            $timetable .= '<td class="maru">' . $reserve . '</td>' . PHP_EOL;
        }
    }
    $timetable .= '</tr>';
    $time = strtotime("+30 minutes", $time);
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>日にち確認</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1 align="center">
        <font size="5" color="gray">まつ毛カール　Le ciel</font>
    </h1>
    <p>「〇」の時間帯は予約が可能です</p>
    <p>
        <font size="2">このページは予約可能時間の確認のみの為、<br>予約はLINE公式またはメールにてお願い致します。</font>
    </p>
    <table class="cal">
        <tr>
            <th colspan="1">
                <a href="<?php $_SERVER['SCRIPT_NAME']; ?>?date=<?php echo $pre_week; ?>">&laquo; 前週</a></td>
            <th colspan="6">
                <?php echo $year; ?>年</td>
            <th colspan="1">
                <a href="<?php $_SERVER['SCRIPT_NAME']; ?>?date=<?php echo $next_week; ?>">次週 &raquo;</a></td>
        </tr>
        <tr>
            <td>日付</td>
            <?php echo $table; ?>
        </tr>
        <?php echo $timetable; ?>
    </table>
</body>

</html>