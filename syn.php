#!/usr/bin/php
<?php
#SYN:xmatec00

//premenne
$format = 0;
$input = STDIN;
$output = STDOUT;
$STDERR = fopen('php://stderr', 'w+');
$br = false;

$regExp = NULL;
$indexArray[0][0] = 0;
$index = 0;
$i  = 0;
$iOld = 0;

/**
 * vypis napovedy
 */
function help(){

echo "NAPOVEDA:\n";
echo "Skript pracuje s tabulkou regularnich vyrazu, ke kterym prirazuje pozadovane vystupni formatovani.\n";
echo "Argumenty:\n";
echo "--help\t\t\tnapoveda\n";
echo "--format=filename\turceni formatovaciho suboru\n";
echo "--input=filename\turceni vstpniho suboru\n";
echo "--output=filename\turceni vystupniho suboru\n";
echo "--br\tprida element <br /> na konec kazdeho radku\n";
}

/**
 * spracovanie argumentov
 */
function arguments($argc, $argv){
	
global $STDERR;
	
$longopts  = array(
    "help",     	// --help
    "format::",		// --format=nazovsuboru
    "input::",      // --input=nazovsuboru
    "output::",		// --output=nazovsuboru
    "br",			// --br
);
$options  = getopt("" , $longopts);

$pom = 1;

for ($x = 1; $x < ($argc - 1); $x++) {				
	for ($i = $x+1; $i < $argc; $i++){
		
		if ((strlen($argv[$x]) > 3) && (strlen($argv[$i]) > 3))
		{
			if ($argv[$x][2] == $argv[$i][2]){
				fwrite($STDERR, "Zle argumenty\n\n");
				exit(1);}
		}
		else {
		fwrite($STDERR, "Zle argumenty\n\n");
		exit(1);
		}
	}
}
					
//vypis napovedy
if ((strcmp($argv[1], "--help") == 0) && ($argc == 2))
	{
	$pom = $pom + 1;
	help();
	exit(0);
	}				
else 		//spracovanie argumentov
	{
		foreach ($options as $k => $v)
		{
			if ((strcmp($k,"format") == 0) && ($v != '')){			//otvorenie formatovacieho suboru
				$pom = $pom + 1;
				if (($GLOBALS["format"] = fopen($v, "r")) == FALSE) {
					fwrite($STDERR, "Nepodarilo sa otvorit formatovaci subor\n");
					exit(2);
					}
				}
			elseif ((strcmp($k,"input") == 0) && ($v != '')){		//otvorenie vstupneho suboru
				$pom = $pom + 1;
				if (($GLOBALS["input"] = fopen($v, "r")) == FALSE) {
					fwrite($STDERR, "Nepodarilo sa otvorit vstupny subor\n");
					exit(2);
					}
				}
			elseif ((strcmp($k,"output") == 0) && ($v != '')){		//otvorenie/vytvorenie vystupneho suboru
				$pom = $pom + 1;
				if (($GLOBALS["output"] = fopen($v, "w")) == FALSE) {
					fwrite($STDERR, "Nepodarilo sa otvorit vystupny subor\n");
					exit(3);
					}
				}	
			elseif (strcmp($argv[$pom],"--br") == 0){				//aktivacia "br"
				$pom = $pom + 1;
				$GLOBALS["br"] = true;
				}
		}
	}
	
if ($argc != $pom)
	{//pocet argumentov nevyhovuje
	fwrite($STDERR, "Zle argumenty\n\n");
	exit(1);
	}
}

/* * * * * * * * * * * *
 * hlavne telo skriptu *
 * * * * * * * * * * * */
if ($argc > 1)
	{
	arguments($argc, $argv);
	}
else{
	fwrite($STDERR, "Nezadane argumenty\n\n");
	exit(1);}	


/*
 * spracovanie formatovacieho suboru
 */
if ($format != 0){

	do{//preskocenie praznych riadkov
	$dataFormat = fgetcsv($format, 4096, ",", "\0");
	}while (($dataFormat[0] == NULL) && ($dataFormat != FALSE));

	while ((!feof($format)) || ($dataFormat != FALSE)){

		//kontrola formatovacieho zapisu a jeho rozparsovanie
		if (ereg ("^([^\t]+)\t+([a-zA-F0-9:]+)$", $dataFormat[0], $regFormat) == FALSE){
			fwrite($STDERR, "Chybny format vo formatovacom subore\n");
			exit(4);
			}

		$regFormat[1] = ereg_replace("[\t]*", "", $regFormat[1]);//odstranenie tabulatorov
		
		foreach ($dataFormat as $key => &$value){
			$value = ereg_replace("[ \t]*", "", $value);
			}
		unset($value);


		$regExp[$i] = $regFormat[1];
		$dataFormat[0] = $regFormat[2];
		
		//kontrola formatovacieho zapisu
		if (ereg ("^\.|^\||^[*]|^\+|^\)|[^%]\($|^\($|\(\)|^\!$|[^%]\|$|[^%]\!$|[^%][.|!][.|!*]", $regExp[$i], $regExp1) == TRUE){
			fwrite($STDERR, "Chybny format vo formatovacom subore\n");
			exit(4);
			}
		
		//prepisanie reg. vyrazov na php reg. vyrazy	
		$regExp[$i] = str_replace("\\", "\\\\", $regExp[$i]);
		$regExp[$i] = str_replace("[", "\[", $regExp[$i]);
		$regExp[$i] = str_replace("]", "\]", $regExp[$i]);
		$regExp[$i] = str_replace("^", "\^", $regExp[$i]);
		$regExp[$i] = str_replace("?", "\?", $regExp[$i]);
		$regExp[$i] = str_replace("\$", "\\$", $regExp[$i]);
		$regExp[$i] = str_replace("{", "\{", $regExp[$i]);
		$regExp[$i] = str_replace("}", "\}", $regExp[$i]);
		$regExp[$i] = ereg_replace("(.*)!(%*.)", "\\1[^\\2]", $regExp[$i]);//negacia

		$regExp[$i] = str_replace("%s", "\s", $regExp[$i]);
		$regExp[$i] = str_replace("%a", ".", $regExp[$i]);
		$regExp[$i] = str_replace("%d", "[0-9]", $regExp[$i]);
		$regExp[$i] = str_replace("%l", "[a-z]", $regExp[$i]);
		$regExp[$i] = str_replace("%L", "[A-Z]", $regExp[$i]);
		$regExp[$i] = str_replace("%w", "[a-z]|[A-Z]", $regExp[$i]);
		$regExp[$i] = str_replace("%W", "[a-z]|[A-Z]|[0-9]", $regExp[$i]);
		$regExp[$i] = str_replace("%t", "\t", $regExp[$i]);
		$regExp[$i] = str_replace("%n", "\n", $regExp[$i]);
		
		$regExp[$i] = str_replace("%.", "\.", $regExp[$i]);
		$regExp[$i] = str_replace("%|", "\|", $regExp[$i]);
		$regExp[$i] = str_replace("%!", "\!", $regExp[$i]);
		$regExp[$i] = str_replace("%*", "\*", $regExp[$i]);
		$regExp[$i] = str_replace("%+", "\+", $regExp[$i]);
		$regExp[$i] = str_replace("%(", "\(", $regExp[$i]);
		$regExp[$i] = str_replace("%)", "\)", $regExp[$i]);
		$regExp[$i] = str_replace("%%", "\%", $regExp[$i]);
		$regExp[$i] = str_replace("//", "\/\/", $regExp[$i]);
		
		//kontrola formatovacieho zapisu
		if (ereg ("\.\.|[^\]\.$|[^\]\%", $regExp[$i], $regExp1) == TRUE){
			fwrite($STDERR, "Chybny format vo formatovacom subore\n");
			exit(4);
			}
		
		$regExp[$i] = ereg_replace("([^\])\.", "\\1", $regExp[$i]);
		$regExp[$i] = ereg_replace("^(.+)\.(.+)$", "\\1\\2", $regExp[$i]);

		
		$partFirst[$i] = "";
		$partScnd[$i] = "";
		$num = NULL;

		//partFirst - pole otvaracich tagov
		//partScnd - pole uzavieracich tagov
		foreach ($dataFormat as $key => $value) {

			switch ($value){
				
				case "italic":
							$partFirst[$i] = $partFirst[$i] . "<i>";
							$partScnd[$i] = "</i>" . $partScnd[$i];
							break;
								
				case "bold":
							$partFirst[$i] = $partFirst[$i] . "<b>";
							$partScnd[$i] = "</b>" . $partScnd[$i];
							break;
				
				case "underline":
							$partFirst[$i] = $partFirst[$i] . "<u>";
							$partScnd[$i] = "</u>" . $partScnd[$i];
							break;
				
				case "teletype":
							$partFirst[$i] = $partFirst[$i] . "<tt>";
							$partScnd[$i] = "</tt>" . $partScnd[$i];
							break;
							
				case (ereg("^size:([1-7])$",$value, $num) ? true : false):
							$partFirst[$i] = $partFirst[$i] . "<font size=$num[1]>";
							$partScnd[$i] = "</font>" . $partScnd[$i];
							break;
							
				case (ereg("^color:([0-F|0-f]{6})$",$value, $num) ? true : false):
							$partFirst[$i] = $partFirst[$i] . "<font color=#$num[1]>";
							$partScnd[$i] = "</font>" . $partScnd[$i];
							break;
							
				default: 
						fwrite($STDERR, "Chybny format vo formatovacom subore\n");
						exit(4);
								
				}

		}
		unset($value);

		//preskocenie praznych riadkov
		do{
		$dataFormat = fgetcsv($format, 4096, ",", "\0");
		}while (($dataFormat[0] == NULL) && ($dataFormat != FALSE));
		$i++;
	}//koniec while
}//koniec if


$startStr = "";
$endStr = "";
$start_end = false;
$line = 0;
$i = 0;

//nacitanie zo vstupneho suboru
$dataInput = fgets($input, 4096);

/*
 * nacitanie zo vstupu po riadkoch
 * vypis sformatovaneho textu
 */
do{	
	$dataOutput = $dataInput;
	$dataInput = fgets($input, 4096);
	
	$line++;	

	if ($dataOutput != NULL){
	
	//ak existuje formatovacy prikaz vykona sa indexacia tagov
		if ($regExp != NULL){
		
			foreach ($regExp as $key1 => $value1)
			{
				if (((strcmp($value1,".+") == 0) || (strcmp($value1,".*") == 0)) && ($line == 1)){
					$start_end = true;
					$startStr = $startStr . $partFirst[$i];
					$endStr = $endStr . $partScnd[$i];
					$partFirst[$i] = "";
					$partScnd[$i] = "";
					}
					
				$i++;
				
				//zistenie indexov
				$chars = preg_split("/$value1/", $dataOutput, -1, PREG_SPLIT_OFFSET_CAPTURE);
				
				//vypocet otvaracich a uzatvaracich indexov
				foreach ($chars as $key => $value)
				{
					$indexArray[$index][0] = strlen($value[0]) + $value[1];
					if ($index > 0){
						$indexArray[$index-1][1] = $value[1];}						
					$index++;
				}
				//nastavenie na zaporne hodnoty na zistenie ukoncenia jedneho reg. vyrazu
				$indexArray[$index-1][0] = -1;
				$indexArray[$index-1][1] = -1;
			}	
			
			$index = 0;
			$i = 0;
			$door = false;
			
			//prekopirovanie vsetkych tagov na dane indexi
			foreach ($indexArray as $key => &$value)
			{
				if ($index == count($indexArray)-1)
					break;
				
				if ($value[0] != -1)
				{
					$door = false;
					
					//ak hodnoty indexov su spravne a nepresahuju velkost vstupeho retazca
					if ($value[0] < strlen($dataOutput) && ($value[0] < $value[1]) && ($value[1] <= strlen($dataOutput)))
					{
						$valueStart = $value[0];
						$valueEnd = $value[1];
						$door = false;
						$iOld = $i;

						//prekopirovanie otvaracieho a uzatvaracieho tagu na dane indexi
						if ($value[0] == 0)
							{
							$dataOutput = ereg_replace("^(.*)", "$partFirst[$i]\\1", $dataOutput);
							$value[1] = $value[1] + strlen($partFirst[$i]);					
							$dataOutputArr = str_split($dataOutput, $value[1]);
							$dataOutput = $dataOutputArr[0] . $partScnd[$i];
							for ($z = 1;$z < count($dataOutputArr); $z++){
								$dataOutput = $dataOutput . $dataOutputArr[$z];}
							}
						else{
							$dataOutputArr = str_split($dataOutput, $value[0]);
							$dataOutput = $dataOutputArr[0] . $partFirst[$i];
							for ($z = 1;$z < count($dataOutputArr); $z++){
								$dataOutput = $dataOutput . $dataOutputArr[$z];}
							
							$value[1] = $value[1] + strlen($partFirst[$i]);

							$dataOutputArr = str_split($dataOutput, $value[1]);
							$dataOutput = $dataOutputArr[0] . $partScnd[$i];
							for ($z = 1;$z < count($dataOutputArr); $z++){
								$dataOutput = $dataOutput . $dataOutputArr[$z];}
							}
						
						//prepocitanie indexov na zaklade uz vlozenych tagov do retazca
						for($y = $index+1; $y < count($indexArray)-1; $y++)
						{
							if ($valueStart <= $indexArray[$y][0])
							{
								if ($valueEnd <= $indexArray[$y][0]){
								$indexArray[$y][0] += strlen($partFirst[$i]) + strlen($partScnd[$i]);}
								else{
									$indexArray[$y][0] += strlen($partFirst[$i]);
									}
							}
							
							if ($valueStart < $indexArray[$y][1])
							{
								if ($valueEnd < $indexArray[$y][1]){
								$indexArray[$y][1] += strlen($partFirst[$i]) + strlen($partScnd[$i]);}
								else{
									$indexArray[$y][1] += strlen($partFirst[$i]);
									}
							}	
						}	
					}
				}
				elseif (($door == false) || ($value[1] == 0))
				{
					$i++;
					$door = true;
				}
					
				$index++;
			}
			
			unset($value);
			$index = 0;
			
		}//if ($regExp != NULL)

		//zapis otvaracieho tagu z reg. vyrazu ".+"
		if (($start_end == true) && ($line == 1)){
			$dataOutput = $startStr . $dataOutput;}
		
		//zapis uzatvaracieho tagu z reg. vyrazu ".+"	
		if (($start_end == true) && (feof($input)))
		{
			$start_end = false;
			if ($iOld > 0)
			{
				if ((strlen($dataOutput) - strlen($partScnd[$iOld-1])) > 0)
				{
				$dataOutputArr = str_split($dataOutput, strlen($dataOutput) - strlen($partScnd[$iOld-1]));
				$dataOutput = $dataOutputArr[0] . $endStr;
				for ($z = 1;$z < count($dataOutputArr); $z++){
					$dataOutput = $dataOutput . $dataOutputArr[$z];}
				}
				else {
					$dataOutput = $dataOutput . $endStr;
					 }
			}
		}
		
		//zapis <br /> na koniec riadku
		if ($br == true)
		{
			if (ereg ("\n", $dataOutput, $regExp1) == TRUE)
				$dataOutput = ereg_replace("\n", "<br />\n", $dataOutput);
			else
				$dataOutput = $dataOutput . "<br />";
		}
		
		//vypis sformatovaneho retazca	
		fputs($output, $dataOutput);

	}//if ($dataOutput != NULL)
			
}while ((!feof($input)) || ($dataInput != NULL));
	
//zatvorenie vsetkych otvorenych suborov	
if ($format != 0)
	fclose($format);
	
fclose($input);
fclose($output);
fclose($STDERR);
exit(0);
?>
