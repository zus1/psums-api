<h3>About</h3>
Psums project is created to analyze lorem ipsum stream by applying pre defined rules<br>
It is composed of 3 services<br><br>
<ol>
    <li><a href="https://github.com/zus1/psums_aggregator">Aggregator</a></li>
    <li><a href="https://github.com/zus1/psums_streams">Streams</a></li>
    <li><a href="https://github.com/zus1/psums-api">Api</a></li>
</ol>

<h3>Installation for sums project</h3>
Sums can be installed in two different ways

First is to pull all three services and put them in same directory (each service in its own directory
and then all put in same parent directory). Then go to Aggregator directory and run
<pre><code>docker-compose up</code></pre>
This will build up all containers, and those are following:
<ul>
    <li>psums_aggregator</li>
    <li>psums_streams</li>
    <li>psums_api</li>
    <li>psums_mysql</li>
    <li>psums_memcached</li>
</ul>
After build process i completed (may take a few minutes, depending if you have some images built already)
it necessary to bash into aggregator container and run migrations. Psums uses Phinx as migration engine.
<br><br>
<pre><code>docker container exec -it bash psums_aggregator</code></pre>
Yous should be in /var/www/html direcotry, now run migrations
<br><br>
<pre><code>php library/phinx/bin/phinx migrate</code></pre>
Now you should be all set up
<br><br>

Second way is by using <a href="https://github.com/zus1/psums_compose">Psums composer</a>, you can follow instalation instructions on that repository. 
It will install production version without access to code base

<h3>Api Docs</h3>
This is api that generates reports from lorem ipsum streams analysis, done by aggregator service. Api behaves as RESTful Api
And returns resources for each triggered endpoint. <br><br>

For authorization Auth header needs to be sent with request
<pre><code>Auth: 11111-11111-11111-11111-11111-11111</code></pre>
Key will be provided manually (aka by email)<br><br>

<b>Following can be requested</b>
<br><br>
Endpoint:<br>
<pre><code>http://localhost:8082/report/available/streams</code></pre>
Response example
<pre><code>
{
    "error": 0,
     available_streams": [
       {
           "stream_id": "1a2b3c4d",
           "name": "asdfast"
       },
       {
          "stream_id": "2a3b5c7d",
          "name": "baconipsum"
       },
       {
          "stream_id": "d34tz671",
          "name": "hipsum"
       },
       {
          "stream_id": "1db56725",
          "name": "metaphorpsum"
       }
   ]
}</code></pre>
This endpoint will return all available streams that can be used in other requests, with their ids and names
No parameters required<br><br>

Endpoint 
<pre><code>http://localhost:8082/report/stream/available</code></pre>
Parameters
<pre><code>string stream_id (required)</code></pre>
Response example
<pre><code>
    {
        "error": 0,
        "stream_id": "1a2b3c4d",
        "available_rules": [
            {
                "stream_id": "1db56725",
                "rule_id": "4",
                "rule_name": "pattern",
                "description": "Uses provided patter to check occurrence of symbols in both streams, and compare"
            },
            {
                "stream_id": "2a3b5c7d",
                "rule_id": "5",
                "rule_name": "match_making",
                "description": "For all words pairs in pattern, checks both steams and tries to first word in first stream and second word in second stream"
            },
            {
                "stream_id": "1db56725",
                "rule_id": "1",
                "rule_name": "compare_vowels",
                "description": "This rule counts number of vowels in words and makes comparison between streams"
            }
        ]
    }
</code></pre>
This endpoint will return rules available for provided stream_id, along with their description and id for stream each rulw
is applied, in combination with
<br><br>

Endpoint
<pre><code>http://localhost:8082/report/stream/report</code></pre>
Parameters
<pre><code>
string stream_one (required)
string stream_two (optional)
int rule_id (optional) 
</code></pre>
Response example
<pre><code>
{
    "error": 0,
    "stream_id": "1a2b3c4d",
    "results": [
        {
            "stream_id": "1db56725",
            "rule": "compare_vowels",
            "rule_id": "1",
            "report": {
                "total_first_stream": 1143,
                "total_second_stream": 866,
                "percentage": 1.32
            }
        },
        {
            "stream_id": "1db56725",
            "rule": "pattern",
            "rule_id": "4",
            "report": {
                "total_first_stream": 2,
                "total_second_stream": 0,
                "percentage": 2
            }
        }
    ]
}
</code></pre>
This endpoint returns one of the following:
<ol>
    <li>All combined streams with rules results if for stream_one, if only stream_one is provided</li>
    <li>All rules applied for stream_one and stream_two if both streams are provided</li>
    <li>Report for stream_one, stream_two and specific rule, if rule id is provided</li>
</ol>

If error occurred response will have following format
<pre><code>
{
    "error": 1,
    "message": "Api key missing"
}
</code></pre>
And status will be indicated by response status.<br><br>
There is no rate limit for now.<br><br>
That is all from api service. Looking for more wisdom? Check out repositories for other services, and have fun :) 