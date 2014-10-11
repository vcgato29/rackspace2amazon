<?php
$apiKey="YOUR_API_KEY";
$username="YOUR_USER_NAME";
$acId="YOUR_ACCOUNT_ID";   //top right of rackspace.com

//enter the domain name IDs you want to get
//these IDs can be found on the DNS page (hover over your domain name)
$dnIds=["000001","000002",...,"000011"];

//
//You shouldn't need to edit anything below this line
//

$contents="
<?xml version=\"1.0\" encoding=\"UTF-8\"?>

<credentials xmlns=\"http://docs.rackspacecloud.com/auth/api/v1.1\"

 username=\"$username\"

 key=\"$apiKey\"/>
";
file_put_contents("auth.xml", $contents);




foreach($dnIds as $id){
	echo "Getting new token...\n";
	$cmd="curl -s -X POST -d @auth.xml -H \"Content-Type: application/xml\" -H \"Accept: application/xml\" https://auth.api.rackspacecloud.com/v1.1/auth";
	$result=`$cmd`;
	$p0 = xml_parser_create();
        xml_parse_into_struct($p0, $result, $vals0, $index0);
        xml_parser_free($p0);	
	$token="";
	foreach($vals0 as $val){
		if($val['tag']=="TOKEN"){
			$token=$val['attributes']['ID'];
		}
	}
	echo "Token: $token\n";
	sleep(1);
	

	
	echo "Getting job id site id ".$id."\n";
	$cmd="curl -H \"X-Auth-Token: $token\" -s -H \"Accept: application/xml\" https://dns.api.rackspacecloud.com/v1.0/$acId/domains/$id/export";
	//echo $cmd."\n";
	$result=`$cmd`;
	//echo $result."\n";
	$p = xml_parser_create();
	xml_parse_into_struct($p, $result, $vals, $index);
	xml_parser_free($p);
	//var_dump($vals);
	
	$jobId="";
	foreach($vals as $val){
		//var_dump($val);
		//exit();
		if($val['tag']=="NS2:JOBID"){
			$jobId=$val['value'];
			break;
		}
	}
	echo "Job id: ".$jobId."\n";
	sleep(1);



	$cmd="curl -s -H \"X-Auth-Token: $token\" -H \"Accept: application/xml\" https://dns.api.rackspacecloud.com/v1.0/$acId/status/$jobId?showDetails=true";
	//echo $cmd."\n";
	$result=`$cmd`;
	//echo $result."\n";
		
	$jobId2="";
	$p2 = xml_parser_create();
        xml_parse_into_struct($p2, $result, $vals2, $index2);
        xml_parser_free($p2);
	//var_dump($vals2);
	foreach($vals2 as $val){
		if($val['tag']=="NS2:CONTENTS"){
			echo $val['value']."\n";
			break;
		}
	}
	echo "\n\n\n\n";
	sleep(5);
}

/*
See also: https://community.rackspace.com/products/f/25/t/3264
*/
?>
