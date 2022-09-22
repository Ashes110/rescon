<?php
session_start();

$dbname = "database_name";
$user = "user_name";
$pass = "password";
$host = "domain";

$mysqli = new mysqli($host, $user, $pass, $dbname);

if (isset($_POST["reserve"])) {
    $YMD = $_POST["date"];
    $HMS = $_POST["time"];
    $sql = "SELECT yn FROM checktable WHERE ymd='" . $YMD . "' AND hms='" . $HMS . "'";
    $yn = mysqli_query($mysqli, $sql);
    $res = mysqli_fetch_assoc($yn);
    if ($res["yn"] == null) {
        $osql = "INSERT INTO checktable (ymd, hms, yn) VALUES('" . $YMD . "', '" . $HMS . "', 1)";
    } elseif ($res["yn"] == 1) {
        $osql = "UPDATE checktable SET yn=0 WHERE ymd='" . $YMD . "' AND hms='" . $HMS . "'";
    } elseif ($res["yn"] == 0) {
        $osql = "UPDATE checktable SET yn=1 WHERE ymd='" . $YMD . "' AND hms='" . $HMS . "'";
    }
    $res = mysqli_query($mysqli, $osql);
}

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
    $timetable .= '<tr><td bgcolor="lightyellow">' . date("G:i", $time) . '~</td>' . PHP_EOL;
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
            $timetable .= '<td class="batu"><form action="' . $_SERVER['REQUEST_URI'] . '" method="post"><input type="hidden" name="date" value="' . $ymd . '"/><input type="hidden" name="time" value="' . date('H:i', $time) . '"/><input type="submit" name="reserve" value="' . $reserve . '"/></form></td>' . PHP_EOL;
        } else {
            $timetable .= '<td class="maru"><form action="' . $_SERVER['REQUEST_URI'] . '" method="post"><input type="hidden" name="date" value="' . $ymd . '"/><input type="hidden" name="time" value="' . date('H:i', $time) . '"/><input type="submit" name="reserve" value="' . $reserve . '"/></form></td>' . PHP_EOL;
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
    <meta http-equiv="Cache-Control" content="no-store">
    <title>管理画面</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
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