<?php
/**
 * Description: 验证item
 */
require_once __DIR__ . '/lib/common.inc.php';

$lNeedValid = array(
	//'sgnpayment' => 'sgnpayment',
	'balloon_reward' => 'balloon_reward',
	'calendar' => 'calendar',
    'cook_activity_step' => 'cook_activity_step',
    'gifts' => 'gifts',
);

echo "\n";
echo "======= Valid Config =======\n\n";

$bError = false;

foreach($lNeedValid as $sJson => $sFile) {

	// data
	$sDataFile = CONF_PATH . $sFile . '.php';
	Ss_Util::CheckSyntax($sDataFile);
	$data = require $sDataFile;
	$data = json_decode(json_encode($data));

	// schema
	$retriever = new JsonSchema\Uri\UriRetriever;
	$schema = $retriever->retrieve('file://' . __DIR__ . '/schema/' . $sJson . '.json');
	$refResolver = new JsonSchema\RefResolver($retriever);
	$refResolver->resolve($schema, 'file://' . __DIR__ . '/schema/' . $sJson . '/', __DIR__ . '/function/');

	// Validate
	$validator = new JsonSchema\Validator();
	$validator->check($data, $schema);

	if ($validator->isValid()) {
		echo "    {$sFile} is OK\n";
	} else {
		$bError = true;
		echo "\n======================================== ERROR =======================================\n\n";
		echo "    {$sFile}.php have some error:\n\n";
		foreach ($validator->getErrors() as $error) {
			echo sprintf("[%s] %s\n", $error['property'], $error['message']);
		}
		echo "\n======================================== ERROR =======================================\n\n";
	}

	unset($schema);
	unset($data);
	unset($validator);
	unset($retriever);
	unset($refResolver);
}

echo "\n";

if($bError) {
	//if(Config::isQa()) {
	//	exit;
	//}
	echo "====== Have Some Error =====\n\n";
}else{
	echo "======== Success! ==========\n\n";
}
