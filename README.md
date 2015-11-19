# PHP-classless-request

- required PHP's cURL lib.
- low memory-consumption, quick, no overhead, no class implementation, zero outside dependencies.
- code can be easily forked and modified.
- will suits most needs (`GET/HEAD`)
- collect and parsed all raw data to easy to use associative arrays (can be easily converted to JSON to be used in JavaScript) information is collected (and parsed) of the statistics, request, response, headers and body.
- supports HTTP 30x/Location redirects, and collect all data from redirection.
