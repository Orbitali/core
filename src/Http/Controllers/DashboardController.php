<?php

namespace Orbitali\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private $floatingKeys = ["", "K", "M"];

    public function index(Request $request)
    {
        [$minTime, $maxTime, $selector] = $this->getRange($request);      
        $listRange = $this->listRange();  
        $query = DB::select(
            "SELECT
        COUNT(*) as `view`,
        JSON_UNQUOTE(JSON_EXTRACT(headers, '$.opanel-track[0]')) AS `session`,
        DATE(from_unixtime(`time`)) AS `date`
    FROM
        `laravel`.`clockwork`
    WHERE
        `type` = 'request'
        AND DATE(from_unixtime(`time`)) BETWEEN '" . $minTime . "' AND '" . $maxTime . "'
        AND JSON_SEARCH(middleware,'all','can:panel.dashboard.view') IS NULL
    GROUP BY
        `date`,
        `session`
    ORDER BY
        `date` DESC;"
        );
        $result = collect($query);
        $pageViews = $result->sum("view");
        $visitors = $result->count("session");

        $part = (int) log($pageViews, 1000);
        if ($part > 0) {
            $pageViews = $pageViews / pow(1000, $part);
            $pageViews =
                number_format($pageViews, 2) . $this->floatingKeys[$part];
        }
        return view(
            "Orbitali::dashboard.index",
            compact("pageViews", "visitors","selector","listRange")
        );
    }

    private function listRange(){
        return [
            "last30D" => "Last 30 days",
            "thisWeek" => "This Week",
            "prevWeek" => "Previous Week",
            "thisMonth" => "This Month",
            "prevMonth" => "Previous Month",
        ];
    }

    private function getRange(Request $request){
        $user = auth()->user();
        $user->dashboard_range = $request->get("range",$user->dashboard_range);
        $user->save();
        switch($user->dashboard_range){
            case "last30D":
            default:
                //Last 30 Day
                return [
                    now("utc")->addDays(-30)->format("Y-m-d"),
                    now("utc")->format("Y-m-d"),
                    "last30D"
                ];

            case "thisWeek":
                //This Week
                return [
                    now("utc")->startOfWeek()->format("Y-m-d"),
                    now("utc")->format("Y-m-d"),
                    "thisWeek"
                ];

            case "prevWeek":
                //Previous Week
                return [
                    now("utc")->startOfWeek()->addDays(-7)->format("Y-m-d"),
                    now("utc")->endOfWeek()->addDays(-7)->format("Y-m-d"),
                    "prevWeek"
                ];

            case "thisMonth":
                //This Month
                return [
                    now("utc")->firstOfMonth()->format("Y-m-d"),
                    now("utc")->format("Y-m-d"),
                    "thisMonth" 
                ];

            case "prevMonth":
                //Previous Month
                return [
                    now("utc")->firstOfMonth()->addMonths(-1)->format("Y-m-d"),
                    now("utc")->firstOfMonth()->addDays(-1)->format("Y-m-d"),
                    "prevMonth" 
                ];
        }
    }
}
