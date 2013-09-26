<?php
error_reporting(E_ALL);
ini_set('display_errors',1);

// authorized API access for a dummy project that can only be requested from [anon.]csug.rochester.edu (128.151.69.98)
$uct_callink_key = 'AIzaSyB6xPrZcyxXHdWvXrx3GUWeEGczw42YdLQ';
// calendar to read
$uct_cal_id = '04lnqg1jsbtupnkq09esf5ccpo@group.calendar.google.com';
// APC cached variable
$uct_apc = 'ur-csug-tutoring:callink';

// API request constructions
// base URL
$api_base = 'https://www.googleapis.com/calendar/v3/';
// request for events in a week
$api_events_list = "calendars/$uct_cal_id/events?";
// request for events in a week: GET params
$api_events_list_query =
    array(
        'key'=>$uct_callink_key,
        'maxResults'=>50,
        'singleEvents'=>'true',
        'orderBy'=>'startTime',
        //set 'timeMin' and 'timeMax'
    );

function curl_get($url, $query_object = array()) {
    $url .= http_build_query($query_object);
    $url = str_replace('%3A', ':', $url);
    $url = str_replace('%25', '', $url);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $json = curl_exec($ch);
    return json_decode($json, true);
}

$now = time();
if (file_exists('tutors-cache.txt') && $now - filemtime('tutors-cache.txt') < 900) {
    $tutoring_list = file_get_contents('tutors-cache.txt');
} else {
    $tutoring_list = '';

    $api_events_list_query['timeMin'] = date('%c', strtotime('last sunday'));
    $api_events_list_query['timeMax'] = date('%c', strtotime('next sunday'));
    $events_this_week = curl_get($api_base.$api_events_list, $api_events_list_query);
    if (isset($events_this_week['items'])) {
        $tutoring_list = '<table cellpadding="10">';
        foreach ($events_this_week['items'] as $event) {
            $tutor = $event['summary'];
            $start = strtotime($event['start']['dateTime']);
            $end = strtotime($event['end']['dateTime']);
            $location = $event['location'];
            $bg_color = 'transparent';
            $fg_color = 'black';
            $fg_ital = 'normal';
            if ($now > $end) {
                $style_as = 'past';
                $fg_color = '#c0c0c0';
                $fg_ital = 'italic';
            } else if ($now > $start) {
                $style_as = 'now';
                $bg_color=  '#ffff00';
            } else if ($now > strtotime('today', $start)) {
                $style_as = 'today';
                $bg_color = '#ffffc0';
            } else {
                $style_as = 'future';
            }
            $tutoring_list .= "<tr style=\"color:$fg_color;background-color:$bg_color;font-style:$fg_ital;\"><td>".date('l', $start).' at '.date('g:i A', $start).' - '.date('g:i A', $end)." in $location ($style_as)</td><td style=\"font-weight:bold;\">$tutor</tr>";
        }
        $tutoring_list .= '</table>';
    } else {
        $tutoring_list = '<pre>Error getting tutor list:'."\n".var_dump($events_this_week).'</pre>';
    }

    file_put_contents('tutors-cache.txt', $tutoring_list);
}

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Rochester CSUG Tutoring</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!-- styles -->
        <link href="assets/css/bootstrap.css" rel="stylesheet">
        <style type="text/css">
            body {
                padding-top: 60px;
                padding-bottom: 40px;
            }
        </style>
        <link href="assets/css/bootstrap-responsive.css" rel="stylesheet">

        <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
        <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->

        <link rel="shortcut icon" href="assets/ico/favicon.ico">

        <script type="text/javascript">
            var _gaq = _gaq || [];
            _gaq.push(['_setAccount', 'UA-34732780-1']);
            _gaq.push(['_trackPageview']);
        </script>
        <script type="text/javascript">
            // adapted from http://stackoverflow.com/a/819455/835995
            function doFit(element) {
                var newheight = null;
                if (element) {
                    newheight = (element.contentWindow.document.body.clientHeight ||
                                element.contentWindow.document.body.scrollHeight) || null;
                }
                if (newheight) {
                    element.height = (newheight) + "px";
                }
            }
        </script>
    </head>

    <body>
        <div class="navbar navbar-inverse navbar-fixed-top">
            <div class="navbar-inner">
                <div class="container">
                    <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </a>
                    <a class="brand" href="index.html#">University of Rochester CSUG Tutoring</a>
                    <!-- unnecessary self-link <div class="nav-collapse collapse">
                        <ul class="nav">
                            <li class="active"><a href="index.html#">Home</a></li>
                        </ul>
                    </div>--><!--/.nav-collapse -->
                </div>
            </div>
        </div>

        <div class="container">
            <div class="hero-unit">
                <h1>Need CS help?</h1>
                <p>You're in the right place. CSUG offers free tutoring for all CS courses.</p>
                <p>Tutoring sessions this week:</p>
                <?php echo $tutoring_list; ?>
                <p><a class="btn btn-primary btn-large" href="https://www.google.com/calendar/embed?src=04lnqg1jsbtupnkq09esf5ccpo%40group.calendar.google.com&amp;ctz=America/New_York" onClick="_gaq.push(['_trackEvent', 'Followup', 'Schedule']);">See full schedule &raquo;</a></p>
            </div>

            <div class="row">
                <div class="span6">
                    <h2>Who we are</h2>
                    <p>We're a bunch of volunteer computer science majors looking to provide help to those who need it.  Feel free to drop by and say hello.</p>
                </div>
                <div class="span6">
                    <h2>What we do</h2>
                    <p>We provide tutoring Monday through Fridays during the school year.  If you're having difficulty with homework, you're stuck on a project, or you need a second pair of eyes, please stop by.</p>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="span6">
                    <h2>Find us</h2>
                    <p>You'll usually be able to find one of us in <b>Hylan 301</b> (the non-major's computer science lab) on weekdays in the late morning or afternoon.  See our schedule for more details or changes in our location.</p>
                </div>
                <div class="span6">
                    <h2>Contact us</h2>
                <p>Can't find the room?  Do you have a comment or suggestion? Unable to attend any of the times? Want to become a tutor?</p>
                    <p><a class="btn" href="mailto:csug-tutoring@googlegroups.com">Send us an email &raquo;</a></p>
                </div>
            </div>


        </div> <!-- /container -->

        <!-- javascript
        ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->
        <!--script src="assets/js/jquery.js"></script>
        <script src="assets/js/bootstrap-transition.js"></script>
        <script src="assets/js/bootstrap-alert.js"></script>
        <script src="assets/js/bootstrap-modal.js"></script>
        <script src="assets/js/bootstrap-dropdown.js"></script>
        <script src="assets/js/bootstrap-scrollspy.js"></script>
        <script src="assets/js/bootstrap-tab.js"></script>
        <script src="assets/js/bootstrap-tooltip.js"></script>
        <script src="assets/js/bootstrap-popover.js"></script>
        <script src="assets/js/bootstrap-button.js"></script>
        <script src="assets/js/bootstrap-collapse.js"></script>
        <script src="assets/js/bootstrap-carousel.js"></script>
        <script src="assets/js/bootstrap-typeahead.js"></script-->

        <!--script type="text/javascript">
            (function() {
             var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
             ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
             var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
             })();
        </script-->
     </body>
 </html>

