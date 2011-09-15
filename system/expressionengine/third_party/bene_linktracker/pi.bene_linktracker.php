<?php

$plugin_info = array(
	'pi_name'		=> 'Bene LinkTracker',
	'pi_version'		=> '1.0',
	'pi_author'		=> 'Bene.be - Tim Bertens',
	'pi_author_url'		=> 'http://www.bene.be/',
	'pi_description'	=> 'Track events on your site in Google Analytics easily',
	'pi_usage'		=> bene_linktracker::usage()
);

class Bene_linktracker {

	var $text = "";
	var $target = "";
	var $url = "";
	var $title = "";
	var $class = "";
        var $track= "";
        var $label = "";
        var $string = "";
        var $javascript = "";
        var $category = "Unknown";
        var $action = "Unknown";

	
	function Bene_linktracker()
	{
		$this->EE =& get_instance();

		
		// Fetch parameters
                // URL + tidying up of the URL
                $url = $this->EE->TMPL->fetch_param('url');
                //$url = strtolower($url);
                if (0 !== stripos($url, 'http://') && 0 !== stripos($url, 'https://') &&
                        0 !== stripos($url, 'mailto:') && 0 !== stripos($url, 'ftp://') &&
                        0 !== stripos($url, 'svn://') && 0 !== stripos($url, 'sftp://') &&
                        0 !== stripos($url, 'ftps://') && 0 !== stripos($url, 'ssh://')) {
                    $url = 'http://' . $url;
                       }
                     
                

                $track = $this->EE->TMPL->fetch_param('track', 'yes');
                
                // determine the protocal of the link
                $urlsplit = explode(":", strtolower($url));
                $urlprotocol= $urlsplit[0];

                switch($urlprotocol) {
                    case "http":
                        $prefixtostrip = 7;
                        break;
                    case "https":
                        $prefixtostrip = 8;
                        break;
                    case "mailto":
                        $prefixtostrip = 7;
                        break;
                    case "ftp":
                        $prefixtostrip = 6;
                        break;
                    default:
                        $prefixtostrip = 0;

                }
                        
                // determine category of link
                switch($urlprotocol) {
                    case ("http"):
                    case ("https"):

                       $pp = pathinfo(strtolower($url));

                       if  (!isset($pp['extension']))
                       {
                         $urlextension = '';
                         }
                       else {
                        $urlextension = $pp['extension'];
                       }
                                             
                       if ($urlextension=="php" || $urlextension=="html" || $urlextension=="xhtml" || $urlextension=="" || $urlextension=="asp" ||
                               $urlextension=="aspx" || $urlextension=="jsp" || $urlextension=="htm" ||  $urlextension=="cfm" ||  $urlextension=="shtml"
                               ||  $urlextension=="cgi") {
                           $category = "Website";
                       } 
                       else
                       {
                            $category = "Download";
                       }
                       
                       break;
                    case "mailto":
                       $category = "Mail";
                       $action = "Click";
                       break;
                    default :
                       $category = "Download";
                       break;

                }

                            

                // determine if link goes to local or external site
                if ($urlprotocol != 'mailto') {
                        $urlsplit = explode("/", $url);
                        $urldomain= $urlsplit[2];
                        if ($urldomain == $_SERVER["SERVER_NAME"]) {
                            //link goes to local domain
                            $action = 'Internal';
                            $prefixtostrip += strlen($urldomain);
                        }
                        else
                        {
                            //link goes to external domain
                            $action = 'External';
                        }                    
                }


                // Creating a structural goalname automatically when not supplied manually
                $label = $this->EE->db->escape_str($this->EE->TMPL->fetch_param('label'));
                if ($label == '') {
                    $label =  substr($url, $prefixtostrip);
                }
 
  
                // Get the text of the link 
                $text = $this->EE->TMPL->fetch_param('text', $url);
                $javascript = "";

                // Get the target of the link
                $target = $this->EE->TMPL->fetch_param('target');
                if ($target != '') {
                    $target = "target='" . $target . "'"  ;
                }

                // Get the title of the link
                $title = $this->EE->db->escape_str($this->EE->TMPL->fetch_param('title'));
                if($title != '') {
                    $title = "title='" .$title . "'";
                }




                // Set the javascript to track the event, based on category, action and label
                if ($track == 'yes') {

                    $javascript = " onClick=\"javascript:_gaq.push(['_trackEvent', '" . $category . "', '" . $action . "', '" . $label . "']);\"";

                }

                // build the string to return
                $string = "<a href='" . $url . "'" . $javascript . " " . $target . " " . $title . ">" .  $text . "</a>";
                $this->return_data = $string;


	

	
	}
	

// ----------------------------------------
//  Plugin Usage
// ----------------------------------------

// This function describes how the plugin is used.
//  Make sure and use output buffering

function usage()
{
ob_start(); 
?>
HOW TO USE:

1. Use the latest Google Analytics javascript
-----------------------------------------------------
First of all make sure you use the latest version of the Google Analytics tracking javascript that allows asynchronic event tracking, for more details see:
http://code.google.com/apis/analytics/docs/tracking/asyncTracking.html
You can easily find the code required for your website in your Google Analytics profile.  Remember that you should place the javascript on every page that
needs to track events.  It should be positioned just before the closing </head> - tag

Please note that Google Analytics tracks about 500 events per session, so if users generate more events you track than they get ignored

2. Tag your links so they get tracked as events
-----------------------------------------------------
This add-ons is here to help you with creating more complex HTML links tags without having to deal with the complexity of it.  Features that are supported:
- Standard Parameters
    - url = the url to link to
    - target = the target of the link (values: blank, parent, self, top or [framename])
    - text = the name of the link
    - title = the title of the link (shown on hover over)
- Google Analytics Goal tracking:
    - track = (default is "yes").  If parameter not explicitely set to "No", Google Analytics code will be added to track the click on this link as goal,
                    url of the link will be used as name of the goal.
    - label = Optionally you can specify a label of the event that needs to appear e.g. label='Click on www.expressionengine.com link'.  If not set a standard set of rules whil be used
                    to determine the label. The logic has the ability to differentiate link to own site, links to external site, links to email and others


3. View your reports in your Google Analytics account
-----------------------------------------------------
After a day of usage you can track the results in your Google Analytics account under 'Content' > 'Events'

EXAMPLE:

{exp:bene_linktracker url="http://www.bene.be" target='_blank' track='yes' text='Visit us' title="Go to our website"}

RELEASE NOTES:

1.0 - Initial Release.

For updates and support, go to our website on : http://www.bene.be/blog/Track_Google_Analytics_events_on_your_ExpressionEngine_website_easily


<?php
$buffer = ob_get_contents();
	
ob_end_clean(); 

return $buffer;
}


}
?>