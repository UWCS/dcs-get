<?php
//usage: php stats.php [/path/to/packages.json [/path/to/output.json]]
if(isset($argv[1]))
{
	$packages_file = $argv[1];
}
else
{
	$packages_file = 'packages.json';
}
if(isset($argv[2]))
{
	$result_file = $argv[2];
}
else
{
	$result_file = 'stats.json';
}
//exec('zgrep -oh "dcs-get\/packages\/.*\.tar\.gz" /var/log/apache2/access.log* | sed "s/dcs-get\/packages\///" | sort | uniq -c | sort -nr', $response);
exec('zgrep -oh "dcs-get\/packages\/.*\.tar\.gz" /var/log/apache2/access.log* | sed "s/dcs-get\/packages\///"', $response);

$installs = array();

foreach($response as $key=>$value)
{
	if(isset($installs[$value]))
	{
		$installs[$value] += 1;
	}
	else
	{
		$installs[$value] = 1;
	}
}

$packages = json_decode(file_get_contents($packages_file), true);
$result = array();

foreach($packages as $name=>$value)
{
	$total = 0;
	$versions = $value['version'];
	foreach($versions as $version)
	{
		$fullname = $name . '-' . $version . '.tar.gz';
		if(isset($installs[$fullname]))
		{
			$result[$name][$version] = $installs[$fullname];
			$total += $installs[$fullname];
		}
		else
		{
			$result[$name][$version] = 0;
		}
	}
	$result[$name]['total'] = $total;
}

uasort($result, 'custom_sort');
function custom_sort($a,$b){return $a['total']<$b['total'];}

//commented out because php doesn't support pretty print until version 5.4
//file_put_contents($result_file, json_encode($result, JSON_PRETTY_PRINT));
file_put_contents($result_file, indent(json_encode($result)));
chmod($result_file, 0644);


function indent($json) {

	$result      = '';
	$pos         = 0;
	$strLen      = strlen($json);
	$indentStr   = "\t";
	$newLine     = "\n";
	$prevChar    = '';
	$outOfQuotes = true;

	for ($i=0; $i<=$strLen; $i++) {

		// Grab the next character in the string.
		$char = substr($json, $i, 1);

		// Are we inside a quoted string?
		if ($char == '"' && $prevChar != '\\') {
			$outOfQuotes = !$outOfQuotes;

			// If this character is the end of an element, 
			// output a new line and indent the next line.
		} else if(($char == '}' || $char == ']') && $outOfQuotes) {
			$result .= $newLine;
			$pos --;
			for ($j=0; $j<$pos; $j++) {
				$result .= $indentStr;
			}
		}

		// Add the character to the result string.
		$result .= $char;

		// If the last character was the beginning of an element, 
		// output a new line and indent the next line.
		if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
			$result .= $newLine;
			if ($char == '{' || $char == '[') {
				$pos ++;
			}

			for ($j = 0; $j < $pos; $j++) {
				$result .= $indentStr;
			}
		}

		$prevChar = $char;
	}

	return $result;
}

