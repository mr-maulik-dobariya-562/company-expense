<?php

use App\Helpers\XMLContent;
use App\Models\Master\Unit;

define('MINUTE_IN_SECONDS', 60);
define('HOUR_IN_SECONDS', 60 * MINUTE_IN_SECONDS);
define('DAY_IN_SECONDS', 24 * HOUR_IN_SECONDS);
define('WEEK_IN_SECONDS', 7 * DAY_IN_SECONDS);
define('MONTH_IN_SECONDS', 30 * DAY_IN_SECONDS);
define('YEAR_IN_SECONDS', 365 * DAY_IN_SECONDS);


function get_image_tag($url, $size = 'thumb', $options = [])
{
    $options = array_merge([
        'lazy' => true
    ], $options);

    if ($url) {
        $alt   = $options['alt'] ?? '';
        $attr  = '';
        $class = $options['class'] ?? '';
        if (!empty($options['lazy'])) {
            $class .= ' lazy';
            $attr .= " data-src=" . e($url) . " ";
        } else {
            $attr .= " src='" . e($url) . "' ";
        }
        return sprintf("<img class='%s' %s alt='%s'>", e($class), $attr, e($alt));
    }
    return "";
}

function display_datetime($time)
{

    if (is_string($time)) {
        $time = strtotime($time);
    }

    if (is_object($time)) {
        return $time->format(get_date_format() . ' H:i');
    }

    return date(get_date_format() . ' H:i', $time);
}

function get_date_format()
{
    return ('m/d/Y');
}

function human_time_diff($from, $to = false)
{

    if (is_string($from)) $from = strtotime($from);
    if (is_string($to)) $to = strtotime($to);

    if (empty($to)) {
        $to = time();
    }

    $diff = (int) abs($to - $from);

    if ($diff < HOUR_IN_SECONDS) {
        $mins = round($diff / MINUTE_IN_SECONDS);
        if ($mins <= 1) {
            $mins = 1;
        }
        /* translators: Time difference between two dates, in minutes (min=minute). %s: Number of minutes */
        if ($mins) {
            $since = __(':num mins', [ 'num' => $mins ]);
        } else {
            $since = __(':num min', [ 'num' => $mins ]);
        }
    } elseif ($diff < DAY_IN_SECONDS && $diff >= HOUR_IN_SECONDS) {
        $hours = round($diff / HOUR_IN_SECONDS);
        if ($hours <= 1) {
            $hours = 1;
        }
        /* translators: Time difference between two dates, in hours. %s: Number of hours */
        if ($hours) {
            $since = __(':num hours', [ 'num' => $hours ]);
        } else {
            $since = __(':num hour', [ 'num' => $hours ]);
        }
    } elseif ($diff < WEEK_IN_SECONDS && $diff >= DAY_IN_SECONDS) {
        $days = round($diff / DAY_IN_SECONDS);
        if ($days <= 1) {
            $days = 1;
        }
        /* translators: Time difference between two dates, in days. %s: Number of days */
        if ($days) {
            $since = __(':num days', [ 'num' => $days ]);
        } else {
            $since = __(':num day', [ 'num' => $days ]);
        }
    } elseif ($diff < MONTH_IN_SECONDS && $diff >= WEEK_IN_SECONDS) {
        $weeks = round($diff / WEEK_IN_SECONDS);
        if ($weeks <= 1) {
            $weeks = 1;
        }
        /* translators: Time difference between two dates, in weeks. %s: Number of weeks */
        if ($weeks) {
            $since = __(':num weeks', [ 'num' => $weeks ]);
        } else {
            $since = __(':num week', [ 'num' => $weeks ]);
        }
    } elseif ($diff < YEAR_IN_SECONDS && $diff >= MONTH_IN_SECONDS) {
        $months = round($diff / MONTH_IN_SECONDS);
        if ($months <= 1) {
            $months = 1;
        }
        /* translators: Time difference between two dates, in months. %s: Number of months */

        if ($months) {
            $since = __(':num months', [ 'num' => $months ]);
        } else {
            $since = __(':num month', [ 'num' => $months ]);
        }
    } elseif ($diff >= YEAR_IN_SECONDS) {
        $years = round($diff / YEAR_IN_SECONDS);
        if ($years <= 1) {
            $years = 1;
        }
        /* translators: Time difference between two dates, in years. %s: Number of years */
        if ($years) {
            $since = __(':num years', [ 'num' => $years ]);
        } else {
            $since = __(':num year', [ 'num' => $years ]);
        }
    }

    return $since;
}

function is_api()
{
    return request()->segment(1) == 'api';
}

function format_interval($d1, $d2 = '')
{
    $first_date = new DateTime($d1);
    if (!empty($d2)) {
        $second_date = new DateTime($d2);
    } else {
        $second_date = new DateTime();
    }


    $interval = $first_date->diff($second_date);

    $result = "";
    if ($interval->y) {
        $result .= $interval->format("%y years ");
    }
    if ($interval->m) {
        $result .= $interval->format("%m months ");
    }
    if ($interval->d) {
        $result .= $interval->format("%d days ");
    }
    if ($interval->h) {
        $result .= $interval->format("%h hours ");
    }
    if ($interval->i) {
        $result .= $interval->format("%i minutes ");
    }
    if ($interval->s) {
        $result .= $interval->format("%s seconds ");
    }

    return $result;
}

function getRoute($path, $parameters = [])
{
    $route = [
        1 => 'admin.orderManage',
        2 => 'PM',
        3 => 'BM',
        4 => 'WMS'
    ];

    return route("{$route[auth()->user()->role_id]}.$path", $parameters);
}

function isDocExists($url = null)
{
    if (empty($url)) return false;
    $headers = @get_headers($url);
    if ($headers === false) return false;
    return strpos(($headers[0] ?? false), '200') !== false;
}
if (!function_exists('isActive')) {

    function isActive(...$routeName)
    {
        return request()->routeIs(...$routeName);
    }
}

if (!function_exists('generateHtmlList')) {
    /**
     * Generate an HTML list from an associative array, converting keys to title case.
     *
     * @param array $items Associative array of items
     * @return string HTML content
     */
    function generateHtmlList(array $items)
    {
        $html = '<ul>';
        foreach ( $items as $key => $value ) {
            $formattedKey = ucwords(str_replace('_', ' ', $key));
            $html .= "<li><strong>{$formattedKey}:</strong> {$value}</li>";
        }
        $html .= '</ul>';
        return $html;
    }
}

function canViewAny(array $children)
{
    $permissions = array_map(fn($permission) => $children["menu"] . "-" . $permission, $children["permission"]);
    return !auth()->user()->hasAnyPermission($permissions);
}

function pre($data)
{
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}

function generateDataSet($data)
{
    $str = "";
    foreach ( $data as $key => $value ) {
        if (is_array($value)) {
            $str .= " data-{$key}=" . htmlspecialchars(json_encode($value), ENT_QUOTES, 'UTF-8');
        } else {
            $str .= " data-{$key}=" . htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
    }
    return $str;

}
if (!function_exists('findOrCreate')) {
    /**
     * Get the ID of the model if it exists, or create a new model instance and return its ID.
     *
     * @param string $modelClass
     * @param string $field
     * @param mixed $value
     * @return int
     */
    function findOrCreate($modelClass, $field, $value)
    {
        if (!is_numeric($value)) {
            $model = $modelClass::where($field, $value)->first();
            if (!$model) {
                return $modelClass::insertGetId([
                    $field       => $value,
                    "created_by" => auth()->id(),
                    'branch_id'  => session('branch_id')
                ]);
            } else {
                return $model->id;
            }
        }
        return $value;
    }
}



function TempEntry(array $data = [])
{
    return \Illuminate\Support\Facades\DB::table('temp')->insertGetId([
        'res' => json_encode($data),
    ]);
}
