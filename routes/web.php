<?php

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Route;

Route::get('cekpengunjung', function () {

    return json();
});

 function json()
{
    $zoneId = env('CLOUDFLARE_ZONE_ID');
    $start = now()->subDays(7)->format('Y-m-d');
    $end = now()->format('Y-m-d');

    $query = "
    {
      viewer {
        zones(filter: { zoneTag: \"$zoneId\" }) {

          totals: httpRequests1dGroups(
            limit: 7
            orderBy: [date_DESC]
            filter: {
              date_geq: \"$start\",
              date_leq: \"$end\"
            }
          ) {
            dimensions { date }
            sum { requests bytes }
          }

          countries: httpRequests1dGroups(
            limit: 5
            orderBy: [sum_requests_DESC]
            filter: {
              date_geq: \"$start\",
              date_leq: \"$end\"
            }
          ) {
            dimensions { clientCountryName }
            sum { requests }
          }

          devices: httpRequests1dGroups(
            limit: 5
            orderBy: [sum_requests_DESC]
            filter: {
              date_geq: \"$start\",
              date_leq: \"$end\"
            }
          ) {
            dimensions { deviceType }
            sum { requests }
          }

        }
      }
    }";

    $response = Http::withToken(env('CLOUDFLARE_API_TOKEN'))
        ->post('https://api.cloudflare.com/client/v4/graphql', [
            'query' => $query
        ]);

    return response()->json([
        'status' => $response->successful(),
        'raw' => $response->json()
    ]);
}
function analdytics()
{
    $query = '
    {
      viewer {
        zones(filter: { zoneTag: "67d5af1a497d25df871ba866e20ad874" }) {
          httpRequests1dGroups(limit: 7, orderBy: [date_DESC]) {
            dimensions { date }
            sum { requests bytes }
          }
        }
      }
    }';

    $response = Http::withToken("9FSqTtVng_omG_9C6dJzn2TBanLz_hEYrwvqrs3i")
        ->post('https://api.cloudflare.com/client/v4/graphql', [
            'query' => $query
        ]);

    return $response->json();
}