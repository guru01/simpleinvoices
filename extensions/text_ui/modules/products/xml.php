<?php

header("Content-type: text/xml");

$dir = (isset($_POST['sortorder'])) ? $_POST['sortorder'] : "ASC" ;
$sort = (isset($_POST['sortname'])) ? $_POST['sortname'] : "description" ;
$rp = (isset($_POST['rp'])) ? $_POST['rp'] : "25" ;
$page = (isset($_GET['page'])) ? $_GET['page'] : "1" ;

$domain_id = domain_id::get();

//SC: Safety checking values that will be directly subbed in
if (intval($page) != $page) {
	$start = 0;
}
if (intval($rp) != $rp) {
	$rp = 25;
}
if (!preg_match('/^(asc|desc)$/iD', $dir)) {
	$dir = 'ASC';
}

/*SQL Limit - start*/
$start = (($page-1) * $rp);
$limit = "LIMIT $start, $rp";
/*SQL Limit - end*/

$query = $_POST['query'];
$qtype = $_POST['qtype'];

$where = "";
if ($query) $where = " AND $qtype LIKE '%$query%' ";


/*Check that the sort field is OK*/
$validFields = array('id', 'description','customer_id');

if (in_array($sort, $validFields)) {
	$sort = $sort;
} else {
	$sort = "id";
}

	$sql = "SELECT 
				id, 
				description,
				unit_price,
				(SELECT (CASE  WHEN enabled = 0 THEN '".$LANG['disabled']."' ELSE '".$LANG['enabled']."' END )) AS enabled
			FROM 
				".TB_PREFIX."products  
			WHERE 
				domain_id = :domain_id
			AND visible = 1
				$where
			ORDER BY 
				$sort $dir 
			$limit";
				
	$sth = dbQuery($sql, ':domain_id', $domain_id);
	$customers = $sth->fetchAll(PDO::FETCH_ASSOC);

$sqlTotal = "SELECT count(id) AS count FROM ".TB_PREFIX."products WHERE domain_id = :domain_id AND visible =1";
$tth = dbQuery($sqlTotal, ':domain_id', $domain_id);
$resultCount = $tth->fetch();
$count = $resultCount[0];
//echo sql2xml($customers, $count);
$xml .= "<rows>";

$xml .= "<page>$page</page>";

$xml .= "<total>$count</total>";

foreach ($customers as $row) {

	$xml .= "<row id='".$row['id']."'>";
	$xml .= "<action><![CDATA[<a href='index.php?module=products&view=details&action=view&id=".$row['id']."'>".$row['id']."</a>]]></action>";
	$xml .= "<description><![CDATA[".utf8_encode($row['description'])."]]></description>";
	$xml .= "<unit_price><![CDATA[".utf8_encode($row['unit_price'])."]]></unit_price>";
	$xml .= "</row>";		
}

$xml .= "</rows>";

echo $xml;

?> 
